<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_otp_creates_user_if_not_exists(): void
    {
        $response = $this->postJson('/api/auth/send-otp', [
            'phone'        => '22670000001',
            'country_code' => 'BF',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        // Le backend normalise le phone en supprimant le préfixe '+'
        $this->assertDatabaseHas('users', ['phone' => '22670000001']);
    }

    public function test_send_otp_fails_without_phone(): void
    {
        $this->postJson('/api/auth/send-otp', [])
            ->assertStatus(422);
    }

    public function test_verify_otp_returns_token_with_valid_code(): void
    {
        $user = User::factory()->create([
            'phone'          => '22670000001',
            'otp_code'       => '123456',
            'otp_expires_at' => now()->addMinutes(5),
            'otp_attempts'   => 0,
        ]);

        $response = $this->postJson('/api/auth/verify-otp', [
            'phone'    => '22670000001',
            'otp_code' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'token',
                'user' => ['id', 'phone'],
            ]);
    }

    public function test_verify_otp_fails_with_wrong_code(): void
    {
        User::factory()->create([
            'phone'          => '22670000002',
            'otp_code'       => '999999',
            'otp_expires_at' => now()->addMinutes(5),
            'otp_attempts'   => 0,
        ]);

        $this->postJson('/api/auth/verify-otp', [
            'phone'    => '22670000002',
            'otp_code' => '000000',
        ])->assertStatus(400);
    }

    public function test_verify_otp_fails_with_expired_code(): void
    {
        User::factory()->create([
            'phone'          => '22670000003',
            'otp_code'       => '123456',
            'otp_expires_at' => now()->subMinutes(10),
            'otp_attempts'   => 0,
        ]);

        $this->postJson('/api/auth/verify-otp', [
            'phone'    => '22670000003',
            'otp_code' => '123456',
        ])->assertStatus(400);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/auth/me')
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'user' => ['id', 'name']]);
    }

    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }
}
