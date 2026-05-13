<?php

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

// Générer les prédictions quotidiennement à 08:00
Schedule::job(new \App\Jobs\GenerateAllPredictionsJob)
    ->dailyAt('08:00')
    ->name('generate-predictions')
    ->onOneServer();

// Envoyer les notifications quotidiennes à 09:00
Schedule::job(new \App\Jobs\SendDailyNotificationJob)
    ->dailyAt('09:00')
    ->name('send-daily-notifications')
    ->onOneServer();

// Rappels expiration Premium (J-7, J-3, J-1) à 10:00
Schedule::job(new \App\Jobs\SendPremiumExpiryReminderJob)
    ->dailyAt('10:00')
    ->name('premium-expiry-reminders')
    ->onOneServer();

// Deuxième génération de prédictions à 20:00
Schedule::job(new \App\Jobs\GenerateAllPredictionsJob)
    ->dailyAt('20:00')
    ->name('generate-predictions-evening')
    ->onOneServer();
