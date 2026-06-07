<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramSetWebhook extends Command
{
    protected $signature   = 'telegram:webhook {action=set : set|delete|info}';
    protected $description = 'Gérer le webhook du bot Telegram COTA';

    public function handle(TelegramService $telegram): int
    {
        $action = $this->argument('action');

        match ($action) {
            'set'    => $this->setWebhook($telegram),
            'delete' => $this->deleteWebhook($telegram),
            'info'   => $this->showInfo($telegram),
            default  => $this->error("Action inconnue : {$action}. Utilise set|delete|info"),
        };

        return self::SUCCESS;
    }

    private function setWebhook(TelegramService $telegram): void
    {
        $appUrl = rtrim(config('app.url'), '/');
        $token  = config('services.telegram.bot_token', '');

        if (empty($token)) {
            $this->error('TELEGRAM_BOT_TOKEN non configuré dans .env');
            return;
        }

        $webhookUrl = "{$appUrl}/api/telegram/webhook";
        $result     = $telegram->setWebhook($webhookUrl);

        if ($result['ok'] ?? false) {
            $this->info("✅ Webhook configuré : {$webhookUrl}");
        } else {
            $this->error('❌ Erreur : ' . ($result['description'] ?? 'inconnue'));
        }
    }

    private function deleteWebhook(TelegramService $telegram): void
    {
        $result = $telegram->deleteWebhook();
        $result['ok'] ?? false
            ? $this->info('✅ Webhook supprimé.')
            : $this->error('❌ Erreur : ' . ($result['description'] ?? 'inconnue'));
    }

    private function showInfo(TelegramService $telegram): void
    {
        $info = $telegram->getWebhookInfo();
        $this->line(json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
