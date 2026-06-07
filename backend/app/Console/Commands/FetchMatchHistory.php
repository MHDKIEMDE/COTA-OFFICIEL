<?php

namespace App\Console\Commands;

use App\Services\TheSportsDbService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class FetchMatchHistory extends Command
{
    protected $signature = 'matches:fetch-history
                            {--days=1 : Nombre de jours à récupérer (1 = hier seulement)}
                            {--date= : Date précise à récupérer (format Y-m-d)}';
    protected $description = 'Récupère les résultats des matchs passés via TheSportsDB (gratuit, sans quota)';

    public function handle(TheSportsDbService $sportsDb): int
    {
        // Mode date précise
        if ($specificDate = $this->option('date')) {
            return $this->fetchDate($sportsDb, $specificDate);
        }

        $days         = (int) $this->option('days');
        $totalSaved   = 0;
        $totalUpdated = 0;
        $totalFinished = 0;
        $errors       = 0;

        $label = $days === 1 ? 'hier' : "les {$days} derniers jours";
        $this->info("Récupération des résultats de {$label} via TheSportsDB...");
        $this->newLine();

        $bar = $this->output->createProgressBar($days);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->start();

        for ($i = 1; $i <= $days; $i++) {
            $date = Carbon::today()->subDays($i)->format('Y-m-d');
            $bar->setMessage($date);

            try {
                [$saved, $updated, $finished] = $this->fetchAndCount($sportsDb, $date);
                $totalSaved    += $saved;
                $totalUpdated  += $updated;
                $totalFinished += $finished;
            } catch (\Throwable $e) {
                $errors++;
                \Illuminate\Support\Facades\Log::error("FetchMatchHistory: erreur {$date}", ['error' => $e->getMessage()]);
            }

            $bar->advance();

            // Pause 200ms entre chaque jour pour ne pas surcharger TheSportsDB
            if ($days > 1) usleep(200_000);
        }

        $bar->finish();
        $this->newLine(2);

        $this->printSummary($days, $totalSaved, $totalUpdated, $totalFinished, $errors);

        return self::SUCCESS;
    }

    private function fetchDate(TheSportsDbService $sportsDb, string $date): int
    {
        $this->info("Récupération des résultats du {$date}...");
        [$saved, $updated, $finished] = $this->fetchAndCount($sportsDb, $date);
        $this->printSummary(1, $saved, $updated, $finished, 0);
        return self::SUCCESS;
    }

    private function fetchAndCount(TheSportsDbService $sportsDb, string $date): array
    {
        // Bypass du cache pour s'assurer d'avoir les scores finaux à jour
        \Illuminate\Support\Facades\Cache::forget("thesportsdb_matches_{$date}");

        $result  = $sportsDb->fetchAndStoreMatches($date);
        $saved   = $result['saved'];
        $updated = $result['updated'];

        $finished = \App\Models\FootballMatch::where('match_id', 'like', 'tsdb_%')
            ->whereDate('match_date', $date)
            ->where('status', 'finished')
            ->whereNotNull('home_score')
            ->count();

        return [$saved, $updated, $finished];
    }

    private function printSummary(int $days, int $saved, int $updated, int $finished, int $errors): void
    {
        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Jours parcourus',              $days],
                ['Matchs créés',                 $saved],
                ['Matchs mis à jour',            $updated],
                ['Matchs terminés (avec score)', $finished],
                ['Erreurs',                      $errors],
            ]
        );

        if ($finished > 0) {
            $this->info("✓ {$finished} matchs terminés en base — l'algorithme dispose de l'historique.");
        } else {
            $this->warn('⚠ Aucun match terminé trouvé. TheSportsDB peut avoir un délai (~24h).');
        }
    }
}
