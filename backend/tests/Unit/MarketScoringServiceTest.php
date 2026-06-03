<?php

namespace Tests\Unit;

use App\Services\MarketScoringService;
use PHPUnit\Framework\TestCase;

class MarketScoringServiceTest extends TestCase
{
    private MarketScoringService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MarketScoringService();
    }

    // ── scoreTier ────────────────────────────────────────────────────────────

    public function test_score_tier_gold(): void
    {
        $this->assertSame('gold', $this->service->scoreTier(65.0));
        $this->assertSame('gold', $this->service->scoreTier(90.0));
    }

    public function test_score_tier_standard(): void
    {
        $this->assertSame('standard', $this->service->scoreTier(50.0));
        $this->assertSame('standard', $this->service->scoreTier(64.9));
    }

    public function test_score_tier_bronze(): void
    {
        $this->assertSame('bronze', $this->service->scoreTier(35.0));
        $this->assertSame('bronze', $this->service->scoreTier(49.9));
    }

    public function test_score_tier_null_below_bronze(): void
    {
        $this->assertNull($this->service->scoreTier(34.9));
        $this->assertNull($this->service->scoreTier(0.0));
    }

    // ── activeSide ───────────────────────────────────────────────────────────

    public function test_active_side_1x2_home(): void
    {
        $this->assertSame('home', $this->service->activeSide('1X2', '1'));
    }

    public function test_active_side_1x2_away(): void
    {
        $this->assertSame('away', $this->service->activeSide('1X2', '2'));
    }

    public function test_active_side_1x2_draw(): void
    {
        $this->assertSame('none', $this->service->activeSide('1X2', 'X'));
    }

    public function test_active_side_double_chance_1x(): void
    {
        $this->assertSame('home', $this->service->activeSide('Double Chance', '1X'));
    }

    public function test_active_side_double_chance_x2(): void
    {
        $this->assertSame('away', $this->service->activeSide('Double Chance', 'X2'));
    }

    public function test_active_side_double_chance_12(): void
    {
        $this->assertSame('both', $this->service->activeSide('Double Chance', '12'));
    }

    public function test_active_side_btts_oui(): void
    {
        $this->assertSame('both', $this->service->activeSide('BTTS', 'Oui'));
    }

    public function test_active_side_btts_non(): void
    {
        $this->assertSame('none', $this->service->activeSide('BTTS', 'Non'));
    }

    public function test_active_side_over_under_is_none(): void
    {
        $this->assertSame('none', $this->service->activeSide('Over/Under', 'Over 2.5'));
        $this->assertSame('none', $this->service->activeSide('Over/Under', 'Under 2.5'));
    }

    public function test_active_side_corners_is_none(): void
    {
        $this->assertSame('none', $this->service->activeSide('Corners', 'Over 9.5 corners'));
    }

    public function test_active_side_team_goals_home(): void
    {
        $this->assertSame('home', $this->service->activeSide('Team Goals', 'France Over 1.5', 'France', 'Sénégal'));
    }

    public function test_active_side_team_goals_away(): void
    {
        $this->assertSame('away', $this->service->activeSide('Team Goals', 'Sénégal Over 0.5', 'France', 'Sénégal'));
    }

    // ── toUniversalCode ──────────────────────────────────────────────────────

    public function test_universal_code_1x2(): void
    {
        $this->assertSame('1', $this->service->toUniversalCode('1X2', '1'));
        $this->assertSame('X', $this->service->toUniversalCode('1X2', 'X'));
    }

    public function test_universal_code_over_under(): void
    {
        $this->assertSame('O 2.5', $this->service->toUniversalCode('Over/Under', 'Over 2.5'));
        $this->assertSame('U 2.5', $this->service->toUniversalCode('Over/Under', 'Under 2.5'));
    }

    public function test_universal_code_btts(): void
    {
        $this->assertSame('GG', $this->service->toUniversalCode('BTTS', 'Oui'));
        $this->assertSame('NG', $this->service->toUniversalCode('BTTS', 'Non'));
    }

    // ── bestMarketFor ────────────────────────────────────────────────────────

    public function test_best_market_returns_highest_market_value(): void
    {
        $candidates = [
            ['type' => '1X2',          'outcome' => '1',        'odds' => 1.80, 'engine' => 'force',  'market_value' => 0.70],
            ['type' => 'Double Chance', 'outcome' => '1X',       'odds' => 1.50, 'engine' => 'force',  'market_value' => 0.60],
            ['type' => 'Over/Under',    'outcome' => 'Over 2.5', 'odds' => 1.72, 'engine' => 'goals',  'market_value' => 0.65],
        ];

        $result = $this->service->bestMarketFor($candidates, 70.0, false, 0.0, 'PSG', 'Lyon');

        $this->assertSame('1X2', $result['type']);
        $this->assertSame('1', $result['outcome']);
        $this->assertSame('home', $result['active_side']);
        $this->assertSame('1', $result['market_selection']);
        $this->assertNotNull($result['score_tier']);
    }

    public function test_best_market_gg_returns_both_active_side(): void
    {
        $candidates = [
            ['type' => 'BTTS', 'outcome' => 'Oui', 'odds' => 1.75, 'engine' => 'goals', 'market_value' => 0.35],
        ];

        $result = $this->service->bestMarketFor($candidates, 68.0, false, 0.0, 'France', 'Sénégal');

        $this->assertSame('both', $result['active_side']);
        $this->assertSame('GG', $result['market_selection']);
    }

    public function test_best_market_tournament_penalizes_team_goals(): void
    {
        $candidates = [
            ['type' => 'Team Goals',   'outcome' => 'France Over 1.5', 'odds' => 1.75, 'engine' => 'team_goals', 'market_value' => 0.40],
            ['type' => 'Double Chance', 'outcome' => '1X',              'odds' => 1.50, 'engine' => 'force',      'market_value' => 0.25],
        ];

        // En mode tournoi avec FIFA gap fort, force doit gagner sur team_goals pénalisé
        $result = $this->service->bestMarketFor($candidates, 70.0, true, 0.50, 'France', 'Sénégal');

        $this->assertSame('Double Chance', $result['type']);
    }

    public function test_best_market_fallback_when_empty(): void
    {
        $result = $this->service->bestMarketFor([], 55.0, false, 0.0, 'A', 'B');

        $this->assertSame('Double Chance', $result['type']);
        $this->assertSame('1X', $result['outcome']);
        $this->assertSame('home', $result['active_side']);
    }

    // ── isTournament ─────────────────────────────────────────────────────────

    public function test_is_tournament_world_cup(): void
    {
        $fixture = ['league' => ['id' => 1, 'country' => 'World']];
        $this->assertTrue($this->service->isTournament($fixture));
    }

    public function test_is_tournament_premier_league_is_false(): void
    {
        $fixture = ['league' => ['id' => 39, 'country' => 'England']];
        $this->assertFalse($this->service->isTournament($fixture));
    }

    public function test_is_tournament_africa_cup(): void
    {
        $fixture = ['league' => ['id' => 6, 'country' => 'Africa']];
        $this->assertTrue($this->service->isTournament($fixture));
    }
}
