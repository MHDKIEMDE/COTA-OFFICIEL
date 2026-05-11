<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PredictionControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Récupération des pronostics du jour
     */
    public function test_today_predictions_returns_list(): void
    {
        // Créer des prédictions de test
        DB::table('predictions')->insert([
            'match_id' => 1001,
            'home_team' => 'Team A',
            'away_team' => 'Team B',
            'competition' => 'Test League',
            'match_date' => now()->addHours(2),
            'is_published' => true,
            'is_premium' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/predictions/today');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'match',
                    ],
                ],
            ]);
    }

    /**
     * Test: Récupération des compétitions disponibles
     */
    public function test_competitions_returns_grouped_list(): void
    {
        DB::table('predictions')->insert([
            'match_id' => 1001,
            'home_team' => 'Team A',
            'away_team' => 'Team B',
            'competition' => 'Premier League',
            'competition_id' => 39,
            'country' => 'England',
            'match_date' => now()->addHours(2),
            'is_published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/predictions/competitions');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'country',
                        'competitions',
                    ],
                ],
            ]);
    }

    /**
     * Test: Recherche de matchs
     */
    public function test_search_predictions_returns_results(): void
    {
        DB::table('predictions')->insert([
            'match_id' => 1001,
            'home_team' => 'Manchester City',
            'away_team' => 'Arsenal',
            'competition' => 'Premier League',
            'match_date' => now()->addHours(2),
            'is_published' => true,
            'is_premium' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/predictions/search?q=Manchester');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }
}

