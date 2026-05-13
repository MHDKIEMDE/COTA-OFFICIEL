<?php

namespace Tests\Unit;

use App\Jobs\UpdatePredictionResultsJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UpdatePredictionResultsJobTest extends TestCase
{
    use RefreshDatabase;

    private function insertPendingPrediction(array $overrides = []): int
    {
        return DB::table('predictions')->insertGetId(array_merge([
            'match_id'         => rand(1000, 9999),
            'home_team'        => 'Home',
            'away_team'        => 'Away',
            'home_team_id'     => rand(1, 999),
            'away_team_id'     => rand(1000, 1999),
            'competition'      => 'Test League',
            'competition_id'   => 39,
            'match_date'       => now()->subHours(2),
            'match_time'       => '18:00',
            'bet_type'         => '1X2',
            'prediction'       => '1',
            'odds'             => 1.80,
            'home_score'       => 2,
            'away_score'       => 0,
            'confidence_stars' => 2,
            'total_score'      => 65,
            'status'           => 'pending',
            'is_published'     => true,
            'is_premium'       => false,
            'created_at'       => now(),
            'updated_at'       => now(),
        ], $overrides));
    }

    public function test_1x2_home_win_marked_won(): void
    {
        $id = $this->insertPendingPrediction(['prediction' => '1', 'home_score' => 2, 'away_score' => 0]);
        (new UpdatePredictionResultsJob())->handle();
        $this->assertDatabaseHas('predictions', ['id' => $id, 'status' => 'won']);
    }

    public function test_1x2_draw_prediction_won(): void
    {
        $id = $this->insertPendingPrediction(['prediction' => 'X', 'home_score' => 1, 'away_score' => 1]);
        (new UpdatePredictionResultsJob())->handle();
        $this->assertDatabaseHas('predictions', ['id' => $id, 'status' => 'won']);
    }

    public function test_1x2_wrong_prediction_lost(): void
    {
        $id = $this->insertPendingPrediction(['prediction' => '1', 'home_score' => 0, 'away_score' => 2]);
        (new UpdatePredictionResultsJob())->handle();
        $this->assertDatabaseHas('predictions', ['id' => $id, 'status' => 'lost']);
    }

    public function test_btts_yes_won_when_both_scored(): void
    {
        $id = $this->insertPendingPrediction(['bet_type' => 'BTTS', 'prediction' => 'Yes', 'home_score' => 1, 'away_score' => 1]);
        (new UpdatePredictionResultsJob())->handle();
        $this->assertDatabaseHas('predictions', ['id' => $id, 'status' => 'won']);
    }

    public function test_btts_yes_lost_when_only_one_scored(): void
    {
        $id = $this->insertPendingPrediction(['bet_type' => 'BTTS', 'prediction' => 'Yes', 'home_score' => 2, 'away_score' => 0]);
        (new UpdatePredictionResultsJob())->handle();
        $this->assertDatabaseHas('predictions', ['id' => $id, 'status' => 'lost']);
    }

    public function test_over_25_won_when_3_goals(): void
    {
        $id = $this->insertPendingPrediction(['bet_type' => 'Over/Under', 'prediction' => 'Over 2.5', 'home_score' => 2, 'away_score' => 1]);
        (new UpdatePredictionResultsJob())->handle();
        $this->assertDatabaseHas('predictions', ['id' => $id, 'status' => 'won']);
    }

    public function test_over_25_lost_when_only_2_goals(): void
    {
        $id = $this->insertPendingPrediction(['bet_type' => 'Over/Under', 'prediction' => 'Over 2.5', 'home_score' => 1, 'away_score' => 1]);
        (new UpdatePredictionResultsJob())->handle();
        $this->assertDatabaseHas('predictions', ['id' => $id, 'status' => 'lost']);
    }

    public function test_pending_future_match_not_updated(): void
    {
        $id = $this->insertPendingPrediction([
            'match_date' => now()->addHours(3),
            'home_score' => null,
            'away_score' => null,
        ]);
        (new UpdatePredictionResultsJob())->handle();
        $this->assertDatabaseHas('predictions', ['id' => $id, 'status' => 'pending']);
    }
}
