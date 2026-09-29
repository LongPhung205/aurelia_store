<?php

namespace Tests\Unit\Services;

use App\Services\RfmAnalyticsService;
use PHPUnit\Framework\TestCase;

class RfmAnalyticsServiceTest extends TestCase
{
    private RfmAnalyticsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RfmAnalyticsService();
    }

    public function test_score_recency_boundaries()
    {
        $this->assertEquals(5, $this->service->scoreRecency(10));
        $this->assertEquals(4, $this->service->scoreRecency(25));
        $this->assertEquals(3, $this->service->scoreRecency(45));
        $this->assertEquals(2, $this->service->scoreRecency(75));
        $this->assertEquals(1, $this->service->scoreRecency(120));
    }

    public function test_score_frequency_boundaries()
    {
        $this->assertEquals(5, $this->service->scoreFrequency(10));
        $this->assertEquals(4, $this->service->scoreFrequency(6));
        $this->assertEquals(3, $this->service->scoreFrequency(3));
        $this->assertEquals(2, $this->service->scoreFrequency(2));
        $this->assertEquals(1, $this->service->scoreFrequency(1));
    }

    public function test_score_monetary_boundaries()
    {
        $this->assertEquals(5, $this->service->scoreMonetary(6000000));
        $this->assertEquals(4, $this->service->scoreMonetary(3000000));
        $this->assertEquals(3, $this->service->scoreMonetary(1500000));
        $this->assertEquals(2, $this->service->scoreMonetary(700000));
        $this->assertEquals(1, $this->service->scoreMonetary(300000));
    }

    public function test_classify_segments()
    {
        $this->assertEquals('Champions', $this->service->classifySegment(5, 5, 5));
        $this->assertEquals('Champions', $this->service->classifySegment(4, 4, 4));
        $this->assertEquals('Loyal Customers', $this->service->classifySegment(3, 3, 2));
        $this->assertEquals('Potential Loyalists', $this->service->classifySegment(5, 1, 3));
        $this->assertEquals('New Customers', $this->service->classifySegment(4, 1, 1));
        $this->assertEquals('At Risk', $this->service->classifySegment(2, 4, 4));
        $this->assertEquals('Lost Customers', $this->service->classifySegment(1, 1, 1));
    }
}
