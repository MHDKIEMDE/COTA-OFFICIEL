<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create 
                            {--name=MHDK : Nom de l\'utilisateur admin}
                            {--email=mkiemde00@gmail.com : Email de l\'utilisateur admin}
                            {--password= : Mot de passe (optionnel, généré si non fourni)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer un utilisateur admin pour Filament';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name');
        $email = $this->option('email');
        $password = $this->option('password') ?? $this->generatePassword();

        // Vérifier si l'utilisateur existe déjà
        $user = User::where('email', $email)->first();

        if ($user) {
            $this->warn("⚠️  L'utilisateur avec l'email {$email} existe déjà.");
            
            if (!$this->confirm('Voulez-vous mettre à jour cet utilisateur ?', false)) {
                $this->info('❌ Opération annulée.');
                return Command::FAILURE;
            }

            // Mettre à jour l'utilisateur existant
            $user->update([
                'name' => $name,
                'password' => Hash::make($password),
                'is_premium' => true,
                'is_admin' => true,
                'is_super_admin' => true,
                'premium_expires_at' => now()->addYears(10),
                'premium_source' => 'subscription', // Utiliser 'subscription' car 'admin' n'est pas dans l'enum
                'email_verified_at' => now(),
            ]);

            $this->info("✅ Utilisateur mis à jour : {$name} ({$email})");
        } else {
            // Créer un nouvel utilisateur admin
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'is_premium' => true,
                'is_admin' => true,
                'is_super_admin' => true,
                'premium_expires_at' => now()->addYears(10),
                'premium_source' => 'subscription', // Utiliser 'subscription' car 'admin' n'est pas dans l'enum
                'email_verified_at' => now(),
            ]);

            $this->info("✅ Utilisateur admin créé : {$name} ({$email})");
        }

        $this->newLine();
        $this->info('📋 Informations de connexion :');
        $this->table(
            ['Champ', 'Valeur'],
            [
                ['Email', $email],
                ['Nom', $name],
                ['Mot de passe', $password],
                ['Premium', 'Oui (expire dans 10 ans)'],
                ['Panel Admin', 'http://localhost:8000/admin'],
            ]
        );

        $this->warn('⚠️  Changez le mot de passe après la première connexion !');

        return Command::SUCCESS;
    }

    /**
     * Générer un mot de passe aléatoire
     */
    private function generatePassword(): string
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*'), 0, 12);
    }
}
