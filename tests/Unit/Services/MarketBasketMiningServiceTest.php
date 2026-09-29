<?php

namespace Tests\Unit\Services;

use App\Services\MarketBasketMiningService;
use PHPUnit\Framework\TestCase;

class MarketBasketMiningServiceTest extends TestCase
{
    private MarketBasketMiningService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MarketBasketMiningService();
    }

    public function test_apriori_metrics_calculation()
    {
        // 4 transactions:
        // T1: [1, 2]
        // T2: [1, 2, 3]
        // T3: [1, 3]
        // T4: [2, 4]
        $transactions = [
            [1, 2],
            [1, 2, 3],
            [1, 3],
            [2, 4],
        ];

        $rules = $this->service->calculateRulesFromTransactions($transactions, 0.0, 0.0);

        // A=1, B=2: both appear in T1 and T2 -> Support(A and B) = 2/4 = 50%
        // Support(A) = 3/4 = 75%
        // Support(B) = 3/4 = 75%
        // Confidence(A -> B) = 2/3 = 66.7%
        // Lift(A -> B) = (2/3) / (3/4) = 0.89

        $ruleAB = collect($rules)->first(fn($r) => $r['antecedent_id'] === 1 && $r['consequent_id'] === 2);
        $this->assertNotNull($ruleAB);
        $this->assertEquals(50.0, $ruleAB['support']);
        $this->assertEquals(66.7, $ruleAB['confidence']);
        $this->assertEquals(0.89, $ruleAB['lift']);
    }
}
