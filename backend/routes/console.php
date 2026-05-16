<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// ============================================================
// COTA — Scheduler optimisé pour plan API gratuit (100 req/j)
//
// Stratégie quota :
//   - 1 requête /fixtures à 05:00 UTC → cache 24h
//   - Prédictions générées depuis ce cache → 0 requête supplémentaire
//   - Live scores désactivé sur plan gratuit
//   - Résultats mis à jour depuis la DB locale uniquement
//
// Pour activer : * * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1
// ============================================================

// ── 05:00 UTC — Récupérer les matchs du jour (1 seule requête API)
// Le cache 24h fait que tous les appels suivants lisent depuis Redis
Schedule::job(new \App\Jobs\FetchMatchesJob)
    ->dailyAt('05:00')
    ->timezone('UTC')
    ->name('fetch-matches-daily')
    ->withoutOverlapping()
    ->onOneServer();

// ── 05:30 UTC — Générer les prédictions (depuis le cache, 0 requête API)
Schedule::job(new \App\Jobs\GenerateAllPredictionsJob)
    ->dailyAt('05:30')
    ->timezone('UTC')
    ->name('generate-predictions')
    ->withoutOverlapping()
    ->onOneServer();

// ── 06:30 UTC — Envoyer les notifications quotidiennes
// 1h de marge après génération — prédictions garanties prêtes
Schedule::job(new \App\Jobs\SendDailyNotificationJob)
    ->dailyAt('06:30')
    ->timezone('UTC')
    ->name('send-daily-notifications')
    ->onOneServer();

// ── 07:00 UTC — Rappels expiration Premium (J-7, J-3, J-1)
Schedule::job(new \App\Jobs\SendPremiumExpiryReminderJob)
    ->dailyAt('07:00')
    ->timezone('UTC')
    ->name('premium-expiry-reminders')
    ->onOneServer();

// ── Toutes les heures — Mettre à jour les résultats (DB locale, 0 requête API)
Schedule::job(new \App\Jobs\UpdatePredictionResultsJob)
    ->hourly()
    ->name('update-prediction-results')
    ->withoutOverlapping()
    ->onOneServer();

// ── Horizon métriques
Schedule::command('horizon:snapshot')->everyFiveMinutes();

// ── Monitoring lag replica MySQL
Schedule::call(function () {
    if (!env('DB_READ_HOST')) return;
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
