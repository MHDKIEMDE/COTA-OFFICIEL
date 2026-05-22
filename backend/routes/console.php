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

// ── 06:30 UTC — Envoyer les notifications quotidiennes (legacy)
// 1h de marge après génération — prédictions garanties prêtes
Schedule::job(new \App\Jobs\SendDailyNotificationJob)
    ->dailyAt('06:30')
    ->timezone('UTC')
    ->name('send-daily-notifications')
    ->onOneServer();

// ── 08:00 UTC (09h WAT) — Routine matin : pronostic phare du jour
Schedule::job(new \App\Jobs\SendRoutineMorningJob)
    ->dailyAt('08:00')
    ->timezone('UTC')
    ->name('routine-morning')
    ->withoutOverlapping()
    ->onOneServer();

// ── 12:00 UTC (13h WAT) — Routine après-midi : rappel coupon IA
Schedule::job(new \App\Jobs\SendRoutineAfternoonJob)
    ->dailyAt('12:00')
    ->timezone('UTC')
    ->name('routine-afternoon')
    ->withoutOverlapping()
    ->onOneServer();

// ── 21:00 UTC (22h WAT) — Routine soir : récap résultats du jour
Schedule::job(new \App\Jobs\SendRoutineEveningRecapJob)
    ->dailyAt('21:00')
    ->timezone('UTC')
    ->name('routine-evening')
    ->withoutOverlapping()
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

// ── Toutes les heures — Monitorer les quotas API
Schedule::job(new \App\Jobs\MonitorApiQuotasJob)
    ->hourly()
    ->name('monitor-api-quotas')
    ->onOneServer();

// ── Toutes les 15 min — Précalculer les stats empty state (win rate, derniers gagnés)
Schedule::job(new \App\Jobs\CacheEmptyStateDataJob)
    ->everyFifteenMinutes()
    ->name('cache-empty-state-data')
    ->withoutOverlapping()
    ->onOneServer();

// ── Lundi 06:00 UTC — Auto-découverte bookmakers + notification admin si nouveau
Schedule::command('bookmakers:discover --notify')
    ->weekly()
    ->mondays()
    ->at('06:00')
    ->timezone('UTC')
    ->name('discover-bookmakers')
    ->withoutOverlapping()
    ->onOneServer();

// ── Dimanche 02:00 UTC — Enrichissement auto des fiches bookmakers via Claude
// Coût : ~1 appel Claude Haiku par bookmaker (~0.001$ pièce)
Schedule::job(new \App\Jobs\EnrichBookmakersJob)
    ->weekly()
    ->sundays()
    ->at('02:00')
    ->timezone('UTC')
    ->name('enrich-bookmakers')
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
