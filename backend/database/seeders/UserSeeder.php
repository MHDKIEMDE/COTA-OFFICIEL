<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Utilisateurs de test pour le développement local.
 *
 * Connexion possible par numéro + PIN ou email + mot de passe :
 *   - Gratuit : +22670000001 / PIN 1234 — free@cota.test / password123
 *   - Premium : +22670000002 / PIN 1234 — premium@cota.test / password123
 *
 * Usage : php artisan db:seed --class=UserSeeder
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'phone'      => '+22670000001',
                'email'      => 'free@cota.test',
                'name'       => 'Test Gratuit',
                'is_premium' => false,
            ],
            [
                'phone'      => '+22670000002',
                'email'      => 'premium@cota.test',
                'name'       => 'Test Premium',
                'is_premium' => true,
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrNew(['phone' => $data['phone']]);

            $user->forceFill([
                'name'                   => $data['name'],
                'email'                  => $data['email'],
                'password'               => Hash::make('password123'),
                'pin'                    => Hash::make('1234'),
                'pin_set'                => true,
                'registration_completed' => true,
                'country_code'           => 'BF',
                'is_premium'             => $data['is_premium'],
                'premium_expires_at'     => $data['is_premium'] ? now()->addYear() : null,
                'premium_source'         => $data['is_premium'] ? 'subscription' : null,
                'phone_verified_at'      => now(),
                'email_verified_at'      => now(),
                'referral_code'          => $user->referral_code ?? strtoupper(Str::random(8)),
            ])->save();

            $this->command?->info("✅ {$user->name} — {$user->phone} / PIN 1234 — {$user->email} / password123"
                . ($user->is_premium ? ' (PREMIUM 1 an)' : ''));
        }
    }
}
