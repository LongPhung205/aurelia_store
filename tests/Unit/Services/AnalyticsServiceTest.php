<?php

namespace Tests\Unit\Services;

use App\Services\AnalyticsService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class AnalyticsServiceTest extends TestCase
{
    private AnalyticsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AnalyticsService();
    }

    public function test_calculate_growth_positive()
    {
        $growth = $this->service->calculateGrowth(120, 100);
        $this->assertEquals(20.0, $growth);
    }

    public function test_calculate_growth_negative()
    {
        $growth = $this->service->calculateGrowth(75, 100);
        $this->assertEquals(-25.0, $growth);
    }

    public function test_calculate_growth_from_zero()
    {
        $growth = $this->service->calculateGrowth(100, 0);
        $this->assertEquals(100.0, $growth);

        $growthZero = $this->service->calculateGrowth(0, 0);
        $this->assertEquals(0.0, $growthZero);
    }

    public function test_resolve_periods_last_7_days()
    {
        Carbon::setTestNow('2026-09-29 12:00:00');
        $periods = $this->service->resolvePeriods('last_7_days', null, null);

        $this->assertEquals('2026-09-23', $periods['current']['start']->toDateString());
        $this->assertEquals('2026-09-29', $periods['current']['end']->toDateString());
        $this->assertEquals(7, $periods['days']);

        // Equal duration previous period
        $this->assertEquals('2026-09-16', $periods['previous']['start']->toDateString());
        $this->assertEquals('2026-09-22', $periods['previous']['end']->toDateString());
    }

    public function test_resolve_periods_custom_range()
    {
        $periods = $this->service->resolvePeriods('custom', '2026-09-10', '2026-09-14');

        $this->assertEquals('2026-09-10', $periods['current']['start']->toDateString());
        $this->assertEquals('2026-09-14', $periods['current']['end']->toDateString());
        $this->assertEquals(5, $periods['days']);

        // Previous 5 days: 2026-09-05 to 2026-09-09
        $this->assertEquals('2026-09-05', $periods['previous']['start']->toDateString());
        $this->assertEquals('2026-09-09', $periods['previous']['end']->toDateString());
    }
}
