<?php

namespace App\Console\Commands;

use App\Services\FootballApiService;
use App\Services\PredictionAlgorithmService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class TestPredictionAlgorithm extends Command
{
    protected $signature = 'prediction:test
                            {--date= : Date des matchs (YYYY-MM-DD, defaut: aujourd\'hui)}
                            {--limit=3 : Nombre de matchs a tester}
                            {--details : Afficher les details de chaque critere}';

    protected $description = 'Tester l\'algorithme de prediction a 9 criteres sur des matchs reels (API-Football)';

    public function handle(FootballApiService $footballApi, PredictionAlgorithmService $algorithm): int
    {
        $limit       = (int) $this->option('limit');
        $showDetails = $this->option('details');

        $this->info('========================================');
        $this->info('  COTA - Test Algorithme 9 Criteres v3.0');
        $this->info('========================================');
        $this->newLine();

        $this->info('Poids des criteres:');
        $this->table(
            ['Critere', 'Poids (%)'],
            [
                ['Forme recente', '25'], ['Confrontations H2H', '20'],
                ['Domicile/Exterieur', '15'], ['Classement', '12'],
                ['Statistiques buts', '10'], ['Horaire match', '8'],
                ['Conditions meteo', '5'], ['Tirs cadres', '3'],
                ['Forme physique', '2'],
            ]
        );
        $this->newLine();

        $this->info('Recuperation des matchs depuis API-Football...');

        $response = $footballApi->getUpcomingMatches(1);
        $fixtures  = $response['response'] ?? [];

        if (empty($fixtures)) {
            $this->error('Aucun match trouve pour aujourd\'hui.');
            return 1;
        }

        $fixtures = array_slice($fixtures, 0, $limit);
        $this->info('Matchs trouves: ' . count($response['response']) . " (test sur {$limit})");
        $this->newLine();

        $results = [];

        foreach ($fixtures as $index => $fixture) {
            $homeTeam   = $fixture['teams']['home']['name'] ?? 'Domicile';
            $awayTeam   = $fixture['teams']['away']['name'] ?? 'Exterieur';
            $league     = $fixture['league']['name'] ?? 'Unknown';
            $startTime  = $fixture['fixture']['date'] ?? null;

            $this->info('----------------------------------------');
            $this->info('Match ' . ($index + 1) . ": {$homeTeam} vs {$awayTeam}");
            $this->info("Competition: {$league}");
            $this->info('Heure: ' . ($startTime ? Carbon::parse($startTime)->format('H:i') : 'N/A'));
            $this->newLine();

            $this->info('Analyse en cours...');
            $prediction = $algorithm->generatePrediction($fixture);

            if ($showDetails) {
                $this->info('Scores detailles:');
                $this->table(
                    ['Critere', 'Score', 'Max', '%'],
                    [
                        ['Forme',      $prediction['scores']['form'],      25, round($prediction['scores']['form'] / 25 * 100)],
                        ['H2H',        $prediction['scores']['h2h'],       20, round($prediction['scores']['h2h'] / 20 * 100)],
                        ['Dom/Ext',    $prediction['scores']['home_away'], 15, round($prediction['scores']['home_away'] / 15 * 100)],
                        ['Classement', $prediction['scores']['league'],    12, round($prediction['scores']['league'] / 12 * 100)],
                        ['Buts',       $prediction['scores']['goals'],     10, round($prediction['scores']['goals'] / 10 * 100)],
                        ['Horaire',    $prediction['scores']['time'],       8, round($prediction['scores']['time'] / 8 * 100)],
                        ['Meteo',      $prediction['scores']['weather'],    5, round($prediction['scores']['weather'] / 5 * 100)],
                        ['Tirs',       $prediction['scores']['shots'],      3, round($prediction['scores']['shots'] / 3 * 100)],
                        ['Physique',   $prediction['scores']['physical'],   2, round($prediction['scores']['physical'] / 2 * 100)],
                    ]
                );
            }

            $starsDisplay = str_repeat('<fg=yellow>★</>', $prediction['stars']) . str_repeat('<fg=gray>☆</>', 4 - $prediction['stars']);

            $this->newLine();
            $this->info('=== PRONOSTIC ===');
            $this->line("Type de pari: <fg=cyan>{$prediction['type']}</>");
            $this->line("Pronostic: <fg=yellow>{$prediction['outcome']}</>");
            $this->line("Cote estimee: <fg=green>{$prediction['odds']}</>");
            $this->line("Score total: <fg=magenta>{$prediction['confidence']}/100</>");
            $this->line("Confiance: {$starsDisplay} ({$prediction['stars']}/4)");
            $this->line('Premium: ' . ($prediction['is_premium'] ? '<fg=green>Oui</>' : '<fg=red>Non</>'));
            $this->newLine();
            $this->line("<fg=gray>{$prediction['reasoning']}</>");

            $results[] = [
                'match'      => "{$homeTeam} vs {$awayTeam}",
                'prediction' => $prediction['outcome'],
                'type'       => $prediction['type'],
                'score'      => $prediction['confidence'],
                'stars'      => $prediction['stars'],
                'odds'       => $prediction['odds'],
            ];
        }

        $this->newLine();
        $this->info('========================================');
        $this->info('  RESUME DES PRONOSTICS');
        $this->info('========================================');

        $this->table(
            ['Match', 'Pronostic', 'Type', 'Score', 'Etoiles', 'Cote'],
            array_map(fn($r) => [substr($r['match'], 0, 30), $r['prediction'], $r['type'], $r['score'], str_repeat('*', $r['stars']), $r['odds']], $results)
        );

        $avgScore     = count($results) > 0 ? array_sum(array_column($results, 'score')) / count($results) : 0;
        $premiumCount = count(array_filter($results, fn($r) => $r['score'] >= 70));

        $this->newLine();
        $this->info('Statistiques:');
        $this->line('- Score moyen: ' . round($avgScore, 1) . '/100');
        $this->line("- Pronostics premium (>=70): {$premiumCount}/" . count($results));
        $this->line('- Pronostics publies (>=50): ' . count(array_filter($results, fn($r) => $r['score'] >= 50)) . '/' . count($results));

        return 0;
    }
}
