<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

class ApiMonitorController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today()->toDateString();

        // ── API-Football quota ─────────────────────────────────────────────
        $footballDailyUsed  = (int) Cache::get('football_api_daily_requests', 0);
        $footballDailyLimit = (int) config('football-api.rate_limits.daily', 100);
        $footballMinUsed    = (int) Cache::get('football_api_minute_requests', 0);
        $footballMinLimit   = (int) config('football-api.rate_limits.per_minute', 30);

        // ── RapidAPI quotas (stockés en cache par RapidApiService) ─────────
        $rapidApiCalls = Cache::get('rapidapi_calls_today', []);

        // ── Appels par endpoint aujourd'hui (loggés en cache) ─────────────
        $endpointCalls = Cache::get('api_endpoint_calls_' . $today, []);

        // ── Dernière activité des jobs schedulés ──────────────────────────
        $lastJobs = [
            'FetchMatchesJob'            => Cache::get('last_run_fetch_matches'),
            'GenerateAllPredictionsJob'  => Cache::get('last_run_generate_predictions'),
            'UpdateLiveScoresJob'        => Cache::get('last_run_update_scores'),
            'UpdatePredictionResultsJob' => Cache::get('last_run_update_results'),
            'SendDailyNotificationJob'   => Cache::get('last_run_send_notifications'),
        ];

        // ── Historique 7 jours (stocké progressivement) ───────────────────
        $history7d = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $history7d[] = [
                'date'     => $date,
                'football' => (int) Cache::get('football_api_daily_requests_' . $date, 0),
                'rapidapi' => (int) Cache::get('rapidapi_total_calls_' . $date, 0),
            ];
        }

        // ── Statut Redis ───────────────────────────────────────────────────
        $redisOk = true;
        try {
            Cache::put('_health_check', 1, 5);
            Cache::get('_health_check');
        } catch (\Throwable) {
            $redisOk = false;
        }

        // ── Prédictions générées aujourd'hui ──────────────────────────────
        $predictionsToday = DB::table('predictions')
            ->whereDate('created_at', $today)
            ->count();

        return view('admin.api_monitor.index', compact(
            'footballDailyUsed', 'footballDailyLimit',
            'footballMinUsed',   'footballMinLimit',
            'rapidApiCalls',     'endpointCalls',
            'lastJobs',          'history7d',
            'redisOk',           'predictionsToday',
        ));
    }
}
