<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Prediction;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function __construct(private readonly TelegramService $telegram) {}

    // ── Webhook principal — reçoit tous les updates Telegram ─────────────────

    public function webhook(Request $request): Response
    {
        // Validation IP — Telegram n'envoie que depuis ces plages officielles
        if (!$this->isFromTelegram($request->ip())) {
            Log::warning('Telegram webhook: IP non autorisée', ['ip' => $request->ip()]);
            return response('Forbidden', 403);
        }

        $update = $request->all();

        Log::debug('Telegram update', ['update_id' => $update['update_id'] ?? null]);

        // Message texte
        if (isset($update['message']['text'])) {
            $this->handleMessage($update['message']);
        }

        // Callback query (boutons inline)
        if (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);
        }

        return response('OK', 200);
    }

    // ── Dispatch des commandes ────────────────────────────────────────────────

    private function handleMessage(array $message): void
    {
        $chatId   = $message['chat']['id'];
        $text     = trim($message['text'] ?? '');
        $from     = $message['from'] ?? [];
        $username = $from['username'] ?? null;
        $tgId     = (string) ($from['id'] ?? '');

        match (true) {
            str_starts_with($text, '/start')   => $this->cmdStart($chatId, $tgId, $username, $text),
            str_starts_with($text, '/picks')   => $this->cmdPicks($chatId),
            str_starts_with($text, '/coupon')  => $this->cmdCoupon($chatId),
            str_starts_with($text, '/profile') => $this->cmdProfile($chatId, $tgId),
            str_starts_with($text, '/referral')=> $this->cmdReferral($chatId, $tgId),
            str_starts_with($text, '/help')    => $this->cmdHelp($chatId),
            default                            => $this->cmdUnknown($chatId),
        };
    }

    private function handleCallback(array $query): void
    {
        // Pour les boutons inline futurs
        $this->telegram->sendMessage($query['message']['chat']['id'], '✅ Action enregistrée.');
    }

    // ── /start ────────────────────────────────────────────────────────────────

    private function cmdStart(string $chatId, string $tgId, ?string $username, string $text): void
    {
        // /start peut contenir un deep link : /start link_XXXXX
        $parts  = explode(' ', $text);
        $param  = $parts[1] ?? null;

        // Liaison de compte si deep link valide
        if ($param && str_starts_with($param, 'link_')) {
            $token = str_replace('link_', '', $param);
            $user  = User::where('referral_code', $token)->first();

            if ($user) {
                $user->update([
                    'telegram_id'       => $tgId,
                    'telegram_username' => $username,
                ]);

                $this->telegram->sendMessage((int) $chatId,
                    "✅ <b>Compte lié avec succès !</b>\n\n"
                    . "Ton compte COTA est maintenant connecté à Telegram.\n"
                    . "Tu recevras les pronostics du jour directement ici.\n\n"
                    . "Tape /picks pour voir les pronostics d'aujourd'hui."
                );
                return;
            }
        }

        // Accueil standard
        $this->telegram->sendMessage((int) $chatId,
            "⚡ <b>Bienvenue sur COTA !</b>\n\n"
            . "Je suis ton assistant pronostics football alimenté par l'IA.\n\n"
            . "📌 <b>Commandes disponibles :</b>\n"
            . "/picks — Pronostics du jour\n"
            . "/coupon — Coupon IA combiné\n"
            . "/profile — Mon profil\n"
            . "/referral — Mon code parrainage\n"
            . "/help — Aide\n\n"
            . "🔗 <a href=\"https://cotafoot.com\">Ouvrir l'application</a>"
        );
    }

    // ── /picks ────────────────────────────────────────────────────────────────

    private function cmdPicks(string $chatId): void
    {
        $predictions = Prediction::whereDate('match_date', today())
            ->where('is_published', true)
            ->where('confidence_stars', '>=', 2)
            ->orderByDesc('total_score')
            ->limit(5)
            ->get();

        if ($predictions->isEmpty()) {
            $this->telegram->sendMessage((int) $chatId,
                "📭 Aucun pronostic publié pour aujourd'hui.\n"
                . "Reviens à <b>10h WAT</b> pour les picks du jour !"
            );
            return;
        }

        $msg = "⚽ <b>PICKS COTA DU JOUR</b>\n\n";

        foreach ($predictions as $p) {
            $stars = str_repeat('⭐', $p->confidence_stars);
            $odds  = number_format((float) $p->odds, 2);
            $msg  .= "• <b>{$p->home_team} vs {$p->away_team}</b>\n"
                   . "  🎯 {$p->prediction} @{$odds} {$stars}\n\n";
        }

        $msg .= "📲 <a href=\"https://cotafoot.com/predictions\">Voir tous les pronostics</a>";

        $this->telegram->sendMessage((int) $chatId, $msg);
    }

    // ── /coupon ───────────────────────────────────────────────────────────────

    private function cmdCoupon(string $chatId): void
    {
        $picks = Prediction::whereDate('match_date', today())
            ->where('is_published', true)
            ->where('confidence_stars', '>=', 2)
            ->orderByDesc('total_score')
            ->limit(5)
            ->get();

        if ($picks->count() < 2) {
            $this->telegram->sendMessage((int) $chatId,
                "📭 Le coupon du jour n'est pas encore disponible.\n"
                . "Reviens à <b>10h WAT</b> !"
            );
            return;
        }

        $totalOdds = $picks->reduce(fn($carry, $p) => $carry * (float) $p->odds, 1.0);
        $gain      = number_format((int) round($totalOdds * 1000), 0, '.', ' ');
        $totalOdds = number_format($totalOdds, 2);

        $msg = "🎟 <b>COUPON IA COTA DU JOUR</b>\n\n";

        foreach ($picks as $p) {
            $odds = number_format((float) $p->odds, 2);
            $msg .= "• {$p->home_team} vs {$p->away_team} → <b>{$p->prediction}</b> @{$odds}\n";
        }

        $msg .= "\n📊 Cote totale : <b>@{$totalOdds}</b>\n"
              . "💰 Gain potentiel (1 000 FCFA) : <b>{$gain} FCFA</b>\n\n"
              . "📲 <a href=\"https://cotafoot.com\">Voir le coupon complet</a>";

        $this->telegram->sendMessage((int) $chatId, $msg);
    }

    // ── /profile ──────────────────────────────────────────────────────────────

    private function cmdProfile(string $chatId, string $tgId): void
    {
        $user = User::where('telegram_id', $tgId)->first();

        if (!$user) {
            $this->telegram->sendMessage((int) $chatId,
                "🔗 <b>Compte non lié</b>\n\n"
                . "Pour lier ton compte COTA, ouvre l'application et va dans\n"
                . "<b>Profil → Connecter Telegram</b>."
            );
            return;
        }

        $premium = $user->isPremiumActive()
            ? '⭐ Premium actif jusqu\'au ' . $user->premium_expires_at?->format('d/m/Y')
            : '🔓 Compte gratuit';

        $this->telegram->sendMessage((int) $chatId,
            "👤 <b>Mon profil COTA</b>\n\n"
            . "Nom : {$user->name}\n"
            . "Statut : {$premium}\n"
            . "Parrainages : {$user->referral_count}\n\n"
            . "📲 <a href=\"https://cotafoot.com/profile\">Voir mon profil complet</a>"
        );
    }

    // ── /referral ─────────────────────────────────────────────────────────────

    private function cmdReferral(string $chatId, string $tgId): void
    {
        $user = User::where('telegram_id', $tgId)->first();

        if (!$user) {
            $this->telegram->sendMessage((int) $chatId,
                "🔗 Lie d'abord ton compte avec /start ou depuis l'application."
            );
            return;
        }

        $code = $user->referral_code;
        $link = "https://cotafoot.com?ref={$code}";

        $this->telegram->sendMessage((int) $chatId,
            "🎁 <b>Mon code parrainage COTA</b>\n\n"
            . "Code : <code>{$code}</code>\n"
            . "Lien : {$link}\n\n"
            . "Partage ce lien — chaque ami inscrit te rapporte des jours Premium !"
        );
    }

    // ── /help ─────────────────────────────────────────────────────────────────

    private function cmdHelp(string $chatId): void
    {
        $this->telegram->sendMessage((int) $chatId,
            "ℹ️ <b>Aide COTA Bot</b>\n\n"
            . "/picks — Pronostics du jour (top 5)\n"
            . "/coupon — Coupon IA combiné du jour\n"
            . "/profile — Voir mon profil et statut Premium\n"
            . "/referral — Mon code et lien de parrainage\n"
            . "/help — Afficher cette aide\n\n"
            . "🌐 <a href=\"https://cotafoot.com\">cotafoot.com</a>"
        );
    }

    private function cmdUnknown(string $chatId): void
    {
        $this->telegram->sendMessage((int) $chatId,
            "❓ Commande inconnue. Tape /help pour voir les commandes disponibles."
        );
    }

    // ── Sécurité — validation IP Telegram ────────────────────────────────────

    private function isFromTelegram(string $ip): bool
    {
        // Plages IP officielles Telegram (https://core.telegram.org/bots/webhooks#the-short-version)
        $ranges = [
            '149.154.160.0/20',
            '91.108.4.0/22',
        ];

        $ipLong = ip2long($ip);
        if ($ipLong === false) return false;

        foreach ($ranges as $range) {
            [$subnet, $bits] = explode('/', $range);
            $mask    = ~((1 << (32 - (int) $bits)) - 1);
            $network = ip2long($subnet) & $mask;
            if (($ipLong & $mask) === $network) return true;
        }

        // En développement local — autoriser localhost
        if (app()->environment('local') && in_array($ip, ['127.0.0.1', '::1'])) {
            return true;
        }

        return false;
    }
}
