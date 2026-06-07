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
 * Routine soir — 21h00 UTC (22h WAT).
 * Récap des résultats du jour : gagnés / perdus / en attente.
 */
class SendRoutineEveningRecapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function handle(NotificationService $notif, NotificationQuotaService $quota): void
    {
        $predictions = Prediction::whereDate('match_date', today())
            ->where('is_published', true)
            ->get();

        if ($predictions->isEmpty()) {
            Log::info('SendRoutineEveningRecapJob: aucun pronostic aujourd\'hui');
            return;
        }

        $won    = $predictions->where('status', 'won')->count();
        $lost   = $predictions->where('status', 'lost')->count();
        $total  = $predictions->whereIn('status', ['won', 'lost'])->count();
        $rate   = $total > 0 ? round(($won / $total) * 100) : 0;

        $sent = 0;

        User::whereNotNull('fcm_token')
            ->chunkById(500, function ($users) use ($notif, $quota, $won, $lost, $total, $rate, &$sent) {
                $eligible = $quota->filterEligible($users, 'routine_evening');

                foreach ($eligible as $user) {
                    $locale = $user->locale ?? 'fr';

                    if ($total === 0) {
                        [$title, $body] = $locale === 'en'
                            ? ['📊 Recap — matches in progress', 'Results are being updated. Check back later tonight.']
                            : ['📊 Récap — matchs en cours', 'Les résultats sont en cours de mise à jour. Reviens plus tard.'];
                    } elseif ($rate >= 60) {
                        [$title, $body] = $locale === 'en'
                            ? ["✅ Great day — {$won}/{$total} won ({$rate}%)", "COTA is on a roll! Tomorrow's picks will be ready at 8am."]
                            : ["✅ Belle journée — {$won}/{$total} gagnés ({$rate}%)", "COTA est en forme ! Les pronostics de demain seront prêts à 8h."];
                    } else {
                        [$title, $body] = $locale === 'en'
                            ? ["📊 Today's recap — {$won}/{$total} won", "Football is unpredictable. Tomorrow is another chance!"]
                            : ["📊 Récap du jour — {$won}/{$total} gagnés", "Le football est imprévisible. Demain c'est une nouvelle chance !"];
                    }

                    $sent += (int) $notif->sendToUser($user->id, $title, $body, [
                        'type'   => 'routine_evening',
                        'screen' => 'history',
                        'won'    => (string) $won,
                        'total'  => (string) $total,
                        'rate'   => (string) $rate,
                    ]);

                    $quota->increment($user->id);
                }
            });

        Log::info('SendRoutineEveningRecapJob: terminé', [
            'sent' => $sent,
            'won'  => $won,
            'lost' => $lost,
            'rate' => $rate,
        ]);
    }
}
