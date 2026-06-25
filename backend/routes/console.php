<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// ============================================================
// COTA — Scheduler
//
// Stratégie principale (23h UTC) :
//   1. Fetch matchs J+1 (données complètes, matchs du soir terminés)
//   2. Résultats d'hier → historique frais pour l'algo
//   3. Cotes 1xBet pré-match via The Odds API (cache 4h)
//   4. Génération prédictions algo 9 critères + coupon IA
//
// Stratégie de rattrapage (00:05 UTC) :
//   - Quota API-Football renouvelé à minuit UTC
//   - Complète ce qui n'a pas pu être généré à 23h
//   - Régénère si prédictions insuffisantes
//
// Pour activer : * * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1
// ============================================================

// ── 22:50 UTC — Résultats d'hier (TheSportsDB, gratuit, 0 quota)
// Doit tourner AVANT FetchMatchesJob pour que l'algo ait l'historique frais
Schedule::command('matches:fetch-history --days=1')
    ->dailyAt('22:50')
    ->timezone('UTC')
    ->name('fetch-yesterday-results')
    ->withoutOverlapping()
    ->onOneServer();

// ── 23:00 UTC — Fetch matchs J+1
// Tous les matchs européens du soir sont terminés → scores complets, stats à jour
// Derniers quotas du jour utilisés avant renouvellement minuit
Schedule::job(new \App\Jobs\FetchMatchesJob)
    ->dailyAt('23:00')
    ->timezone('UTC')
    ->name('fetch-matches-eve')
    ->withoutOverlapping()
    ->onOneServer();

// ── 23:10 UTC — Import pronostics RapidAPI (SOURCE PRINCIPALE)
// 1 appel API (cache 6h), prono + cotes déjà calculés, filtre matchs commencés.
// Doit tourner AVANT la génération coupon pour que les picks soient en base.
Schedule::command('predictions:import-rapidapi')
    ->dailyAt('23:10')
    ->timezone('UTC')
    ->name('import-rapidapi-predictions')
    ->withoutOverlapping()
    ->onOneServer();

// ── 23:12 UTC — Import pronostics Coupe du monde (API-Football /predictions)
// RapidAPI ne couvre pas la CDM. Cotes réelles 1xBet via The Odds API.
// Doit tourner AVANT la génération coupon (coupons par compétition J/J+1).
Schedule::command('predictions:import-worldcup')
    ->dailyAt('23:12')
    ->timezone('UTC')
    ->name('import-worldcup-predictions')
    ->withoutOverlapping()
    ->onOneServer();

// ── 23:15 UTC — Génération prédictions + cotes 1xBet + coupon IA
// OddsApiService charge les cotes 1xBet pré-match (The Odds API) en début de job
// Quota dispo  → algo 9 critères complet (données réelles API-Football)
// Quota épuisé → fallback prédictions tierces RapidAPI
Schedule::job(new \App\Jobs\GenerateAllPredictionsJob)
    ->dailyAt('23:15')
    ->timezone('UTC')
    ->name('generate-predictions')
    ->withoutOverlapping()
    ->onOneServer();

// ── 00:05 UTC — Rattrapage dès que le quota se renouvelle (minuit UTC)
// Complète les prédictions manquantes ou régénère si génération de 23h15 insuffisante
// Si quota ok → algo complet sur les matchs non couverts
// Si quota encore vide → skip silencieux
Schedule::job(new \App\Jobs\RefreshDatabaseWhenQuotaRestoredJob)
    ->dailyAt('00:05')
    ->timezone('UTC')
    ->name('refresh-db-quota-restored')
    ->withoutOverlapping()
    ->onOneServer();

// ── 08:45 UTC — Rattrapage import RapidAPI pour les matchs ajoutés en journée
// Cache 6h expiré → nouvel appel ; resync coupon avant le broadcast Telegram de 09h00
Schedule::command('predictions:import-rapidapi')
    ->dailyAt('08:45')
    ->timezone('UTC')
    ->name('import-rapidapi-predictions-morning')
    ->withoutOverlapping()
    ->onOneServer();

// ── 08:47 UTC — Rattrapage import Coupe du monde (matchs ajoutés en journée)
Schedule::command('predictions:import-worldcup')
    ->dailyAt('08:47')
    ->timezone('UTC')
    ->name('import-worldcup-predictions-morning')
    ->withoutOverlapping()
    ->onOneServer();

// ── Toutes les 3h — Rattrapage import Coupe du monde
// L'API-Football remplit les pronos CDM en journée (parfois après le matin).
// Sans relance régulière, les matchs du soir restent is_published=0 → invisibles
// sur l'accueil. Une relance /3h garantit leur publication dès que l'API est prête.
Schedule::command('predictions:import-worldcup')
    ->everyThreeHours()
    ->name('import-worldcup-predictions-catchup')
    ->withoutOverlapping()
    ->onOneServer();

// ── 09:00 UTC (10h WAT) — Broadcast Telegram picks du jour
Schedule::job(new \App\Jobs\SendTelegramBroadcastJob)
    ->dailyAt('09:00')
    ->timezone('UTC')
    ->name('telegram-broadcast')
    ->withoutOverlapping()
    ->onOneServer();

// ── 09:00 UTC (09h WAT) — Envoyer les notifications quotidiennes
// Prédictions + coupon garantis prêts depuis 23h15 la veille
Schedule::job(new \App\Jobs\SendDailyNotificationJob)
    ->dailyAt('09:00')
    ->timezone('UTC')
    ->name('send-daily-notifications')
    ->onOneServer();

// ── Toutes les 30 min — Fetch sources RSS actualités
Schedule::job(new \App\Jobs\FetchNewsSourcesJob)
    ->everyThirtyMinutes()
    ->name('fetch-news-sources')
    ->withoutOverlapping()
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

// ── Toutes les 15 min — Vérifier inscriptions bookmakers → activer 7j Premium (§21.3 CDC V2)
Schedule::job(new \App\Jobs\CheckBookmakerRegistrationsJob)
    ->everyFifteenMinutes()
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

// ── Dimanche 03:00 UTC — Nettoyage hebdomadaire DB (archive + prune)
// predictions > 90j archivées en JSON, matches > 60j supprimés, notifications > 30j supprimées
Schedule::job(new \App\Jobs\PruneOldDataJob)
    ->weekly()
    ->sundays()
    ->at('03:00')
    ->timezone('UTC')
    ->name('prune-old-data')
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

// ── Toutes les heures — Vérifier seuils influenceurs et distribuer récompenses
Schedule::job(new \App\Jobs\CheckInfluencerRewardsJob)
    ->hourly()
    ->name('check-influencer-rewards')
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
