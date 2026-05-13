<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeder de test complet COTA
 * Couvre : utilisateurs, prédictions (hier/aujourd'hui/demain), combiné, abonnements, parrainages, affiliations
 *
 * Usage : php artisan db:seed --class=TestDataSeeder
 * Reset : php artisan migrate:fresh --seed  (attention : efface tout)
 */
class TestDataSeeder extends Seeder
{
    // ── Palettes de données réalistes ────────────────────────────────────────

    private array $matches = [
        // Premier League
        ['home' => 'Manchester City',    'away' => 'Arsenal',           'competition' => 'Premier League', 'country' => 'England',  'cid' => 101],
        ['home' => 'Chelsea',            'away' => 'Liverpool',         'competition' => 'Premier League', 'country' => 'England',  'cid' => 101],
        ['home' => 'Manchester United',  'away' => 'Tottenham',         'competition' => 'Premier League', 'country' => 'England',  'cid' => 101],
        ['home' => 'Newcastle',          'away' => 'Aston Villa',       'competition' => 'Premier League', 'country' => 'England',  'cid' => 101],
        ['home' => 'Brighton',           'away' => 'Brentford',         'competition' => 'Premier League', 'country' => 'England',  'cid' => 101],
        ['home' => 'West Ham',           'away' => 'Everton',           'competition' => 'Premier League', 'country' => 'England',  'cid' => 101],
        // La Liga
        ['home' => 'Real Madrid',        'away' => 'Barcelona',         'competition' => 'La Liga',        'country' => 'Spain',    'cid' => 102],
        ['home' => 'Atletico Madrid',    'away' => 'Sevilla',           'competition' => 'La Liga',        'country' => 'Spain',    'cid' => 102],
        ['home' => 'Valencia',           'away' => 'Villarreal',        'competition' => 'La Liga',        'country' => 'Spain',    'cid' => 102],
        ['home' => 'Real Betis',         'away' => 'Athletic Bilbao',   'competition' => 'La Liga',        'country' => 'Spain',    'cid' => 102],
        // Bundesliga
        ['home' => 'Bayern Munich',      'away' => 'Borussia Dortmund', 'competition' => 'Bundesliga',     'country' => 'Germany',  'cid' => 103],
        ['home' => 'Bayer Leverkusen',   'away' => 'RB Leipzig',        'competition' => 'Bundesliga',     'country' => 'Germany',  'cid' => 103],
        ['home' => 'Eintracht Frankfurt','away' => 'Wolfsburg',         'competition' => 'Bundesliga',     'country' => 'Germany',  'cid' => 103],
        // Serie A
        ['home' => 'Inter Milan',        'away' => 'AC Milan',          'competition' => 'Serie A',        'country' => 'Italy',    'cid' => 104],
        ['home' => 'Juventus',           'away' => 'Napoli',            'competition' => 'Serie A',        'country' => 'Italy',    'cid' => 104],
        ['home' => 'Roma',               'away' => 'Lazio',             'competition' => 'Serie A',        'country' => 'Italy',    'cid' => 104],
        ['home' => 'Atalanta',           'away' => 'Fiorentina',        'competition' => 'Serie A',        'country' => 'Italy',    'cid' => 104],
        // Ligue 1
        ['home' => 'PSG',                'away' => 'Olympique Marseille','competition' => 'Ligue 1',       'country' => 'France',   'cid' => 105],
        ['home' => 'Monaco',             'away' => 'Lyon',              'competition' => 'Ligue 1',        'country' => 'France',   'cid' => 105],
        ['home' => 'Lens',               'away' => 'Lille',             'competition' => 'Ligue 1',        'country' => 'France',   'cid' => 105],
        // Champions League
        ['home' => 'Real Madrid',        'away' => 'Bayern Munich',     'competition' => 'Champions League','country' => 'Europe',  'cid' => 106],
        ['home' => 'Manchester City',    'away' => 'Inter Milan',       'competition' => 'Champions League','country' => 'Europe',  'cid' => 106],
        ['home' => 'Barcelona',          'away' => 'PSG',               'competition' => 'Champions League','country' => 'Europe',  'cid' => 106],
        // CAN
        ['home' => 'Sénégal',            'away' => 'Maroc',             'competition' => 'CAN',            'country' => 'Afrique',  'cid' => 107],
        ['home' => 'Côte d\'Ivoire',     'away' => 'Ghana',             'competition' => 'CAN',            'country' => 'Afrique',  'cid' => 107],
        ['home' => 'Nigeria',            'away' => 'Cameroun',          'competition' => 'CAN',            'country' => 'Afrique',  'cid' => 107],
        ['home' => 'Algérie',            'away' => 'Tunisie',           'competition' => 'CAN',            'country' => 'Afrique',  'cid' => 107],
        ['home' => 'Mali',               'away' => 'Burkina Faso',      'competition' => 'CAN',            'country' => 'Afrique',  'cid' => 107],
        ['home' => 'Guinée',             'away' => 'Cap-Vert',          'competition' => 'CAN',            'country' => 'Afrique',  'cid' => 107],
        ['home' => 'Congo',              'away' => 'Égypte',            'competition' => 'CAN',            'country' => 'Afrique',  'cid' => 107],
    ];

    private array $betTypes = [
        ['type' => '1X2',          'prediction' => '1',         'odds' => 1.85],
        ['type' => '1X2',          'prediction' => '2',         'odds' => 2.30],
        ['type' => '1X2',          'prediction' => 'X',         'odds' => 3.20],
        ['type' => 'BTTS',         'prediction' => 'Yes',       'odds' => 1.70],
        ['type' => 'BTTS',         'prediction' => 'No',        'odds' => 2.00],
        ['type' => 'Over/Under',   'prediction' => 'Over 2.5',  'odds' => 1.65],
        ['type' => 'Over/Under',   'prediction' => 'Under 2.5', 'odds' => 1.90],
        ['type' => 'Double Chance','prediction' => '1X',        'odds' => 1.40],
        ['type' => 'Double Chance','prediction' => 'X2',        'odds' => 1.55],
    ];

    // ── Run ──────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->command->info('🌱 Démarrage du seeder COTA...');

        $isSqlite = DB::getDriverName() === 'sqlite';
        if ($isSqlite) {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        $this->seedUsers();
        $this->seedPredictions();
        $this->seedCombinedBet();
        $this->seedSubscriptions();
        $this->seedReferrals();
        $this->seedAffiliations();

        if ($isSqlite) {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->command->info('');
        $this->command->info('✅ Seeder terminé. Récapitulatif :');
        $this->command->table(
            ['Table', 'Lignes insérées'],
            [
                ['users',             DB::table('users')->count()],
                ['predictions',       DB::table('predictions')->count()],
                ['combined_bets',     DB::table('combined_bets')->count()],
                ['subscriptions',     DB::table('subscriptions')->count()],
                ['referrals',         DB::table('referrals')->count()],
                ['affiliations_bonus',DB::table('affiliations_bonus')->count()],
            ]
        );
    }

    // ── 1. UTILISATEURS ──────────────────────────────────────────────────────

    private function seedUsers(): void
    {
        $this->command->info('👤 Création des utilisateurs...');

        $users = [
            // Admin
            [
                'name' => 'Admin COTA', 'email' => 'admin@cota.test',
                'phone' => '+22670000001', 'is_premium' => true, 'is_admin' => true,
                'is_super_admin' => true, 'premium_expires_at' => now()->addYears(10),
                'premium_source' => 'subscription', 'country_code' => 'BF',
            ],
            // Utilisateur premium actif
            [
                'name' => 'Ibrahima Diallo', 'email' => 'ibrahima@test.com',
                'phone' => '+22670000002', 'is_premium' => true,
                'premium_expires_at' => now()->addDays(25), 'premium_source' => 'subscription',
                'country_code' => 'SN',
            ],
            // Utilisateur premium expiré
            [
                'name' => 'Fatou Koné', 'email' => 'fatou@test.com',
                'phone' => '+22507000003', 'is_premium' => false,
                'premium_expires_at' => now()->subDays(5), 'premium_source' => 'subscription',
                'country_code' => 'CI',
            ],
            // Utilisateur parrainage premium
            [
                'name' => 'Moussa Traoré', 'email' => 'moussa@test.com',
                'phone' => '+22370000004', 'is_premium' => true,
                'premium_expires_at' => now()->addDays(7), 'premium_source' => 'referral',
                'country_code' => 'ML',
            ],
            // Utilisateur affiliation premium
            [
                'name' => 'Aminata Bah', 'email' => 'aminata@test.com',
                'phone' => '+22470000005', 'is_premium' => true,
                'premium_expires_at' => now()->addDays(3), 'premium_source' => 'affiliation',
                'country_code' => 'GN',
            ],
            // Utilisateurs gratuits
            ['name' => 'Kofi Mensah',    'email' => 'kofi@test.com',    'phone' => '+23370000006', 'is_premium' => false, 'country_code' => 'GH'],
            ['name' => 'Aissatou Sow',   'email' => 'aissatou@test.com', 'phone' => '+22170000007', 'is_premium' => false, 'country_code' => 'SN'],
            ['name' => 'Jean-Paul Zida', 'email' => 'jeanpaul@test.com', 'phone' => '+22670000008', 'is_premium' => false, 'country_code' => 'BF'],
            ['name' => 'Grace Akosua',   'email' => 'grace@test.com',    'phone' => '+22870000009', 'is_premium' => false, 'country_code' => 'BJ'],
            ['name' => 'Seydou Camara',  'email' => 'seydou@test.com',   'phone' => '+22470000010', 'is_premium' => false, 'country_code' => 'GN'],
        ];

        foreach ($users as $data) {
            $existing = DB::table('users')->where('email', $data['email'])->first();
            if ($existing) continue;

            DB::table('users')->insert([
                'name'                     => $data['name'],
                'email'                    => $data['email'],
                'phone'                    => $data['phone'],
                'password'                 => Hash::make('password123'),
                'email_verified_at'        => now(),
                'phone_verified_at'        => now(),
                'is_premium'               => $data['is_premium'] ?? false,
                'is_admin'                 => $data['is_admin'] ?? false,
                'is_super_admin'           => $data['is_super_admin'] ?? false,
                'premium_expires_at'       => $data['premium_expires_at'] ?? null,
                'premium_source'           => $data['premium_source'] ?? null,
                'referral_code'            => strtoupper(Str::random(8)),
                'country_code'             => $data['country_code'] ?? 'BF',
                'welcome_combined_used'    => false,
                'notification_settings'    => json_encode(['predictions' => true, 'live' => true, 'promo' => false]),
                'preferences'              => json_encode(['competitions' => ['Premier League', 'CAN'], 'language' => 'fr']),
                'last_login_at'            => now()->subHours(rand(1, 48)),
                'created_at'               => now()->subDays(rand(1, 90)),
                'updated_at'               => now(),
            ]);
        }

        $this->command->info('   ✓ ' . count($users) . ' utilisateurs créés (doublons ignorés)');
    }

    // ── 2. PRÉDICTIONS (hier / aujourd'hui / demain) ─────────────────────────

    private function seedPredictions(): void
    {
        $this->command->info('⚽ Création des prédictions...');

        $days = [
            ['date' => Carbon::yesterday(), 'status_pool' => ['won', 'lost', 'won', 'won', 'lost', 'won']],
            ['date' => Carbon::today(),     'status_pool' => ['pending']],
            ['date' => Carbon::tomorrow(),  'status_pool' => ['pending']],
        ];

        $predictionId = 9000; // Partir d'un ID haut pour éviter les conflits

        foreach ($days as $day) {
            $date    = $day['date'];
            $statuses = $day['status_pool'];
            $isToday  = $date->isToday();
            $isPast   = $date->isPast() && !$isToday;

            foreach ($this->matches as $i => $match) {
                $predictionId++;
                $bet    = $this->betTypes[$i % count($this->betTypes)];
                $stars  = $this->confidenceStars($bet['odds']);
                $status = $statuses[array_rand($statuses)];
                $hour   = 13 + ($i % 9);      // Heures entre 13h et 21h
                $isPremium = $stars >= 3 && $bet['type'] !== '1X2';
                $isCombined = $i < 5 && $isToday; // 5 premiers = dans le combiné du jour

                // Scores pour hier : home/away selon résultat
                [$homeScore, $awayScore] = $this->generateScore($match, $bet, $status);

                DB::table('predictions')->insertOrIgnore([
                    'match_id'          => $predictionId,
                    'home_team'         => $match['home'],
                    'away_team'         => $match['away'],
                    'home_team_logo'    => null,
                    'away_team_logo'    => null,
                    'home_team_id'      => 1000 + $i,
                    'away_team_id'      => 2000 + $i,
                    'competition'       => $match['competition'],
                    'competition_id'    => $match['cid'],
                    'competition_logo'  => null,
                    'country'           => $match['country'],
                    'match_date'        => $date->copy()->setHour($hour)->setMinute(0)->setSecond(0),
                    'match_time'        => str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00',
                    'bet_type'          => $bet['type'],
                    'prediction'        => $bet['prediction'],
                    'odds'              => $bet['odds'],
                    'confidence_stars'  => $stars,
                    'score_form'        => rand(18, 28),
                    'score_h2h'         => rand(12, 23),
                    'score_home_away'   => rand(8, 18),
                    'score_league'      => rand(6, 13),
                    'score_goals'       => rand(5, 10),
                    'score_time'        => rand(3, 8),
                    'score_weather'     => rand(2, 6),
                    'score_shots'       => rand(2, 6),
                    'score_physical'    => rand(2, 6),
                    'total_score'       => rand(65, 95),
                    'home_score'        => $isPast ? $homeScore : null,
                    'away_score'        => $isPast ? $awayScore : null,
                    'status'            => $isPast ? $status : 'pending',
                    'is_published'      => true,
                    'is_premium'        => $isPremium,
                    'is_combined_daily' => $isCombined,
                    'combined_date'     => $isCombined ? $date->toDateString() : null,
                    'combined_position' => $isCombined ? ($i + 1) : null,
                    'analysis_details'  => json_encode(['source' => 'seeder', 'v' => '3.0']),
                    'published_at'      => $date->copy()->subHours(2),
                    'created_at'        => $date->copy()->subHours(3),
                    'updated_at'        => now(),
                ]);
            }
        }

        $total = count($this->matches) * count($days);
        $this->command->info("   ✓ {$total} prédictions créées (hier/aujourd'hui/demain)");
    }

    // ── 3. COMBINÉ QUOTIDIEN ─────────────────────────────────────────────────

    private function seedCombinedBet(): void
    {
        $this->command->info('🎯 Création du combiné quotidien...');

        // Récupérer les 5 premières prédictions d'aujourd'hui (marquées is_combined_daily)
        $ids = DB::table('predictions')
            ->where('is_combined_daily', true)
            ->whereDate('match_date', Carbon::today())
            ->orderBy('combined_position')
            ->limit(5)
            ->pluck('id')
            ->toArray();

        if (empty($ids)) {
            $ids = DB::table('predictions')->whereDate('match_date', Carbon::today())->limit(5)->pluck('id')->toArray();
        }

        $odds = DB::table('predictions')->whereIn('id', $ids)->sum('odds');

        DB::table('combined_bets')->insertOrIgnore([
            'type'              => 'daily',
            'date'              => Carbon::today()->toDateString(),
            'prediction_ids'    => json_encode($ids),
            'predictions_count' => count($ids),
            'total_odds'        => round($odds, 2),
            'potential_payout'  => round($odds * 1000, 0),
            'status'            => 'pending',
            'won_count'         => 0,
            'lost_count'        => 0,
            'is_published'      => true,
            'published_at'      => now()->subHour(),
            'expires_at'        => Carbon::today()->endOfDay(),
            'details'           => json_encode(['source' => 'seeder']),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Combiné d'hier (résolu)
        $yesterdayIds = DB::table('predictions')
            ->whereDate('match_date', Carbon::yesterday())
            ->limit(5)->pluck('id')->toArray();

        if (!empty($yesterdayIds)) {
            $yOdds = DB::table('predictions')->whereIn('id', $yesterdayIds)->sum('odds');
            DB::table('combined_bets')->insertOrIgnore([
                'type'              => 'daily',
                'date'              => Carbon::yesterday()->toDateString(),
                'prediction_ids'    => json_encode($yesterdayIds),
                'predictions_count' => count($yesterdayIds),
                'total_odds'        => round($yOdds, 2),
                'potential_payout'  => round($yOdds * 1000, 0),
                'status'            => 'won',
                'won_count'         => count($yesterdayIds),
                'lost_count'        => 0,
                'is_published'      => true,
                'published_at'      => Carbon::yesterday()->subHour(),
                'expires_at'        => Carbon::yesterday()->endOfDay(),
                'details'           => json_encode(['source' => 'seeder']),
                'created_at'        => Carbon::yesterday()->subHours(3),
                'updated_at'        => now(),
            ]);
        }

        $this->command->info('   ✓ Combinés du jour et d\'hier créés');
    }

    // ── 4. ABONNEMENTS ───────────────────────────────────────────────────────

    private function seedSubscriptions(): void
    {
        $this->command->info('💳 Création des abonnements...');

        $premiumUsers = DB::table('users')
            ->where('is_premium', true)->where('is_admin', false)
            ->pluck('id')->toArray();

        $plans = [
            ['plan' => 'weekly',    'amount' => 500,  'days' => 7],
            ['plan' => 'monthly',   'amount' => 1500, 'days' => 30],
            ['plan' => 'quarterly', 'amount' => 3500, 'days' => 90],
        ];

        foreach ($premiumUsers as $i => $userId) {
            $plan = $plans[$i % count($plans)];
            DB::table('subscriptions')->insert([
                'user_id'         => $userId,
                'plan'            => $plan['plan'],
                'amount'          => $plan['amount'],
                'currency'        => 'XOF',
                'starts_at'       => now()->subDays(rand(1, 5)),
                'expires_at'      => now()->addDays($plan['days']),
                'status'          => 'active',
                'payment_method'  => 'paydunya',
                'payment_id'      => 'PAY_TEST_' . strtoupper(Str::random(10)),
                'payment_status'  => 'completed',
                'payment_details' => json_encode(['method' => 'wave', 'ref' => Str::random(12)]),
                'created_at'      => now()->subDays(rand(1, 5)),
                'updated_at'      => now(),
            ]);
        }

        // 2 abonnements expirés (pour tester l'état expired)
        $freeUsers = DB::table('users')->where('is_premium', false)->limit(2)->pluck('id');
        foreach ($freeUsers as $userId) {
            DB::table('subscriptions')->insert([
                'user_id'         => $userId,
                'plan'            => 'weekly',
                'amount'          => 500,
                'currency'        => 'XOF',
                'starts_at'       => now()->subDays(14),
                'expires_at'      => now()->subDays(7),
                'status'          => 'expired',
                'payment_method'  => 'paydunya',
                'payment_id'      => 'PAY_EXP_' . strtoupper(Str::random(8)),
                'payment_status'  => 'completed',
                'payment_details' => json_encode(['method' => 'orange_money']),
                'created_at'      => now()->subDays(14),
                'updated_at'      => now()->subDays(7),
            ]);
        }

        $this->command->info('   ✓ Abonnements créés');
    }

    // ── 5. PARRAINAGES ───────────────────────────────────────────────────────

    private function seedReferrals(): void
    {
        $this->command->info('🔗 Création des parrainages...');

        $allUsers = DB::table('users')->where('is_admin', false)->pluck('id')->toArray();
        if (count($allUsers) < 3) return;

        $referrerId = $allUsers[0]; // Ibrahima = parrain principal

        $referralData = [
            ['referred' => $allUsers[1], 'status' => 'rewarded', 'days' => 3,  'tier' => 'first'],
            ['referred' => $allUsers[2], 'status' => 'rewarded', 'days' => 0,  'tier' => 'tier_3'],
            ['referred' => $allUsers[3], 'status' => 'validated','days' => 0,  'tier' => null],
            ['referred' => $allUsers[4], 'status' => 'pending',  'days' => 0,  'tier' => null],
        ];

        foreach ($referralData as $r) {
            if ($r['referred'] === $referrerId) continue;
            DB::table('referrals')->insert([
                'referrer_id'       => $referrerId,
                'referred_id'       => $r['referred'],
                'status'            => $r['status'],
                'validated_at'      => in_array($r['status'], ['validated', 'rewarded']) ? now()->subDays(rand(1, 10)) : null,
                'reward_days'       => $r['days'],
                'reward_tier'       => $r['tier'],
                'reward_applied'    => $r['status'] === 'rewarded',
                'reward_applied_at' => $r['status'] === 'rewarded' ? now()->subDays(rand(1, 5)) : null,
                'referral_source'   => collect(['whatsapp', 'facebook', 'sms', 'direct'])->random(),
                'created_at'        => now()->subDays(rand(2, 30)),
                'updated_at'        => now(),
            ]);
        }

        $this->command->info('   ✓ Parrainages créés');
    }

    // ── 6. AFFILIATIONS BOOKMAKERS ───────────────────────────────────────────

    private function seedAffiliations(): void
    {
        $this->command->info('🎰 Création des affiliations bookmakers...');

        $users = DB::table('users')->where('is_admin', false)->pluck('id');

        $bookmakers = [
            ['bookmaker' => '1xbet',     'link' => 'https://1xbet.com/ref/COTA001'],
            ['bookmaker' => 'betwinner', 'link' => 'https://betwinner.com/ref/COTA002'],
            ['bookmaker' => 'melbet',    'link' => 'https://melbet.com/ref/COTA003'],
        ];

        foreach ($users->take(6) as $i => $userId) {
            $bm = $bookmakers[$i % count($bookmakers)];
            $confirmed = $i < 3; // 3 premiers ont confirmé leur inscription

            DB::table('affiliations_bonus')->insert([
                'user_id'                    => $userId,
                'bookmaker'                  => $bm['bookmaker'],
                'bookmaker_custom_name'      => null,
                'affiliate_link'             => $bm['link'] . '_' . $userId,
                'clicks_count'               => rand(1, 5),
                'clicked_at'                 => now()->subDays(rand(1, 20)),
                'user_ip'                    => '197.213.' . rand(1, 255) . '.' . rand(1, 255),
                'user_agent'                 => 'Mozilla/5.0 Flutter App',
                'registration_confirmed'     => $confirmed,
                'registration_confirmed_at'  => $confirmed ? now()->subDays(rand(1, 15)) : null,
                'bonus_activated'            => $confirmed && $i < 2,
                'bonus_activated_at'         => ($confirmed && $i < 2) ? now()->subDays(rand(1, 10)) : null,
                'bonus_expires_at'           => ($confirmed && $i < 2) ? now()->addDays(rand(1, 5)) : null,
                'tracking_details'           => json_encode(['platform' => 'mobile', 'version' => '1.0']),
                'created_at'                 => now()->subDays(rand(5, 30)),
                'updated_at'                 => now(),
            ]);
        }

        $this->command->info('   ✓ Affiliations créées');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function confidenceStars(float $odds): int
    {
        if ($odds <= 1.50) return 4;
        if ($odds <= 1.80) return 3;
        if ($odds <= 2.20) return 2;
        return 1;
    }

    private function generateScore(array $match, array $bet, string $status): array
    {
        if ($status === 'pending') return [null, null];

        // Générer un score cohérent selon le type de pari et le résultat
        if ($bet['type'] === '1X2') {
            if ($bet['prediction'] === '1') {
                return $status === 'won' ? [2, 0] : [0, 1];
            } elseif ($bet['prediction'] === '2') {
                return $status === 'won' ? [0, 2] : [1, 0];
            } else {
                return $status === 'won' ? [1, 1] : [2, 0];
            }
        }
        if ($bet['type'] === 'BTTS') {
            return $bet['prediction'] === 'Yes'
                ? ($status === 'won' ? [1, 1] : [1, 0])
                : ($status === 'won' ? [1, 0] : [1, 1]);
        }
        if ($bet['type'] === 'Over/Under') {
            return str_contains($bet['prediction'], 'Over')
                ? ($status === 'won' ? [2, 1] : [1, 0])
                : ($status === 'won' ? [1, 0] : [2, 1]);
        }
        return [1, 1];
    }
}
