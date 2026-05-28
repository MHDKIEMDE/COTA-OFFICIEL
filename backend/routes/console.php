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

// ── 00:05 UTC — Peuplement auto BD dès que le quota se renouvelle (minuit UTC)
// Si quota ok : peupler BD historique top ligues + régénérer prédictions algo complet
// Si quota encore vide : skip silencieux (fallback RapidAPI prend le relais à 05:30)
Schedule::job(new \App\Jobs\RefreshDatabaseWhenQuotaRestoredJob)
    ->dailyAt('00:05')
    ->timezone('UTC')
    ->name('refresh-db-quota-restored')
    ->withoutOverlapping()
    ->onOneServer();

// ── 05:00 UTC — Récupérer les matchs du jour (1 seule requête API)
// Le cache 24h fait que tous les appels suivants lisent depuis Redis
Schedule::job(new \App\Jobs\FetchMatchesJob)
    ->dailyAt('05:00')
    ->timezone('UTC')
    ->name('fetch-matches-daily')
    ->withoutOverlapping()
    ->onOneServer();

// ── 05:10 UTC — Récupérer les résultats d'hier (TheSportsDB, gratuit, 0 quota)
// Doit tourner AVANT GenerateAllPredictionsJob pour que l'algo ait l'historique frais
Schedule::command('matches:fetch-history --days=1')
    ->dailyAt('05:10')
    ->timezone('UTC')
    ->name('fetch-yesterday-results')
    ->withoutOverlapping()
    ->onOneServer();

// ── 05:30 UTC — Générer les prédictions
// Quota dispo  → algo 9 critères complet (données réelles API-Football)
// Quota épuisé → fallback prédictions tierces RapidAPI (abonnés ont toujours du contenu)
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

// ── Toutes les 15 min — Détection anomalies de cotes (live 1xBet vs marché)
Schedule::job(new \App\Jobs\DetectOddsAnomalyJob)
    ->everyFifteenMinutes()
    ->timezone('UTC')
    ->name('detect-odds-anomaly')
    ->withoutOverlapping()
    ->onOneServer();

// ── 09:00 UTC (10h WAT) — Analyse coups sûrs (après génération des prédictions)
Schedule::job(new \App\Jobs\RunSureBetAnalysisJob)
    ->dailyAt('09:00')
    ->timezone('UTC')
    ->name('run-sure-bet-analysis')
    ->withoutOverlapping()
    ->onOneServer();

// ── 08:00 UTC (09h WAT) — Pari du Jour : meilleur pari EV+ (Value Betting)
Schedule::job(new \App\Jobs\SendBestValueBetNotificationJob)
    ->dailyAt('08:00')
    ->timezone('UTC')
    ->name('send-best-value-bet')
    ->withoutOverlapping()
    ->onOneServer();

// ── 08:30 UTC (09h30 WAT) — Routine matin : pronostic phare du jour
Schedule::job(new \App\Jobs\SendRoutineMorningJob)
    ->dailyAt('08:30')
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

// ── Toutes les heures — Vérifier inscriptions bookmakers → activer 7j Premium (§21.3 CDC V2)
Schedule::job(new \App\Jobs\CheckBookmakerRegistrationsJob)
    ->hourly()
    ->name('check-bookmaker-registrations')
    ->withoutOverlapping()
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
