<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class TestNotification extends Command
{
    protected $signature = 'notification:test
                            {--token= : FCM token du device cible}
                            {--user=  : ID utilisateur (alternatif au token)}
                            {--title= : Titre de la notification}
                            {--body=  : Corps de la notification}
                            {--screen=home : Écran de deep link (home/coupon/subscription)}';

    protected $description = 'Envoie une notification push FCM de test';

    public function handle(NotificationService $notifications): int
    {
        $token  = $this->option('token');
        $userId = $this->option('user');
        $title  = $this->option('title') ?? '⚽ COTA — Test notification';
        $body   = $this->option('body')  ?? 'Vos pronostics du jour sont disponibles !';
        $screen = $this->option('screen') ?? 'home';

        $data = ['screen' => $screen, 'type' => 'test'];

        if ($token) {
            $this->info("Envoi vers token FCM : " . substr($token, 0, 25) . '...');
            $ok = $notifications->sendToToken($token, $title, $body, $data);
            $this->line($ok ? '<fg=green>✓ Notification envoyée avec succès</>' : '<fg=red>✗ Échec de l\'envoi (voir les logs)</> ');
            return $ok ? self::SUCCESS : self::FAILURE;
        }

        if ($userId) {
            $this->info("Envoi vers user ID : $userId");
            $ok = $notifications->sendToUser((int) $userId, $title, $body, $data);
            $this->line($ok ? '<fg=green>✓ Notification envoyée avec succès</>' : '<fg=red>✗ Échec (user sans token FCM ou erreur FCM)</> ');
            return $ok ? self::SUCCESS : self::FAILURE;
        }

        $this->error('Paramètre manquant. Usage :');
        $this->line('  php artisan notification:test --token=FCM_TOKEN_ICI');
        $this->line('  php artisan notification:test --user=1');
        $this->newLine();
        $this->line('Options disponibles :');
        $this->line('  --title="Mon titre"');
        $this->line('  --body="Mon message"');
        $this->line('  --screen=coupon  (home | coupon | subscription | history)');
        $this->newLine();
        $this->info('Pour obtenir le FCM token, ouvre l\'app Flutter et cherche dans les logs :');
        $this->line('  flutter logs | grep "FCM token"');

        return self::FAILURE;
    }
}
