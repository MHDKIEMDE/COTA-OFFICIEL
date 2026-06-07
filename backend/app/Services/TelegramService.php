<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $token;
    private string $baseUrl;

    public function __construct()
    {
        $this->token   = config('services.telegram.bot_token', '');
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}";
    }

    // ── Envoi de messages ─────────────────────────────────────────────────────

    public function sendMessage(int|string $chatId, string $text, array $options = []): bool
    {
        return $this->call('sendMessage', array_merge([
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ], $options));
    }

    public function sendPhoto(int|string $chatId, string $photoUrl, string $caption = ''): bool
    {
        return $this->call('sendPhoto', [
            'chat_id'    => $chatId,
            'photo'      => $photoUrl,
            'caption'    => $caption,
            'parse_mode' => 'HTML',
        ]);
    }

    // ── Webhook ───────────────────────────────────────────────────────────────

    public function setWebhook(string $url): array
    {
        $response = Http::post("{$this->baseUrl}/setWebhook", [
            'url'             => $url,
            'allowed_updates' => ['message', 'callback_query'],
            'drop_pending_updates' => true,
        ]);

        return $response->json();
    }

    public function deleteWebhook(): array
    {
        $response = Http::post("{$this->baseUrl}/deleteWebhook");
        return $response->json();
    }

    public function getWebhookInfo(): array
    {
        $response = Http::get("{$this->baseUrl}/getWebhookInfo");
        return $response->json();
    }

    // ── Appel API générique ───────────────────────────────────────────────────

    private function call(string $method, array $params): bool
    {
        if (empty($this->token)) {
            Log::warning('TelegramService: TELEGRAM_BOT_TOKEN non configuré');
            return false;
        }

        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/{$method}", $params);

            if (!$response->successful() || !($response->json('ok'))) {
                Log::warning("TelegramService: {$method} failed", [
                    'params'   => $params,
                    'response' => $response->json(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error("TelegramService: {$method} exception", ['error' => $e->getMessage()]);
            return false;
        }
    }

    // ── Messages formatés COTA ────────────────────────────────────────────────

    public function formatPick(array $pick): string
    {
        $stars = str_repeat('⭐', $pick['stars'] ?? 1);
        $odds  = number_format((float)($pick['odds'] ?? 0), 2);

        return "⚽ <b>{$pick['match']}</b>\n"
            . "🎯 {$pick['prediction']} — <b>@{$odds}</b>\n"
            . "{$stars} · {$pick['league']}\n";
    }

    public function formatCoupon(array $coupon): string
    {
        $picks = collect($coupon['picks'] ?? [])
            ->map(fn($p) => "• {$p['match']} → <b>{$p['prediction']}</b> @{$p['odds']}")
            ->join("\n");

        $totalOdds = number_format((float)($coupon['total_odds'] ?? 0), 2);
        $gain      = number_format((float)($coupon['potential_gain_1000'] ?? 0), 0, '.', ' ');
        $stars     = str_repeat('⭐', $coupon['stars'] ?? 1);

        return "🎟 <b>COUPON IA COTA DU JOUR</b> {$stars}\n\n"
            . "{$picks}\n\n"
            . "📊 Cote totale : <b>@{$totalOdds}</b>\n"
            . "💰 Gain potentiel (1 000 FCFA) : <b>{$gain} FCFA</b>\n\n"
            . "📲 <a href=\"https://cotafoot.com\">Voir tous les pronostics</a>";
    }
}
