<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Créer un utilisateur admin
     * 
     * Usage: php artisan db:seed --class=AdminUserSeeder
     */
    public function run(): void
    {
        $email = 'mkiemde00@gmail.com';
        $name = 'MHDK';

        // Vérifier si l'utilisateur existe déjà
        $user = User::where('email', $email)->first();

        if ($user) {
            $this->command->info("⚠️  L'utilisateur avec l'email {$email} existe déjà.");
            $this->command->info("   Mise à jour de l'utilisateur...");
            
            // Mettre à jour l'utilisateur existant
            $user->update([
                'name' => $name,
                'is_premium' => true,
                'is_admin' => true,
                'is_super_admin' => true,
                'premium_expires_at' => now()->addYears(10), // Premium pour 10 ans
                'premium_source' => 'subscription', // Utiliser 'subscription' car 'admin' n'est pas dans l'enum
                'email_verified_at' => now(),
            ]);
            
            $this->command->info("✅ Utilisateur mis à jour : {$name} ({$email})");
        } else {
            // Créer un nouvel utilisateur admin
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password123'), // Mot de passe par défaut (à changer)
                'is_premium' => true,
                'is_admin' => true,
                'is_super_admin' => true,
                'premium_expires_at' => now()->addYears(10), // Premium pour 10 ans
                'premium_source' => 'subscription', // Utiliser 'subscription' car 'admin' n'est pas dans l'enum
                'email_verified_at' => now(),
            ]);

            $this->command->info("✅ Utilisateur admin créé : {$name} ({$email})");
            $this->command->warn("⚠️  Mot de passe par défaut : password123");
            $this->command->warn("⚠️  Changez le mot de passe après la première connexion !");
        }

        $this->command->info("");
        $this->command->info("📧 Email : {$email}");
        $this->command->info("👤 Nom : {$name}");
        $this->command->info("🔑 Mot de passe : password123 (à changer)");
        $this->command->info("⭐ Premium : Oui (expire dans 10 ans)");
    }
}
