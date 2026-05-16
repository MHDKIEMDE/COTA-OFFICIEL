<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// Configuration des jobs schedulés pour COTA LIVE
// Ces jobs s'exécutent automatiquement via le scheduler Laravel
// 
// Pour activer le scheduler, ajouter dans crontab :
// * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1

// Récupérer les matchs depuis Sportradar toutes les heures
Schedule::job(new \App\Jobs\FetchMatchesJob)
    ->hourly()
    ->name('fetch-matches')
    ->withoutOverlapping()
    ->onOneServer();

// Mettre à jour les scores en direct toutes les 2 minutes
Schedule::job(new \App\Jobs\UpdateLiveScoresJob)
    ->everyTwoMinutes()
    ->name('update-live-scores')
    ->withoutOverlapping()
    ->onOneServer();

// Mettre à jour les résultats des prédictions toutes les 5 minutes
Schedule::job(new \App\Jobs\UpdatePredictionResultsJob)
    ->everyFiveMinutes()
    ->name('update-prediction-results')
    ->withoutOverlapping()
    ->onOneServer();

// Générer les prédictions quotidiennement à 05:00 UTC
// Fenêtre morte mondiale (aucun match en cours) — données de veille complètes
Schedule::job(new \App\Jobs\GenerateAllPredictionsJob)
    ->dailyAt('05:00')
    ->timezone('UTC')
    ->name('generate-predictions')
    ->onOneServer();

// Envoyer les notifications quotidiennes à 06:30 UTC
// 1h30 de marge après génération — prédictions garanties prêtes
Schedule::job(new \App\Jobs\SendDailyNotificationJob)
    ->dailyAt('06:30')
    ->timezone('UTC')
    ->name('send-daily-notifications')
    ->onOneServer();

// Rappels expiration Premium (J-7, J-3, J-1) à 07:00 UTC
Schedule::job(new \App\Jobs\SendPremiumExpiryReminderJob)
    ->dailyAt('07:00')
    ->timezone('UTC')
    ->name('premium-expiry-reminders')
    ->onOneServer();

// Horizon métriques — snapshot toutes les 5 minutes pour les graphiques
Schedule::command('horizon:snapshot')->everyFiveMinutes();

// Monitoring lag replica MySQL — alerte si > 30s (prod uniquement)
Schedule::call(function () {
    if (!env('DB_READ_HOST')) {
        return; // Pas de replica configuré, skip
    }
    try {
        $rows = DB::select('SHOW SLAVE STATUS');
        $lag  = $rows[0]->Seconds_Behind_Master ?? null;
        if ($lag === null || $lag > 30) {
            Log::error('MySQL replica lag critique', ['lag_seconds' => $lag]);
        }
    } catch (\Throwable $e) {
        Log::error('MySQL replica check failed', ['error' => $e->getMessage()]);
    }
})->everyFiveMinutes()->name('check-replica-lag');
