<?php

namespace App\Console\Commands;

use App\Models\Bookmaker;
use App\Services\BookmakerEnrichmentService;
use Illuminate\Console\Command;

class EnrichBookmakers extends Command
{
    protected $signature = 'bookmakers:enrich
                            {--id= : Enrichir un seul bookmaker par ID}
                            {--force : Forcer même si déjà enrichi récemment}
                            {--dry-run : Afficher ce qui serait fait sans écrire en base}';

    protected $description = 'Enrichit automatiquement les fiches bookmakers via Claude AI (méthodes paiement, bonus, note...)';

    public function handle(BookmakerEnrichmentService $service): int
    {
        $id    = $this->option('id');
        $force = (bool) $this->option('force');
        $dry   = (bool) $this->option('dry-run');

        if (!config('services.anthropic.key') && !env('ANTHROPIC_API_KEY')) {
            $this->error('ANTHROPIC_API_KEY non configurée dans .env');
            $this->line('  Ajoute : ANTHROPIC_API_KEY=sk-ant-...');
            return self::FAILURE;
        }

        $query = Bookmaker::active();
        if ($id) {
            $query->where('id', $id);
        }
        $bookmakers = $query->get();

        if ($bookmakers->isEmpty()) {
            $this->warn('Aucun bookmaker actif trouvé.');
            return self::SUCCESS;
        }

        $this->info("🤖 Enrichissement de {$bookmakers->count()} bookmaker(s) via Claude...");
        $this->newLine();

        $updated = 0;
        $skipped = 0;
        $failed  = 0;

        foreach ($bookmakers as $bm) {
            $this->line("  → <fg=cyan>{$bm->name}</>");

            if ($dry) {
                $this->line("     [dry-run] serait enrichi");
                continue;
            }

            $result = $service->enrich($bm, $force);

            if ($result) {
                $bm->refresh();
                $deposit = implode(', ', $bm->deposit_methods ?? []);
                $this->line("     <fg=green>✓ Dépôt : {$deposit}</>");
                $this->line("     <fg=green>✓ Bonus  : {$bm->bonus_label}</>");
                $updated++;
            } else {
                $this->line("     <fg=yellow>↷ Ignoré (déjà enrichi ou erreur)</>");
                $skipped++;
            }

            // Pause entre les appels pour ne pas saturer l'API
            if ($bookmakers->count() > 1) {
                sleep(1);
            }
        }

        $this->newLine();
        $this->info("✅ Résultat : {$updated} enrichi(s) · {$skipped} ignoré(s) · {$failed} erreur(s)");

        return self::SUCCESS;
    }
}
