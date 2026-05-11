<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class AddPremiumUser extends Command
{
    protected $signature = 'user:add-premium 
                            {phone : Numéro de téléphone avec indicatif (ex: +22607443112)}
                            {--days=365 : Nombre de jours de premium (défaut: 365, mettre null pour premium à vie)}';

    protected $description = 'Ajouter un utilisateur comme premium par numéro de téléphone';

    public function handle(): int
    {
        $phone = $this->argument('phone');
        $days = $this->option('days');

        // Normaliser le numéro de téléphone (s'assurer qu'il commence par +)
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        $this->info("🔍 Recherche de l'utilisateur avec le numéro: {$phone}");

        // Chercher l'utilisateur
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            $this->warn("⚠️  Utilisateur non trouvé. Création d'un nouvel utilisateur...");
            
            // Extraire le code pays (premiers chiffres après le +)
            $countryCode = 'BF'; // Burkina Faso par défaut pour +226
            if (str_starts_with($phone, '+226')) {
                $countryCode = 'BF';
            } elseif (str_starts_with($phone, '+221')) {
                $countryCode = 'SN';
            } elseif (str_starts_with($phone, '+225')) {
                $countryCode = 'CI';
            }
            
            // Créer un nouvel utilisateur
            $user = User::create([
                'phone' => $phone,
                'name' => 'User_' . substr($phone, -4),
                'country_code' => $countryCode,
                'email' => null,
            ]);
            
            $this->info("✅ Nouvel utilisateur créé: {$user->name} (ID: {$user->id})");
        } else {
            $this->info("✅ Utilisateur trouvé: {$user->name} (ID: {$user->id})");
        }

        // Définir la date d'expiration
        if ($days === 'null' || $days === null) {
            // Premium à vie
            $expiresAt = null;
            $this->info("🌟 Attribution d'un premium À VIE");
        } else {
            $days = (int) $days;
            $expiresAt = Carbon::now()->addDays($days);
            $this->info("📅 Premium valable jusqu'au: {$expiresAt->format('d/m/Y H:i')}");
        }

        // Mettre à jour l'utilisateur
        $user->update([
            'is_premium' => true,
            'premium_expires_at' => $expiresAt,
            'premium_source' => 'subscription', // ou 'admin' si on veut un autre type
        ]);

        $this->newLine();
        $this->info("✅ Premium ajouté avec succès !");
        $this->newLine();
        $this->table(
            ['Champ', 'Valeur'],
            [
                ['Nom', $user->name],
                ['Téléphone', $user->phone],
                ['Premium', $user->is_premium ? 'Oui' : 'Non'],
                ['Expire le', $expiresAt ? $expiresAt->format('d/m/Y H:i') : 'À vie'],
                ['Source', $user->premium_source ?? 'N/A'],
            ]
        );

        return Command::SUCCESS;
    }
}
