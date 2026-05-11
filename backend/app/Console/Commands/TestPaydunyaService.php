<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PaydunyaService;

class TestPaydunyaService extends Command
{
    protected $signature = 'paydunya:test';
    protected $description = 'Tester la connexion et les fonctionnalités Paydunya';

    public function handle(PaydunyaService $paydunya): int
    {
        $this->info('🔍 Test de l\'intégration Paydunya');
        $this->newLine();

        // Test 1: Connexion
        $this->line('Test 1: Connexion à l\'API Paydunya...');
        $connected = $paydunya->testConnection();

        if ($connected) {
            $this->info('✅ Connexion réussie !');
        } else {
            $this->error('❌ Échec de connexion à l\'API Paydunya');
            $this->warn('Vérifiez vos clés API dans le fichier .env');
            return Command::FAILURE;
        }

        $this->newLine();

        // Test 2: Création d'une facture test
        $this->line('Test 2: Création d\'une facture de test...');

        $testInvoice = $paydunya->createInvoice([
            'amount' => 2500,
            'description' => 'Test - Abonnement COTA Premium - 7 jours',
            'user_id' => 1,
            'user_email' => 'test@cota.com',
            'user_name' => 'Utilisateur Test',
            'user_phone' => '+226 00 00 00 00',
            'plan' => 'weekly',
        ]);

        if ($testInvoice['success']) {
            $this->info('✅ Facture créée avec succès !');
            $this->newLine();
            $this->line('📄 Détails de la facture :');
            $this->line("   Token: {$testInvoice['token']}");
            $this->line("   URL de paiement: {$testInvoice['url']}");
            $this->newLine();
            $this->warn('⚠️  Attention : Cette facture est en mode TEST');
        } else {
            $this->error('❌ Échec de création de facture');
            $this->line('Message: ' . ($testInvoice['message'] ?? 'Erreur inconnue'));
            return Command::FAILURE;
        }

        $this->newLine();

        // Test 3: Tarification
        $this->line('Test 3: Vérification de la tarification...');
        $plans = [
            'weekly' => $paydunya->getPlanPrice('weekly'),
            'monthly' => $paydunya->getPlanPrice('monthly'),
            'quarterly' => $paydunya->getPlanPrice('quarterly'),
        ];

        $this->table(
            ['Plan', 'Prix (FCFA)', 'Durée'],
            [
                ['Hebdomadaire', number_format($plans['weekly']), '7 jours'],
                ['Mensuel', number_format($plans['monthly']), '30 jours'],
                ['Trimestriel', number_format($plans['quarterly']), '90 jours'],
            ]
        );

        $this->newLine();

        // Test 4: Calcul des dates d'expiration
        $this->line('Test 4: Calcul des dates d\'expiration...');
        $expirations = [
            'Hebdomadaire' => $paydunya->calculateExpirationDate('weekly')->format('d/m/Y'),
            'Mensuel' => $paydunya->calculateExpirationDate('monthly')->format('d/m/Y'),
            'Trimestriel' => $paydunya->calculateExpirationDate('quarterly')->format('d/m/Y'),
        ];

        foreach ($expirations as $plan => $date) {
            $this->line("   $plan : Expire le $date");
        }

        $this->newLine();
        $this->info('✅ Tous les tests ont réussi !');
        $this->newLine();

        // Instructions pour la suite
        $this->line('📌 Prochaines étapes :');
        $this->line('   1. Configurer vos clés Paydunya dans .env');
        $this->line('   2. Passer en mode LIVE quand prêt (PAYDUNYA_MODE=live)');
        $this->line('   3. Tester un paiement réel via l\'application mobile');

        return Command::SUCCESS;
    }
}
