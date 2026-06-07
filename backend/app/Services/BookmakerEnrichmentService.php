<?php

namespace App\Services;

use App\Models\Bookmaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Enrichit automatiquement les fiches bookmakers via Claude API.
 *
 * Pour chaque bookmaker, Claude restitue (depuis sa base de connaissance
 * publique) les informations structurées : méthodes de paiement, bonus,
 * dépôt minimum, note, description adaptée à l'Afrique de l'Ouest.
 *
 * Appel : php artisan bookmakers:enrich [--id=X] [--force]
 */
class BookmakerEnrichmentService
{
    private const CLAUDE_API_URL = 'https://api.anthropic.com/v1/messages';
    private const CLAUDE_MODEL   = 'claude-haiku-4-5-20251001'; // rapide + économique pour cette tâche
    private const CACHE_TTL      = 86400 * 7;                   // 7 jours

    public function __construct(
        private readonly string $apiKey = '',
    ) {}

    // ── Point d'entrée principal ─────────────────────────────────────────────

    /**
     * Enrichit un bookmaker. Retourne true si la base a été mise à jour.
     */
    public function enrich(Bookmaker $bm, bool $force = false): bool
    {
        $cacheKey = "bm_enriched:{$bm->id}";

        if (!$force && Cache::has($cacheKey)) {
            Log::info("[Enrich] {$bm->name} déjà enrichi récemment, skip.");
            return false;
        }

        $key = $this->apiKey ?: config('services.anthropic.key', env('ANTHROPIC_API_KEY', ''));

        if (empty($key)) {
            Log::warning("[Enrich] ANTHROPIC_API_KEY non configurée — enrichissement ignoré.");
            return false;
        }

        try {
            $data = $this->callClaude($bm->name, $bm->regions ?? [], $key);
            if (empty($data)) {
                return false;
            }

            $this->applyToModel($bm, $data);
            Cache::put($cacheKey, true, self::CACHE_TTL);

            Log::info("[Enrich] {$bm->name} enrichi avec succès.", $data);
            return true;

        } catch (\Throwable $e) {
            Log::error("[Enrich] Erreur pour {$bm->name} : " . $e->getMessage());
            return false;
        }
    }

    // ── Appel Claude ─────────────────────────────────────────────────────────

    private function callClaude(string $name, array $regions, string $key): array
    {
        $regionContext = $this->regionContext($regions);

        $prompt = <<<PROMPT
Tu es un expert des bookmakers sportifs, notamment en Afrique de l'Ouest et en Europe.

Donne-moi les informations PUBLIQUES et ACTUELLES sur le bookmaker "{$name}" {$regionContext}.

Réponds UNIQUEMENT avec un objet JSON valide, sans markdown, sans explication. Format exact :

{
  "deposit_methods": ["Wave", "Orange Money", "MTN Mobile Money", "Carte bancaire", "Virement"],
  "withdrawal_methods": ["Wave", "Orange Money", "MTN Mobile Money"],
  "min_deposit": 1000,
  "min_withdrawal": 2000,
  "bonus_label": "100% jusqu'à 50 000 FCFA",
  "rating": 4.2,
  "description": "Description courte (max 120 caractères) adaptée au marché africain.",
  "popular_rank": 2
}

Règles :
- deposit_methods et withdrawal_methods : liste uniquement les méthodes réellement disponibles
- Inclure les méthodes mobiles africaines si disponibles (Wave, Orange Money, MTN, Moov Africa, Airtel Money)
- Inclure Carte bancaire (Visa/Mastercard), Crypto, PayPal, Virement bancaire si disponibles
- min_deposit et min_withdrawal en FCFA (1 EUR ≈ 656 FCFA). Si inconnu, mettre null
- bonus_label : le bonus de bienvenue principal actuel, ou null si inconnu
- rating : note sur 5 basée sur la réputation publique (Trustpilot, forums), ou null
- popular_rank : rang mondial parmi les bookmakers (1=Bet365, 2=1xBet, etc.), ou null
- description : en français, courte, factuelle
- Si tu ne connais pas le bookmaker, retourne {}
PROMPT;

        $response = Http::withHeaders([
            'x-api-key'         => $key,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(30)->post(self::CLAUDE_API_URL, [
            'model'      => self::CLAUDE_MODEL,
            'max_tokens' => 600,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if (!$response->successful()) {
            Log::warning("[Enrich] Claude API error {$response->status()}: " . $response->body());
            return [];
        }

        $content = $response->json('content.0.text', '');
        $content = trim($content);

        // Extraire JSON si Claude a quand même ajouté du texte autour
        if (!str_starts_with($content, '{')) {
            preg_match('/\{.*\}/s', $content, $matches);
            $content = $matches[0] ?? '{}';
        }

        $data = json_decode($content, true);
        if (!is_array($data) || empty($data)) {
            Log::warning("[Enrich] JSON vide ou invalide pour {$name}: {$content}");
            return [];
        }

        return $data;
    }

    // ── Application au modèle ────────────────────────────────────────────────

    private function applyToModel(Bookmaker $bm, array $data): void
    {
        $updates = [];

        if (!empty($data['deposit_methods']) && is_array($data['deposit_methods'])) {
            $updates['deposit_methods'] = $data['deposit_methods'];
        }
        if (!empty($data['withdrawal_methods']) && is_array($data['withdrawal_methods'])) {
            $updates['withdrawal_methods'] = $data['withdrawal_methods'];
        }
        if (isset($data['min_deposit']) && is_numeric($data['min_deposit'])) {
            $updates['min_deposit'] = (int) $data['min_deposit'];
        }
        if (isset($data['min_withdrawal']) && is_numeric($data['min_withdrawal'])) {
            $updates['min_withdrawal'] = (int) $data['min_withdrawal'];
        }
        if (!empty($data['bonus_label'])) {
            // Ne pas écraser un bonus déjà saisi manuellement
            if (empty($bm->bonus_label)) {
                $updates['bonus_label'] = (string) $data['bonus_label'];
            }
        }
        if (isset($data['rating']) && is_numeric($data['rating'])) {
            $updates['rating'] = min(5.0, max(0.0, (float) $data['rating']));
        }
        if (!empty($data['description']) && empty($bm->description)) {
            $updates['description'] = (string) $data['description'];
        }
        if (isset($data['popular_rank']) && is_numeric($data['popular_rank']) && empty($bm->popular_rank)) {
            $updates['popular_rank'] = (int) $data['popular_rank'];
        }

        if (!empty($updates)) {
            $bm->update($updates);
        }
    }

    // ── Génération contenu blog ──────────────────────────────────────────────

    /**
     * Génère title, excerpt et bonus_description pour un BookmakerBlog.
     *
     * @param  string $bookmakerName  Nom du bookmaker
     * @param  string $category       Catégorie du blog (guide, promotion, tutoriel…)
     * @param  string $bonusLabel     Bonus actuel (optionnel)
     * @return array{title:string, excerpt:string, bonus_description:string}|null
     */
    public function generateBlogContent(string $bookmakerName, string $category, string $bonusLabel = ''): ?array
    {
        $key = $this->apiKey ?: config('services.anthropic.key', env('ANTHROPIC_API_KEY', ''));

        if (empty($key)) {
            return null;
        }

        $bonusCtx = $bonusLabel ? "Le bonus actuel est : « {$bonusLabel} »." : '';

        $prompt = <<<PROMPT
Tu es un rédacteur spécialisé dans les paris sportifs pour le marché africain francophone (Sénégal, Côte d'Ivoire, Cameroun, etc.).

Génère un contenu marketing pour la catégorie "{$category}" concernant le bookmaker "{$bookmakerName}". {$bonusCtx}

Réponds UNIQUEMENT avec un JSON valide, sans markdown :

{
  "title": "Titre accrocheur (max 80 caractères)",
  "excerpt": "Résumé (max 200 caractères, incite à l'action)",
  "bonus_description": "Description complète en 2-3 paragraphes (max 1200 caractères), en français, adaptée au contexte africain. Inclure les avantages, les étapes clés si pertinent, et un appel à l'action."
}
PROMPT;

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $key,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(30)->post(self::CLAUDE_API_URL, [
                'model'      => self::CLAUDE_MODEL,
                'max_tokens' => 800,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
            ]);

            if (!$response->successful()) {
                Log::warning("[BlogGen] Claude API error {$response->status()}: " . $response->body());
                return null;
            }

            $content = trim($response->json('content.0.text', ''));

            if (!str_starts_with($content, '{')) {
                preg_match('/\{.*\}/s', $content, $m);
                $content = $m[0] ?? '{}';
            }

            $data = json_decode($content, true);

            if (!is_array($data) || empty($data['title'])) {
                return null;
            }

            return [
                'title'             => (string) ($data['title'] ?? ''),
                'excerpt'           => (string) ($data['excerpt'] ?? ''),
                'bonus_description' => (string) ($data['bonus_description'] ?? ''),
            ];

        } catch (\Throwable $e) {
            Log::error("[BlogGen] Erreur : " . $e->getMessage());
            return null;
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function regionContext(array $regions): string
    {
        $africaRegions = ['west_africa', 'central_africa', 'east_africa', 'north_africa', 'south_africa'];
        $hasAfrica = !empty(array_intersect($regions, $africaRegions));
        $hasEurope = in_array('europe', $regions);
        $isGlobal  = empty($regions) || in_array('global', $regions);

        if ($hasAfrica && !$hasEurope) return 'disponible en Afrique de l\'Ouest (FCFA)';
        if ($hasEurope && !$hasAfrica) return 'disponible en Europe';
        if ($isGlobal || ($hasAfrica && $hasEurope)) return 'disponible mondialement (focus Afrique de l\'Ouest)';
        return '';
    }
}
