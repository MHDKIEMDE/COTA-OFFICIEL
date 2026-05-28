<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Couche d'analyse IA — "le serveur" (§9 CDC V2).
 *
 * Rôle strict : mettre en mots les chiffres déjà produits par l'algo.
 * N'effectue aucun calcul, ne prend aucune décision.
 *
 * Contraintes de génération (§9.2) :
 *   - 3–4 phrases en français, données fournies uniquement
 *   - Interdiction d'inventer une statistique absente
 *   - Interdiction des termes "garanti", "100 %", "gain sûr"
 *   - Se termine par un rappel jeu responsable
 *
 * Appel LLM : une seule fois par prédiction dans le pipeline (§9.3).
 * Fallback template si LLM indisponible — l'analyse est un bonus, pas une dépendance critique.
 */
class PredictionAnalysisService
{
    private const FORBIDDEN_TERMS = [
        'garanti', 'garantie', 'garantis', '100 %', '100%',
        'gain sûr', 'pari sûr', 'coup sûr', 'certitude',
    ];

    private const RESPONSIBLE_GAMBLING_REMINDER =
        'Pariez de façon responsable : COTA est un outil d\'aide à la décision, aucun résultat n\'est garanti.';

    /**
     * Génère l'analyse textuelle d'une prédiction.
     * Essaie le LLM configuré ; bascule sur le template en cas d'échec.
     *
     * @param array $predictionData  Résultat de PredictionAlgorithmService::generatePrediction()
     * @param array $matchContext    ['home_team', 'away_team', 'competition']
     */
    public function generateAnalysis(array $predictionData, array $matchContext): string
    {
        $provider = config('services.llm.provider', 'none');

        if ($provider !== 'none') {
            try {
                $analysis = $this->callLlm($predictionData, $matchContext, $provider);
                if ($analysis) {
                    return $this->sanitize($analysis);
                }
            } catch (\Throwable $e) {
                Log::warning('PredictionAnalysisService: LLM failed, using template', [
                    'provider' => $provider,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        return $this->buildTemplate($predictionData, $matchContext);
    }

    /**
     * Appel LLM (Anthropic ou OpenAI selon config).
     */
    private function callLlm(array $predictionData, array $matchContext, string $provider): ?string
    {
        $prompt = $this->buildPrompt($predictionData, $matchContext);

        if ($provider === 'anthropic') {
            return $this->callAnthropic($prompt);
        }

        if ($provider === 'openai') {
            return $this->callOpenAi($prompt);
        }

        return null;
    }

    private function buildPrompt(array $predictionData, array $matchContext): string
    {
        $home       = $matchContext['home_team'] ?? 'Domicile';
        $away       = $matchContext['away_team'] ?? 'Extérieur';
        $competition = $matchContext['competition'] ?? '';
        $outcome    = $predictionData['outcome'] ?? '';
        $type       = $predictionData['type'] ?? '';
        $confidence = round($predictionData['confidence'] ?? 0, 1);
        $odds       = number_format((float) ($predictionData['odds'] ?? 1.5), 2);
        $scores     = $predictionData['scores'] ?? [];
        $engine     = $predictionData['engine'] ?? 'force';

        $scoresText = implode(', ', array_map(
            fn($k, $v) => "{$k}: {$v}",
            array_keys($scores),
            $scores
        ));

        return <<<PROMPT
Tu es l'assistant d'analyse de COTA, une application de pronostics football.

MATCH : {$home} vs {$away} ({$competition})
PRONOSTIC : {$type} — {$outcome} (cote indicative {$odds})
SCORE DE CONFIANCE : {$confidence}/100
MOTEUR : {$engine}
SCORES CRITÈRES : {$scoresText}

Rédige une analyse de 3 à 4 phrases en français à partir UNIQUEMENT des données ci-dessus.
Règles absolues :
- N'invente aucune statistique, aucun joueur, aucune information absente des données fournies.
- N'utilise JAMAIS les mots : garanti, garantie, 100 %, gain sûr, pari sûr, coup sûr, certitude.
- Termine par cette phrase exacte : "Pariez de façon responsable : COTA est un outil d'aide à la décision, aucun résultat n'est garanti."
- Langue : français uniquement.
PROMPT;
    }

    private function callAnthropic(string $prompt): ?string
    {
        $apiKey = config('services.llm.anthropic_key');
        if (!$apiKey) return null;

        $model = config('services.llm.anthropic_model', 'claude-haiku-4-5-20251001');

        $response = Http::withHeaders([
            'x-api-key'         => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(15)->post('https://api.anthropic.com/v1/messages', [
            'model'      => $model,
            'max_tokens' => 300,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Anthropic API error: ' . $response->status());
        }

        return $response->json('content.0.text');
    }

    private function callOpenAi(string $prompt): ?string
    {
        $apiKey = config('services.llm.openai_key');
        if (!$apiKey) return null;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(15)->post('https://api.openai.com/v1/chat/completions', [
            'model'      => config('services.llm.openai_model', 'gpt-4o-mini'),
            'max_tokens' => 300,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('OpenAI API error: ' . $response->status());
        }

        return $response->json('choices.0.message.content');
    }

    /**
     * Template fallback (§9.3) — généré sans LLM à partir des scores.
     */
    private function buildTemplate(array $predictionData, array $matchContext): string
    {
        $home       = $matchContext['home_team'] ?? 'L\'équipe domicile';
        $away       = $matchContext['away_team'] ?? 'l\'équipe extérieure';
        $outcome    = $predictionData['outcome'] ?? '';
        $type       = $predictionData['type'] ?? '';
        $confidence = round($predictionData['confidence'] ?? 0, 1);
        $scores     = $predictionData['scores'] ?? [];
        $engine     = $predictionData['engine'] ?? 'force';

        $parts = [];

        if ($engine === 'goals') {
            $goalsScore = $scores['goals'] ?? 0;
            if ($goalsScore >= 7) {
                $parts[] = "Les statistiques de buts indiquent un match offensif entre {$home} et {$away}.";
            } else {
                $parts[] = "Les statistiques de buts suggèrent un match défensif entre {$home} et {$away}.";
            }
        } else {
            $formScore = $scores['form'] ?? 0;
            if ($formScore >= 17) {
                $parts[] = "{$home} affiche une forme récente solide qui justifie ce pronostic.";
            } elseif ($formScore <= 8) {
                $parts[] = "{$away} présente de meilleures performances récentes dans cette confrontation.";
            } else {
                $parts[] = "La forme récente des deux équipes est relativement équilibrée.";
            }
        }

        $h2hScore = $scores['h2h'] ?? 0;
        if ($h2hScore >= 14) {
            $parts[] = "L'historique des confrontations directes penche en faveur de {$home}.";
        } elseif ($h2hScore <= 6) {
            $parts[] = "Les confrontations passées favorisent {$away} dans ce duel.";
        }

        $parts[] = "Le score de confiance de l'algorithme est de {$confidence}/100 pour le pronostic {$type} : {$outcome}.";
        $parts[] = self::RESPONSIBLE_GAMBLING_REMINDER;

        return implode(' ', $parts);
    }

    /**
     * Filtre les termes interdits et vérifie la présence du rappel jeu responsable.
     */
    private function sanitize(string $text): string
    {
        foreach (self::FORBIDDEN_TERMS as $term) {
            if (mb_stripos($text, $term) !== false) {
                Log::warning('PredictionAnalysisService: terme interdit détecté, bascule sur template', [
                    'term' => $term,
                ]);
                return '';
            }
        }

        if (mb_stripos($text, 'responsable') === false) {
            $text .= ' ' . self::RESPONSIBLE_GAMBLING_REMINDER;
        }

        return trim($text);
    }
}
