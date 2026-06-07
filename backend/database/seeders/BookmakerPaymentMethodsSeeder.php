<?php

namespace Database\Seeders;

use App\Models\Bookmaker;
use Illuminate\Database\Seeder;

/**
 * Pré-remplit les méthodes de paiement des bookmakers connus.
 * Ces données sont publiques et stables — pas besoin d'appeler Claude pour elles.
 * Claude est utilisé pour les bookmakers non listés ici via bookmakers:enrich.
 *
 * Usage : php artisan db:seed --class=BookmakerPaymentMethodsSeeder
 */
class BookmakerPaymentMethodsSeeder extends Seeder
{
    private const KNOWN = [
        '1xbet' => [
            'deposit_methods'    => ['Wave', 'Orange Money', 'MTN Mobile Money', 'Moov Africa', 'Airtel Money', 'Carte bancaire', 'Crypto (USDT/BTC)', 'Virement bancaire'],
            'withdrawal_methods' => ['Wave', 'Orange Money', 'MTN Mobile Money', 'Moov Africa', 'Carte bancaire', 'Crypto (USDT/BTC)'],
            'min_deposit'        => 300,
            'min_withdrawal'     => 1500,
            'bonus_label'        => '100% jusqu\'à 130 000 FCFA',
            'rating'             => 3.8,
            'popular_rank'       => 2,
        ],
        'betwinner' => [
            'deposit_methods'    => ['Wave', 'Orange Money', 'MTN Mobile Money', 'Moov Africa', 'Carte bancaire', 'Crypto (USDT/BTC)', 'Virement bancaire'],
            'withdrawal_methods' => ['Wave', 'Orange Money', 'MTN Mobile Money', 'Moov Africa', 'Carte bancaire', 'Crypto (USDT/BTC)'],
            'min_deposit'        => 300,
            'min_withdrawal'     => 1500,
            'bonus_label'        => '100% jusqu\'à 75 000 FCFA',
            'rating'             => 3.9,
            'popular_rank'       => 4,
        ],
        'melbet' => [
            'deposit_methods'    => ['Wave', 'Orange Money', 'MTN Mobile Money', 'Moov Africa', 'Carte bancaire', 'Crypto'],
            'withdrawal_methods' => ['Wave', 'Orange Money', 'MTN Mobile Money', 'Carte bancaire', 'Crypto'],
            'min_deposit'        => 300,
            'min_withdrawal'     => 1500,
            'bonus_label'        => '100% jusqu\'à 130 000 FCFA',
            'rating'             => 3.7,
            'popular_rank'       => 5,
        ],
        'bet365' => [
            'deposit_methods'    => ['Carte bancaire', 'Virement bancaire', 'PayPal', 'Neteller', 'Skrill'],
            'withdrawal_methods' => ['Carte bancaire', 'Virement bancaire', 'PayPal', 'Neteller', 'Skrill'],
            'min_deposit'        => 6560,
            'min_withdrawal'     => 6560,
            'bonus_label'        => 'Jusqu\'à 100 € en crédits de paris',
            'rating'             => 4.5,
            'popular_rank'       => 1,
        ],
        'betclic' => [
            'deposit_methods'    => ['Carte bancaire', 'Virement bancaire', 'PayPal', 'Skrill'],
            'withdrawal_methods' => ['Carte bancaire', 'Virement bancaire', 'PayPal', 'Skrill'],
            'min_deposit'        => 6560,
            'min_withdrawal'     => 6560,
            'bonus_label'        => '100% jusqu\'à 50 €',
            'rating'             => 4.2,
            'popular_rank'       => 7,
        ],
        'sportybet' => [
            'deposit_methods'    => ['Wave', 'Orange Money', 'MTN Mobile Money', 'Moov Africa', 'Airtel Money'],
            'withdrawal_methods' => ['Wave', 'Orange Money', 'MTN Mobile Money', 'Moov Africa', 'Airtel Money'],
            'min_deposit'        => 500,
            'min_withdrawal'     => 1000,
            'bonus_label'        => 'Bonus de bienvenue 50%',
            'rating'             => 4.0,
            'popular_rank'       => 6,
        ],
        'betway' => [
            'deposit_methods'    => ['Orange Money', 'MTN Mobile Money', 'Carte bancaire', 'Virement bancaire'],
            'withdrawal_methods' => ['Orange Money', 'MTN Mobile Money', 'Carte bancaire'],
            'min_deposit'        => 1000,
            'min_withdrawal'     => 2000,
            'bonus_label'        => '50% jusqu\'à 50 000 FCFA',
            'rating'             => 4.1,
            'popular_rank'       => 8,
        ],
        'parimatch' => [
            'deposit_methods'    => ['Wave', 'Orange Money', 'MTN Mobile Money', 'Carte bancaire', 'Crypto'],
            'withdrawal_methods' => ['Wave', 'Orange Money', 'MTN Mobile Money', 'Crypto'],
            'min_deposit'        => 500,
            'min_withdrawal'     => 1000,
            'bonus_label'        => '100% jusqu\'à 100 000 FCFA',
            'rating'             => 3.6,
            'popular_rank'       => 10,
        ],
        '22bet' => [
            'deposit_methods'    => ['Wave', 'Orange Money', 'MTN Mobile Money', 'Carte bancaire', 'Crypto (USDT/BTC)'],
            'withdrawal_methods' => ['Wave', 'Orange Money', 'MTN Mobile Money', 'Crypto (USDT/BTC)'],
            'min_deposit'        => 300,
            'min_withdrawal'     => 1500,
            'bonus_label'        => '100% jusqu\'à 36 000 FCFA',
            'rating'             => 3.7,
            'popular_rank'       => 9,
        ],
        'linebet' => [
            'deposit_methods'    => ['Wave', 'Orange Money', 'MTN Mobile Money', 'Carte bancaire', 'Crypto'],
            'withdrawal_methods' => ['Wave', 'Orange Money', 'MTN Mobile Money', 'Crypto'],
            'min_deposit'        => 300,
            'min_withdrawal'     => 1000,
            'bonus_label'        => '100% jusqu\'à 130 000 FCFA',
            'rating'             => 3.5,
            'popular_rank'       => 12,
        ],
        'mostbet' => [
            'deposit_methods'    => ['Wave', 'Orange Money', 'MTN Mobile Money', 'Carte bancaire', 'Crypto'],
            'withdrawal_methods' => ['Wave', 'Orange Money', 'MTN Mobile Money', 'Crypto'],
            'min_deposit'        => 500,
            'min_withdrawal'     => 1500,
            'bonus_label'        => '125% + 250 tours gratuits',
            'rating'             => 3.6,
            'popular_rank'       => 11,
        ],
    ];

    public function run(): void
    {
        $bookmakers = Bookmaker::all();
        $updated = 0;

        foreach ($bookmakers as $bm) {
            $slug = strtolower($bm->slug ?? $bm->name);

            // Chercher une correspondance dans les données connues
            $data = null;
            foreach (self::KNOWN as $key => $info) {
                if (str_contains($slug, $key) || str_contains($key, $slug)) {
                    $data = $info;
                    break;
                }
            }

            if (!$data) {
                $this->command?->line("  <fg=yellow>↷ {$bm->name}</> — données inconnues (lance bookmakers:enrich)");
                continue;
            }

            $updates = [
                'deposit_methods'    => $data['deposit_methods'],
                'withdrawal_methods' => $data['withdrawal_methods'],
                'min_deposit'        => $data['min_deposit'],
                'min_withdrawal'     => $data['min_withdrawal'],
                'popular_rank'       => $bm->popular_rank ?? $data['popular_rank'],
            ];

            // Ne pas écraser un bonus ou note déjà saisis manuellement
            if (empty($bm->bonus_label)) {
                $updates['bonus_label'] = $data['bonus_label'];
            }
            if (empty($bm->rating)) {
                $updates['rating'] = $data['rating'];
            }

            $bm->update($updates);
            $updated++;
            $this->command?->line("  <fg=green>✓ {$bm->name}</> — " . count($data['deposit_methods']) . ' méthodes dépôt');
        }

        $this->command?->info("Seeder terminé : {$updated}/{$bookmakers->count()} bookmakers mis à jour.");
    }
}
