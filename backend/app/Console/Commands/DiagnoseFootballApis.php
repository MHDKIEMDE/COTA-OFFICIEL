<?php

namespace App\Console\Commands;

use App\Services\FootballApiService;
use Illuminate\Console\Command;

class DiagnoseFootballApis extends Command
{
    protected $signature   = 'football:diagnose';
    protected $description = 'Diagnostique la connexion et les quotas API-Football';

    public function handle(FootballApiService $footballApi): int
    {
        $this->line('🏆 Diagnostique API-Football');
        $this->line('============================');

        $stats = $footballApi->getUsageStats();
        $this->line('📊 Utilisation des quotas:');
        $this->line("  Requêtes aujourd'hui : {$stats['daily']['used']} / {$stats['daily']['limit']}");
        $this->line("  Restantes            : {$stats['daily']['remaining']}");
        $this->line("  Plan actif           : {$stats['plan']}");

        $this->newLine();
        $this->line('🧪 Test de connexion:');

        try {
            $response = $footballApi->getLiveMatches();
            $this->line($response !== null ? '  ✅ API-Football: Accessible' : '  ⚠️  API-Football: Réponse vide');
        } catch (\Exception $e) {
            $this->line('  ❌ API-Football: ' . $e->getMessage());
        }

        $this->newLine();
        $this->line('📋 Commandes utiles:');
        $this->line('  php artisan football:diagnose');
        $this->line('  php artisan cache:clear');

        return Command::SUCCESS;
    }
}
