<?php

namespace App\Console\Commands;

use App\Models\FootballMatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Importe l'historique des matchs des sélections nationales (forme + H2H)
 * depuis API-Football, en contournant la limite du plan gratuit :
 *   - le paramètre `last` est interdit  → on passe par `season`
 *   - la saison 2026 est interdite      → on utilise les saisons 2022-2024
 *
 * Ces matchs alimentent FootballMatch (status=finished) pour que
 * PredictionAlgorithmService calcule une vraie forme sur les équipes
 * nationales (Coupe du monde) au lieu de retomber sur ses valeurs neutres.
 */
class ImportNationalTeamMatches extends Command
{
    protected $signature = 'wc:import-teams
        {--teams= : IDs API-Football séparés par virgule (ex: 2,1567). Vide = déduit des matchs à venir en base}
        {--days=7 : Fenêtre de matchs à venir (jours) pour déduire les équipes quand --teams est vide}
        {--seasons=2024,2023 : Saisons à importer (autorisées en gratuit : 2022-2024)}
        {--max-requests=60 : Plafond d\'appels API pour préserver le quota gratuit (100/jour)}';

    protected $description = 'Importe l\'historique des sélections nationales (forme/H2H) pour les pronostics Coupe du monde';

    private const BASE = 'https://v3.football.api-sports.io';

    public function handle(): int
    {
        $key = config('football-api.api_key') ?: env('FOOTBALL_API_KEY');
        if (!$key) {
            $this->error('FOOTBALL_API_KEY absente.');
            return self::FAILURE;
        }

        $teamIds = $this->resolveTeamIds();
        if (empty($teamIds)) {
            $this->warn('Aucune équipe à importer. Passe --teams=ID,ID ou ajoute des matchs WC en base.');
            return self::SUCCESS;
        }

        $seasons     = array_filter(array_map('trim', explode(',', (string) $this->option('seasons'))));
        $maxRequests = (int) $this->option('max-requests');

        $this->info(sprintf('Import de %d équipe(s) × %d saison(s) — plafond %d appels.', count($teamIds), count($seasons), $maxRequests));

        $requests = 0;
        $imported = 0;

        foreach ($teamIds as $teamId) {
            foreach ($seasons as $season) {
                if ($requests >= $maxRequests) {
                    $this->warn("Plafond d'appels atteint ($maxRequests). Arrêt — relance demain ou avec --max-requests plus haut.");
                    $this->summary($imported, $requests);
                    return self::SUCCESS;
                }

                $requests++;
                $fixtures = $this->fetchTeamSeason($key, (int) $teamId, (int) $season);

                if ($fixtures === null) {
                    $this->line("  ⚠️  team=$teamId season=$season : quota API épuisé, arrêt.");
                    $this->summary($imported, $requests);
                    return self::SUCCESS;
                }

                $count = $this->saveFixtures($fixtures);
                $imported += $count;
                $this->line("  ✓ team=$teamId season=$season : $count match(s) finis importés");

                usleep(300_000); // ~3 req/s, respecte la limite minute du plan gratuit
            }
        }

        $this->summary($imported, $requests);
        return self::SUCCESS;
    }

    /**
     * IDs explicites via --teams, sinon déduit des matchs WC à venir en base.
     *
     * @return int[]
     */
    private function resolveTeamIds(): array
    {
        if ($opt = $this->option('teams')) {
            return array_values(array_unique(array_filter(array_map('intval', explode(',', $opt)))));
        }

        $days = max(1, (int) $this->option('days'));
        $rows = FootballMatch::whereBetween('match_date', [now()->startOfDay(), now()->addDays($days)->endOfDay()])
            ->where('status', '!=', 'finished')
            ->get(['home_team_id', 'away_team_id']);

        $ids = [];
        foreach ($rows as $r) {
            if ($r->home_team_id) $ids[] = (int) $r->home_team_id;
            if ($r->away_team_id) $ids[] = (int) $r->away_team_id;
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * Récupère les fixtures d'une équipe sur une saison.
     * @return array|null  null = quota API épuisé (on arrête proprement)
     */
    private function fetchTeamSeason(string $key, int $teamId, int $season): ?array
    {
        $resp = Http::withHeaders(['x-apisports-key' => $key])
            ->timeout(20)
            ->get(self::BASE . '/fixtures', ['team' => $teamId, 'season' => $season]);

        $json   = $resp->json();
        $errors = $json['errors'] ?? [];

        if (!empty($errors['requests']) || !empty($errors['rateLimit'])) {
            return null;
        }
        if (!empty($errors)) {
            $this->line('    info API: ' . json_encode($errors));
        }

        return $json['response'] ?? [];
    }

    /**
     * Sauvegarde uniquement les matchs terminés (status FT/AET/PEN), avec scores.
     */
    private function saveFixtures(array $fixtures): int
    {
        $finishedStatuses = ['FT', 'AET', 'PEN'];
        $saved = 0;

        foreach ($fixtures as $f) {
            $short = $f['fixture']['status']['short'] ?? '';
            if (!in_array($short, $finishedStatuses, true)) {
                continue;
            }
            if (($f['goals']['home'] ?? null) === null || ($f['goals']['away'] ?? null) === null) {
                continue;
            }

            $date = $f['fixture']['date'] ?? null;
            FootballMatch::updateOrCreate(
                ['match_id' => (string) ($f['fixture']['id'] ?? '')],
                [
                    'home_team_id'   => $f['teams']['home']['id'] ?? null,
                    'away_team_id'   => $f['teams']['away']['id'] ?? null,
                    'competition_id' => $f['league']['id'] ?? null,
                    'home_team'      => $f['teams']['home']['name'] ?? 'Unknown',
                    'away_team'      => $f['teams']['away']['name'] ?? 'Unknown',
                    'competition'    => $f['league']['name'] ?? 'Unknown',
                    'country'        => $f['league']['country'] ?? 'World',
                    'match_date'     => $date,
                    'match_time'     => $date ? substr($date, 11, 5) : null,
                    'home_score'     => $f['goals']['home'],
                    'away_score'     => $f['goals']['away'],
                    'status'         => 'finished',
                    'status_long'    => $f['fixture']['status']['long'] ?? 'Match Finished',
                ]
            );
            $saved++;
        }

        return $saved;
    }

    private function summary(int $imported, int $requests): void
    {
        $this->newLine();
        $this->info("Terminé : $imported match(s) importés en $requests appel(s) API.");
        $this->line('Relance la génération des pronostics pour profiter de la nouvelle forme.');
    }
}
