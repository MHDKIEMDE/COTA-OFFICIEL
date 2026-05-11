<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job pour envoyer les notifications quotidiennes (8h et 20h)
 */
class SendDailyNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $timeOfDay; // 'morning' ou 'evening'

    /**
     * Create a new job instance.
     */
    public function __construct(string $timeOfDay = 'morning')
    {
        $this->timeOfDay = $timeOfDay;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        $title = $this->timeOfDay === 'morning'
            ? '🔥 Nouveaux pronostics disponibles !'
            : '⚽ Pronostics du soir sont là !';

        $body = $this->timeOfDay === 'morning'
            ? 'Découvrez les meilleurs pronostics du jour'
            : 'Consultez les pronostics de ce soir';

        $data = [
            'type' => 'daily_predictions',
            'screen' => 'dashboard',
        ];

        $sent = $notificationService->sendToAll($title, $body, $data);

        Log::info('Daily notification job completed', [
            'time_of_day' => $this->timeOfDay,
            'notifications_sent' => $sent,
        ]);
    }
}

