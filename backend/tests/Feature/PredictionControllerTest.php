<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PredictionControllerTest extends TestCase
{
    use RefreshDatabase;

    private function insertPrediction(array $overrides = []): void
    {
        DB::table('predictions')->insert(array_merge([
            'match_id'         => rand(1000, 9999),
            'home_team'        => 'Team A',
            'away_team'        => 'Team B',
            'home_team_id'     => 1,
            'away_team_id'     => 2,
            'competition'      => 'Premier League',
            'competition_id'   => 39,
            'country'          => 'England',
            'match_date'       => now()->addHours(2),
            'match_time'       => now()->addHours(2)->format('H:i'),
            'bet_type'         => '1X2',
            'prediction'       => '1',
            'odds'             => 1.80,
            'confidence_stars' => 3,
            'total_score'      => 75,
            'status'           => 'pending',
            'is_published'     => true,
            'is_premium'       => false,
            'created_at'       => now(),
            'updated_at'       => now(),
        ], $overrides));
    }

    public function test_today_predictions_returns_list(): void
    {
        $this->insertPrediction();

        $this->getJson('/api/predictions/today')
            ->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_today_predictions_guest_cannot_see_premium(): void
    {
        $this->insertPrediction(['is_premium' => true]);

        $response = $this->getJson('/api/predictions/today');
        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $item) {
            $this->assertFalse((bool) ($item['is_premium'] ?? false),
                'Un guest ne doit pas voir les prédictions premium déverrouillées');
        }
    }

    public function test_competitions_returns_grouped_list(): void
    {
        $this->insertPrediction();

        $this->getJson('/api/predictions/competitions')
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_search_predictions_returns_results(): void
    {
        $this->insertPrediction(['home_team' => 'Manchester City', 'away_team' => 'Arsenal']);

        $this->getJson('/api/predictions/search?q=Manchester')
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_premium_prediction_unlocked_for_premium_user(): void
    {
        $this->insertPrediction(['is_premium' => true]);

        $user = User::factory()->create([
            'is_premium'        => true,
            'premium_expires_at' => now()->addDays(30),
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/predictions/today')
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
