<?php

namespace App\Console\Commands;

use App\Jobs\UpdatePredictionResultsJob;
use App\Services\ZafronixService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Remplit les scores finaux des prédictions Coupe du Monde depuis Zafronix,
 * puis déclenche la résolution win/lost. Comble le trou de synchro : API-Football
 * ne connaît pas les matchs Mondial, donc UpdatePredictionResultsJob les laissait
 * bloqués en "pending" faute de score.
 */
class SyncWorldCupResults extends Command
{
    protected $signature = 'predictions:sync-worldcup-results {--year=2026}';
    protected $description = 'Récupère les scores finaux Mondial (Zafronix) et résout les prédictions win/lost';

    public function handle(ZafronixService $zafronix): int
    {
        $year   = (int) $this->option('year');
        $scores = $zafronix->getFinalScores($year);

        if (empty($scores)) {
            $this->warn('Aucun score final disponible chez Zafronix.');
            return self::SUCCESS;
        }

        // Prédictions Mondial passées, toujours sans score.
        $pending = DB::table('predictions')
            ->where('competition', 'World Cup')
            ->where('match_date', '<', Carbon::now())
            ->where(function ($q) {
                $q->whereNull('home_score')->orWhereNull('away_score');
            })
            ->get();

        $updated = 0;
        foreach ($pending as $p) {
            $key = strtolower(trim($p->home_team) . '|' . trim($p->away_team));
            if (!isset($scores[$key])) {
                continue;
            }

            DB::table('predictions')->where('id', $p->id)->update([
                'home_score' => $scores[$key]['home'],
                'away_score' => $scores[$key]['away'],
                'updated_at' => now(),
            ]);
            $updated++;
        }

        $this->info("Scores remplis : {$updated} prédiction(s) Mondial.");

        // Résolution win/lost immédiate sur les prédictions désormais scorées.
        (new UpdatePredictionResultsJob())->handle();
        $this->line('Résolution win/lost déclenchée.');

        return self::SUCCESS;
    }
}
