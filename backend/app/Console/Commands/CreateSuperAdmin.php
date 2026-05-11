<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create 
                            {--email= : Email du super admin}
                            {--password= : Mot de passe}
                            {--name= : Nom complet}
                            {--phone= : Numéro de téléphone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer un compte super administrateur';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 Création d\'un Super Administrateur COTA');
        $this->newLine();

        // Récupérer ou demander les informations
        $email = $this->option('email') ?? $this->ask('Email du super admin');
        
        // Vérifier si l'email existe déjà
        if (User::where('email', $email)->exists()) {
            $user = User::where('email', $email)->first();
            
            if ($this->confirm("Un utilisateur avec cet email existe déjà ({$user->name}). Voulez-vous le promouvoir en Super Admin ?")) {
                $user->update([
                    'is_admin' => true,
                    'is_super_admin' => true,
                ]);
                
                $this->info("✅ {$user->name} est maintenant Super Admin !");
                $this->info("📧 Email: {$email}");
                return Command::SUCCESS;
            }
            
            return Command::FAILURE;
        }

        $name = $this->option('name') ?? $this->ask('Nom complet', 'Admin COTA');
        $phone = $this->option('phone') ?? $this->ask('Téléphone', '+221770000000');
        $password = $this->option('password') ?? $this->secret('Mot de passe (min 8 caractères)');

        if (strlen($password) < 8) {
            $this->error('Le mot de passe doit contenir au moins 8 caractères.');
            return Command::FAILURE;
        }

        // Créer l'utilisateur
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => Hash::make($password),
            'referral_code' => 'ADMIN' . strtoupper(Str::random(4)),
            'is_premium' => true,
            'is_admin' => true,
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ]);

        $this->newLine();
        $this->info('✅ Super Administrateur créé avec succès !');
        $this->newLine();
        
        $this->table(
            ['Champ', 'Valeur'],
            [
                ['ID', $user->id],
                ['Nom', $user->name],
                ['Email', $user->email],
                ['Téléphone', $user->phone],
                ['Code parrainage', $user->referral_code],
            ]
        );

        $this->newLine();
        $this->info("🌐 Connectez-vous sur: " . url('/admin/login'));
        $this->warn("🔒 Conservez ces identifiants en lieu sûr !");

        return Command::SUCCESS;
    }
}

