<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Envoie un rappel push + SMS aux utilisateurs dont le Premium expire bientôt.
 * Lancé quotidiennement à 10h via le scheduler Laravel.
 *
 * Seuils : J-7, J-3, J-1
 */
class SendPremiumExpiryReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(NotificationService $notificationService): void
    {
        $thresholds = [7, 3, 1]; // jours avant expiration

        foreach ($thresholds as $days) {
            $targetDate = Carbon::today()->addDays($days)->toDateString();

            $users = User::where('is_premium', true)
                ->whereDate('premium_expires_at', $targetDate)
                ->whereNotNull('premium_expires_at')
                ->get();

            foreach ($users as $user) {
                $this->sendReminder($user, $days, $notificationService);
            }

            Log::info("PremiumExpiryReminder: J-{$days}", ['count' => $users->count()]);
        }
    }

    private function sendReminder(User $user, int $daysLeft, NotificationService $notif): void
    {
        [$title, $body] = match ($daysLeft) {
            1 => [
                '⚠️ Dernier jour Premium !',
                'Ton abonnement COTA expire demain. Renouvelle maintenant pour ne rien manquer.',
            ],
            3 => [
                '⏳ Plus que 3 jours Premium',
                'Ton accès Premium se termine dans 3 jours. Renouvelle via Wave, Orange Money ou MTN.',
            ],
            default => [
                '📅 Ton Premium expire dans ' . $daysLeft . ' jours',
                'Pense à renouveler ton abonnement COTA pour continuer à recevoir les meilleurs pronostics.',
            ],
        };

        $notif->sendToUser($user->id, $title, $body, [
            'type'       => 'premium_expiry',
            'days_left'  => (string) $daysLeft,
            'screen'     => 'subscription',
        ]);
    }
}
