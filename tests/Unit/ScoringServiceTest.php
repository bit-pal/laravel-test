<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\ScoringService;
use Tests\TestCase;

class ScoringServiceTest extends TestCase
{
    private $scoringService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scoringService = new ScoringService();
    }

    public function test_lp_gp_scoring()
    {
        $user1 = new User([
            'fund_type' => 'Venture Capital',
            'fund_manager' => 'Manager A',
            'geo_preferences' => ['US', 'EU'],
            'sector_preferences' => ['Tech', 'Healthcare'],
        ]);

        $user2 = new User([
            'fund_type' => 'Private Equity',
            'fund_manager' => 'Manager B',
            'geo_preferences' => ['US', 'Asia'],
            'sector_preferences' => ['Tech', 'Finance'],
        ]);

        $score = $this->scoringService->calculateScore($user1, $user2);
        $this->assertGreaterThan(0, $score);
    }

    public function test_investor_company_scoring()
    {
        $investor = new User([
            'company_stage_preferences' => ['Seed', 'Series A'],
            'geo_preferences' => ['US', 'EU'],
            'sector_preferences' => ['Tech', 'Healthcare'],
        ]);

        $company = new User([
            'company_stage_preferences' => ['Series A', 'Series B'],
            'geo_preferences' => ['US', 'Asia'],
            'sector_preferences' => ['Tech', 'Finance'],
        ]);

        $score = $this->scoringService->calculateScore($investor, $company);
        $this->assertGreaterThan(0, $score);
    }
} 