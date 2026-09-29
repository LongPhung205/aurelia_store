<?php

namespace Tests\Unit\Services;

use App\Services\RfmAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RfmAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_get_segment_category_affinities_structure()
    {
        $mockCustomers = [
            [
                'key' => 'user_1',
                'user_id' => 1,
                'name' => 'VIP Long',
                'phone' => '0987654321',
                'order_ids' => [101, 102],
                'recency_days' => 5,
                'last_order_date' => '25/09/2026',
                'frequency' => 5,
                'monetary' => 6000000,
                'r_score' => 5,
                'f_score' => 4,
                'm_score' => 5,
                'segment' => 'Champions',
            ]
        ];

        $affinities = $this->service->getSegmentCategoryAffinities($mockCustomers);
        $this->assertArrayHasKey('Champions', $affinities);
        $this->assertEquals('Champions', $affinities['Champions']['name']);
    }
}
