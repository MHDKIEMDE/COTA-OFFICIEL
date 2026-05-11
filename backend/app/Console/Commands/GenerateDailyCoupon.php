<?php

namespace App\Console\Commands;

use App\Services\FootballApiService;
use App\Services\PredictionAlgorithmService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateDailyCoupon extends Command
{
    protected $signature = 'prediction:coupon
                            {--limit=30 : Nombre de matchs populaires à analyser}
                            {--min=4 : Nombre minimum de picks dans le coupon}
                            {--max=5 : Nombre maximum de picks dans le coupon}
                            {--confidence=60 : Score minimum de confiance (0-100)}';

    protected $description = 'Analyse les matchs populaires du jour et génère un coupon combiné IA';

    public function handle(FootballApiService $footballApi, PredictionAlgorithmService $algorithm): int
    {
        $limit      = (int) $this->option('limit');
        $minPicks   = (int) $this->option('min');
        $maxPicks   = (int) $this->option('max');
        $minConf    = (float) $this->option('confidence');

        $this->info('============================================');
        $this->info('  COTA — Coupon IA  |  ' . Carbon::today()->format('d/m/Y'));
        $this->info('============================================');
        $this->newLine();

        // 1. Récupérer les matchs populaires
        $this->info('Récupération des matchs populaires...');
        $response = $footballApi->getPopularMatches();
        $fixtures = $response['response'] ?? [];

        if (empty($fixtures)) {
            $this->error('Aucun match populaire trouvé pour aujourd\'hui.');
            return 1;
        }

        $total = count($fixtures);
        $fixtures = array_slice($fixtures, 0, $limit);

        $this->info("Matchs disponibles: {$total} au total, analyse des {$limit} premiers");
        $this->newLine();

        // Afficher la répartition par ligue
        $leagueGroups = [];
        foreach ($fixtures as $f) {
            $key = $f['league']['name'] ?? 'Unknown';
            $leagueGroups[$key] = ($leagueGroups[$key] ?? 0) + 1;
        }
        arsort($leagueGroups);

        $this->info('Répartition par compétition :');
        foreach (array_slice($leagueGroups, 0, 10, true) as $league => $count) {
            $this->line("  <fg=cyan>{$league}</>: {$count} match(s)");
        }
        $this->newLine();

        // 2. Générer le coupon
        $this->info("Analyse IA en cours ({$limit} matchs, confiance min {$minConf}%)...");
        $bar = $this->output->createProgressBar(count($fixtures));
        $bar->start();

        // On passe tous les fixtures à generateDailyCoupon qui les analyse en interne
        // Pour afficher la barre de progression on va les pré-analyser ici
        $analyzed = [];
        foreach ($fixtures as $fixture) {
            $prediction = $algorithm->generatePrediction($fixture);
            $analyzed[] = ['fixture' => $fixture, 'prediction' => $prediction];
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Passer les fixtures pré-analysés à la méthode coupon
        // On reformate pour que generateDailyCoupon reçoive juste les fixtures
        // (elle regénère les prédictions en interne, mais c'est ok pour le cache)
        $coupon = $algorithm->generateDailyCoupon($fixtures, $minPicks, $maxPicks, $minConf);

        if (!$coupon['success']) {
            $this->warn($coupon['message']);
            $this->line("Matchs analysés : {$coupon['analyzed']} | Qualifiés : {$coupon['qualified']}");
            return 1;
        }

        // 3. Afficher les picks du coupon
        $this->info('============================================');
        $this->info('  COUPON DU JOUR  —  ' . $coupon['matches_count'] . ' sélections');
        $this->info('============================================');
        $this->newLine();

        foreach ($coupon['picks'] as $i => $pick) {
            $stars    = str_repeat('★', $pick['stars']) . str_repeat('☆', 4 - $pick['stars']);
            $time     = $pick['date'] ? Carbon::parse($pick['date'])->format('H:i') : '--:--';
            $premium  = $pick['is_premium'] ? '<fg=yellow>[PREMIUM]</>' : '';
            $this->line(sprintf(
                '  <fg=green>%d.</> <fg=white>%s</> %s',
                $i + 1,
                $pick['match'],
                $premium
            ));
            $this->line("     <fg=gray>{$pick['league']}</> — {$time}");
            $this->line(sprintf(
                '     Pari: <fg=cyan>%s %s</>  |  Cote: <fg=green>%s</>  |  Confiance: <fg=magenta>%s/100</>  |  %s',
                $pick['type'],
                $pick['prediction'],
                $pick['odds'],
                $pick['confidence'],
                $stars
            ));
            $this->newLine();
        }

        // 4. Résumé coupon
        $this->info('============================================');
        $totalStars   = str_repeat('★', $coupon['stars']) . str_repeat('☆', 4 - $coupon['stars']);
        $this->line("  Cote totale combinée  : <fg=green>{$coupon['total_odds']}</>");
        $this->line("  Confiance moyenne     : <fg=magenta>{$coupon['avg_confidence']}/100</>");
        $this->line("  Qualité coupon        : {$totalStars} ({$coupon['stars']}/4)");
        $this->line("  Gain potentiel 1 000  : <fg=yellow>{$coupon['potential_gain_1000']} FCFA</>");
        $this->line("  Matchs analysés       : {$coupon['analyzed']} | Qualifiés : {$coupon['qualified']}");
        $this->info('============================================');

        return 0;
    }
}
