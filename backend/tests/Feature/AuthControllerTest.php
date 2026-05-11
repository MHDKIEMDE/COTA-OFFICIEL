<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Envoi d'un code OTP
     */
    public function test_send_otp_creates_user_if_not_exists(): void
    {
        $response = $this->postJson('/api/auth/send-otp', [
            'phone' => '+22670000001',
            'country_code' => 'BF',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'phone' => '+22670000001',
        ]);
    }

    /**
     * Test: Vérification d'un code OTP valide
     */
    public function test_verify_otp_returns_token(): void
    {
        $user = User::factory()->create([
            'phone' => '+22670000001',
            'otp_code' => bcrypt('123456'),
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        // Note: En production, le code OTP ne serait pas hashé de cette manière
        // Ce test nécessite une adaptation selon l'implémentation réelle
        $response = $this->postJson('/api/auth/verify-otp', [
            'phone' => '+22670000001',
            'otp_code' => '123456',
        ]);

        // Le test peut échouer si l'OTP n'est pas correctement vérifié
        // C'est normal car l'implémentation réelle nécessite une vérification bcrypt
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token',
                    'user',
                ],
            ]);
    }

    /**
     * Test: Rate limiting sur l'envoi d'OTP
     */
    public function test_send_otp_rate_limiting(): void
    {
        $phone = '+22670000002';

        // Premier envoi
        $response1 = $this->postJson('/api/auth/send-otp', [
            'phone' => $phone,
        ]);
        $response1->assertStatus(200);

        // Deuxième envoi immédiat (devrait être limité)
        $response2 = $this->postJson('/api/auth/send-otp', [
            'phone' => $phone,
        ]);
        $response2->assertStatus(429);
    }
}

