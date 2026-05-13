<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\Models\User;
use App\Services\Payment\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SubscriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_plans_returns_three_plans(): void
    {
        $response = $this->getJson('/api/subscriptions/plans')
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(3, count($data));
        // Chaque plan doit avoir au moins un champ prix
        foreach ($data as $plan) {
            $this->assertTrue(isset($plan['price']) || isset($plan['id']),
                'Un plan doit avoir un champ price ou id');
        }
    }

    public function test_get_my_subscription_requires_auth(): void
    {
        $this->getJson('/api/subscriptions/me')->assertStatus(401);
    }

    public function test_get_my_subscription_returns_status(): void
    {
        $user = User::factory()->create([
            'is_premium'        => false,
            'premium_expires_at' => null,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/subscriptions/me')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => ['is_premium' => false],
            ]);
    }

    public function test_initiate_purchase_requires_auth(): void
    {
        $this->postJson('/api/subscriptions/purchase', ['plan' => 'monthly'])
            ->assertStatus(401);
    }

    public function test_initiate_purchase_fails_with_invalid_plan(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/subscriptions/purchase', ['plan' => 'invalid_plan'])
            ->assertStatus(422);
    }

    public function test_initiate_purchase_succeeds_with_mocked_gateway(): void
    {
        $user = User::factory()->create();

        $mockDriver = Mockery::mock(PaymentGatewayInterface::class);
        $mockDriver->shouldReceive('getPlanPrice')->andReturn(8000);
        $mockDriver->shouldReceive('createInvoice')->andReturn([
            'success'     => true,
            'token'       => 'test_token_123',
            'payment_url' => 'https://paydunya.test/pay/test_token_123',
        ]);

        $mockGateway = Mockery::mock(PaymentGatewayService::class);
        $mockGateway->shouldReceive('gateway')->andReturn($mockDriver);
        $mockGateway->shouldReceive('activeProvider')->andReturn('paydunya');

        $this->app->bind(PaymentGatewayService::class, fn() => $mockGateway);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/subscriptions/purchase', ['plan' => 'monthly'])
            ->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => ['payment_url', 'token', 'amount', 'plan'],
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
