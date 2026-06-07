<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Prediction;
use App\Models\User;
use App\Services\NotificationQuotaService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Routine après-midi — 12h00 UTC (13h WAT).
 * Rappel coupon IA combiné du jour avec la cote totale.
 */
class SendRoutineAfternoonJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function handle(NotificationService $notif, NotificationQuotaService $quota): void
    {
        // Compter les pronostics publiés du jour pour le coupon
        $count = Prediction::whereDate('match_date', today())
            ->where('is_published', true)
            ->count();

        if ($count < 2) {
            Log::info('SendRoutineAfternoonJob: pas assez de pronostics pour le coupon', ['count' => $count]);
            return;
        }

        $sent = 0;

        User::whereNotNull('fcm_token')
            ->chunkById(500, function ($users) use ($notif, $quota, $count, &$sent) {
                $eligible = $quota->filterEligible($users, 'routine_afternoon');

                foreach ($eligible as $user) {
                    $locale = $user->locale ?? 'fr';

                    [$title, $body] = $locale === 'en'
                        ? [
                            "🎯 Today's AI Coupon is ready",
                            "{$count} picks selected by our algorithm. Open COTA to see the combined bet.",
                        ]
                        : [
                            "🎯 Le coupon IA du jour est prêt",
                            "{$count} picks sélectionnés par notre algorithme. Ouvre COTA pour voir le combiné du jour.",
                        ];

                    $sent += (int) $notif->sendToUser($user->id, $title, $body, [
                        'type'   => 'routine_afternoon',
                        'screen' => 'coupon',
                    ]);

                    $quota->increment($user->id);
                }
            });

        Log::info('SendRoutineAfternoonJob: terminé', ['sent' => $sent]);
    }
}
