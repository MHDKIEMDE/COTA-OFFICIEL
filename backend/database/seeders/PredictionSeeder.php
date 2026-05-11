<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PredictionSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        $predictions = [
            [
                'home_team' => 'Manchester City',
                'away_team' => 'Arsenal',
                'competition' => 'Premier League',
                'country' => 'England',
                'bet_type' => '1X2',
                'prediction' => '1',
                'odds' => '1.85',
                'confidence_stars' => 4,
                'total_score' => 87,
                'is_premium' => false,
            ],
            [
                'home_team' => 'Real Madrid',
                'away_team' => 'Barcelona',
                'competition' => 'La Liga',
                'country' => 'Spain',
                'bet_type' => 'BTTS',
                'prediction' => 'Yes',
                'odds' => '1.70',
                'confidence_stars' => 4,
                'total_score' => 85,
                'is_premium' => false,
            ],
            [
                'home_team' => 'Bayern Munich',
                'away_team' => 'Borussia Dortmund',
                'competition' => 'Bundesliga',
                'country' => 'Germany',
                'bet_type' => 'Over/Under',
                'prediction' => 'Over 2.5',
                'odds' => '1.65',
                'confidence_stars' => 3,
                'total_score' => 78,
                'is_premium' => false,
            ],
            [
                'home_team' => 'PSG',
                'away_team' => 'Olympique de Marseille',
                'competition' => 'Ligue 1',
                'country' => 'France',
                'bet_type' => '1X2',
                'prediction' => '1',
                'odds' => '1.55',
                'confidence_stars' => 3,
                'total_score' => 76,
                'is_premium' => false,
            ],
            [
                'home_team' => 'Inter Milan',
                'away_team' => 'AC Milan',
                'competition' => 'Serie A',
                'country' => 'Italy',
                'bet_type' => 'Double Chance',
                'prediction' => '1X',
                'odds' => '1.40',
                'confidence_stars' => 3,
                'total_score' => 72,
                'is_premium' => false,
            ],
            [
                'home_team' => 'Chelsea',
                'away_team' => 'Liverpool',
                'competition' => 'Premier League',
                'country' => 'England',
                'bet_type' => 'BTTS',
                'prediction' => 'Yes',
                'odds' => '1.75',
                'confidence_stars' => 4,
                'total_score' => 88,
                'is_premium' => true,
            ],
            [
                'home_team' => 'Atletico Madrid',
                'away_team' => 'Sevilla',
                'competition' => 'La Liga',
                'country' => 'Spain',
                'bet_type' => '1X2',
                'prediction' => '1',
                'odds' => '2.10',
                'confidence_stars' => 3,
                'total_score' => 74,
                'is_premium' => true,
            ],
            [
                'home_team' => 'Juventus',
                'away_team' => 'Napoli',
                'competition' => 'Serie A',
                'country' => 'Italy',
                'bet_type' => 'Over/Under',
                'prediction' => 'Under 2.5',
                'odds' => '1.90',
                'confidence_stars' => 4,
                'total_score' => 86,
                'is_premium' => true,
            ],
        ];

        foreach ($predictions as $i => $data) {
            DB::table('predictions')->insert([
                'match_id'        => 'TEST_' . ($i + 1),
                'home_team'       => $data['home_team'],
                'away_team'       => $data['away_team'],
                'home_team_id'    => $i + 100,
                'away_team_id'    => $i + 200,
                'competition'     => $data['competition'],
                'competition_id'  => $i + 1,
                'country'         => $data['country'],
                'match_date'      => $today->copy()->setHour(15 + ($i % 6))->setMinute(0),
                'match_time'      => (15 + ($i % 6)) . ':00',
                'bet_type'        => $data['bet_type'],
                'prediction'      => $data['prediction'],
                'odds'            => $data['odds'],
                'confidence_stars'=> $data['confidence_stars'],
                'total_score'     => $data['total_score'],
                'score_form'      => rand(18, 25),
                'score_h2h'       => rand(14, 20),
                'score_home_away' => rand(10, 15),
                'score_league'    => rand(8, 12),
                'score_goals'     => rand(7, 10),
                'score_time'      => rand(5, 8),
                'is_published'    => true,
                'is_premium'      => $data['is_premium'],
                'status'          => 'pending',
                'analysis_details'=> json_encode(['source' => 'seeder_test']),
                'published_at'    => now(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        $this->command->info('✅ ' . count($predictions) . ' prédictions de test insérées pour aujourd\'hui.');
    }
}
