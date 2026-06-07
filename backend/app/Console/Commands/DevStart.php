<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class DevStart extends Command
{
    protected $signature   = 'cota:dev-start {--force : Regénérer même si des prédictions existent}';
    protected $description = 'Vérifie et prépare toutes les données backend pour le développement Flutter';

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=yellow;options=bold>COTA — Dev Start</> <fg=gray>v1.0</>');
        $this->line('  <fg=gray>─────────────────────────────────────────</>');
        $this->newLine();

        $today = Carbon::today()->toDateString();
        $force = $this->option('force');

        // ── 1. Scheduler ──────────────────────────────────────────────────────
        $schedulerRunning = $this->isProcessRunning('schedule:work');
        if ($schedulerRunning) {
            $this->checkLine('Scheduler actif');
        } else {
            $this->startBackground('php artisan schedule:work', 'storage/logs/scheduler.log');
            sleep(1);
            $this->checkLine('Scheduler démarré');
        }

        // ── 2. Queue worker ───────────────────────────────────────────────────
        $workerRunning = $this->isProcessRunning('queue:work');
        if ($workerRunning) {
            $this->checkLine('Queue worker actif');
        } else {
            $this->startBackground(
                'php artisan queue:work --sleep=3 --tries=3 --timeout=300',
                'storage/logs/queue-worker.log'
            );
            sleep(1);
            $this->checkLine('Queue worker démarré');
        }

        // ── 3. Matchs du jour ─────────────────────────────────────────────────
        $matchCount = DB::table('matches')->whereDate('match_date', $today)->count();

        if ($matchCount === 0 || $force) {
            $this->warnLine('Aucun match pour aujourd\'hui — fetch en cours...');
            $this->call('queue:work', ['--stop-when-empty' => true, '--timeout' => 120]);
            // Dispatcher puis attendre
            \App\Jobs\FetchMatchesJob::dispatchSync();
            $matchCount = DB::table('matches')->whereDate('match_date', $today)->count();
        }

        $this->checkLine("$matchCount matchs disponibles aujourd'hui");

        // ── 4. Prédictions ───────────────────────────────────────────────────
        $predCount = DB::table('predictions')
            ->where('is_published', true)
            ->whereDate('match_date', $today)
            ->count();

        if ($predCount === 0 || $force) {
            $this->warnLine('Aucune prédiction — génération en cours (1-2 min)...');
            \App\Jobs\GenerateAllPredictionsJob::dispatchSync();
            $predCount = DB::table('predictions')
                ->where('is_published', true)
                ->whereDate('match_date', $today)
                ->count();
        }

        if ($predCount > 0) {
            $this->checkLine("$predCount prédictions publiées aujourd'hui");
        } else {
            $this->errorLine('Aucune prédiction générée — vérifiez les logs');
        }

        // ── 5. Coupon ─────────────────────────────────────────────────────────
        $cacheKey = 'coupon_daily_' . $today;
        if ($force) {
            Cache::forget($cacheKey);
        }

        // Simuler l'appel coupon pour peupler le cache
        $coupon = $this->buildCoupon($today);

        if (($coupon['success'] ?? false) === true) {
            $picks     = count($coupon['picks'] ?? []);
            $totalOdds = $coupon['total_odds'] ?? 0;
            $stars     = str_repeat('★', $coupon['stars'] ?? 1);
            $this->checkLine("Coupon prêt — @{$totalOdds} ($picks picks · $stars)");
        } else {
            $msg = $coupon['message'] ?? 'Coupon indisponible';
            $this->warnLine("Coupon : $msg");
        }

        // ── 6. Résumé ─────────────────────────────────────────────────────────
        $this->newLine();
        $this->line('  <fg=gray>─────────────────────────────────────────</>');
        $this->line('  <fg=green;options=bold>✓ Backend prêt.</>  Lance maintenant :');
        $this->newLine();
        $this->line('  <fg=yellow>flutter run -d 21</>  (ou ton device ID)');
        $this->newLine();

        return self::SUCCESS;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function isProcessRunning(string $keyword): bool
    {
        $result = shell_exec("pgrep -fl '$keyword' 2>/dev/null");
        return !empty(trim((string) $result));
    }

    private function startBackground(string $command, string $logFile): void
    {
        $base = base_path();
        shell_exec("cd '$base' && nohup php $command >> $logFile 2>&1 &");
    }

    private function buildCoupon(string $date): array
    {
        $cacheKey = 'coupon_daily_' . $date;
        $ttl      = Carbon::tomorrow()->startOfDay()->diffInSeconds(Carbon::now());

        return Cache::remember($cacheKey, $ttl, function () use ($date) {
            $maxPicks   = 5;

            $popularPairs = array_filter(
                config('football-api.popular_leagues', []),
                fn($l) => $l['tier'] <= 3
            );
            $popularNames = array_column($popularPairs, 'name');

            $rows = DB::table('predictions')
                ->where('is_published', true)
                ->whereDate('match_date', $date)
                ->where(function ($q) use ($popularPairs) {
                    foreach ($popularPairs as $league) {
                        $q->orWhere(fn($sub) =>
                            $sub->where('competition', $league['name'])
                                ->where('country', $league['country'])
                        );
                    }
                })
                ->orderBy('total_score', 'desc')
                ->limit(50)
                ->get();

            if ($rows->count() < 2) {
                $rows = DB::table('predictions')
                    ->where('is_published', true)
                    ->whereDate('match_date', $date)
                    ->orderByRaw("CASE WHEN competition IN ('" . implode("','", $popularNames) . "') THEN 1 ELSE 2 END ASC")
                    ->orderBy('total_score', 'desc')
                    ->limit(50)
                    ->get();
            }

            if ($rows->count() === 0) {
                return ['success' => false, 'message' => 'Aucune prédiction pour le ' . $date, 'picks' => []];
            }

            $rows = $rows->filter(function ($row) {
                $analysis  = $row->analysis_details ? json_decode($row->analysis_details, true) : [];
                $agreement = $analysis['third_party']['agreement'] ?? null;
                return $agreement !== 'contradicts';
            })->values();

            $selected    = [];
            $usedLeagues = [];
            $minPicks    = max(2, min(4, $rows->count()));

            foreach ($rows as $row) {
                if (count($selected) >= $maxPicks) break;
                $league = $row->competition ?? 'unknown';
                if (!in_array($league, $usedLeagues)) {
                    $selected[]    = $row;
                    $usedLeagues[] = $league;
                }
            }

            if (count($selected) < $minPicks) {
                foreach ($rows as $row) {
                    if (count($selected) >= $minPicks) break;
                    if (empty(array_filter($selected, fn($s) => $s->id === $row->id))) {
                        $selected[] = $row;
                    }
                }
            }

            $totalOdds     = array_reduce($selected, fn($c, $p) => $c * (float)($p->odds ?? 1.0), 1.0);
            $avgConfidence = array_sum(array_map(fn($p) => (float)$p->total_score, $selected)) / count($selected);
            $stars         = match(true) {
                $avgConfidence >= 85 => 4,
                $avgConfidence >= 70 => 3,
                $avgConfidence >= 60 => 2,
                default              => 1,
            };

            $picks = array_map(function ($p) {
                $analysis  = $p->analysis_details ? json_decode($p->analysis_details, true) : [];
                return [
                    'match'          => $p->home_team . ' vs ' . $p->away_team,
                    'home_team'      => $p->home_team,
                    'away_team'      => $p->away_team,
                    'league'         => $p->competition ?? null,
                    'country'        => $p->country ?? null,
                    'date'           => $p->match_date,
                    'time'           => $p->match_time ?? null,
                    'prediction'     => $p->prediction,
                    'type'           => $p->bet_type,
                    'odds'           => (float)($p->odds ?? 1.0),
                    'confidence'     => round((float)$p->total_score, 1),
                    'stars'          => $p->confidence_stars,
                    'is_premium'     => (bool)$p->is_premium,
                    'home_team_logo' => $p->home_team_logo ?? null,
                    'away_team_logo' => $p->away_team_logo ?? null,
                    'reasoning'      => $analysis['reasoning'] ?? [],
                    'scores'         => $analysis['scores'] ?? [],
                    'agreement'      => $analysis['third_party']['agreement'] ?? null,
                    'corners_avg'    => isset($analysis['corners_avg']) ? (float)$analysis['corners_avg'] : null,
                    'cards_avg'      => isset($analysis['cards_avg']) ? (float)$analysis['cards_avg'] : null,
                ];
            }, $selected);

            return [
                'success'        => true,
                'date'           => $date,
                'picks'          => $picks,
                'total_odds'     => round($totalOdds, 2),
                'avg_confidence' => round($avgConfidence, 1),
                'stars'          => $stars,
            ];
        });
    }

    private function checkLine(string $msg): void
    {
        $this->line("  <fg=green>✓</> $msg");
    }

    private function warnLine(string $msg): void
    {
        $this->line("  <fg=yellow>⚡</> $msg");
    }

    private function errorLine(string $msg): void
    {
        $this->line("  <fg=red>✗</> $msg");
    }
}
