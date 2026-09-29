# Advanced Analytics: RFM Segmentation & Market Basket Analysis Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement RFM customer segmentation and Apriori market basket analysis organized into 3 sub-tabs on `/admin/analytics` to deliver algorithmic business intelligence for Aurelia Store.

**Architecture:** Two dedicated services (`RfmAnalyticsService` and `MarketBasketMiningService`) encapsulate segmentation math, category affinities, and association rule mining. The `AnalyticsController` handles tab routing (`?tab=sales|rfm|basket`), serving a unified multi-tab Blade view with ApexCharts and Dark/Light mode synchronization.

**Tech Stack:** Laravel 12, PHP 8.2+, MySQL (Aiven/Production), Tailwind CSS, Flowbite, ApexCharts, PHPUnit 11.

## Global Constraints

- Do not introduce third-party composer dependencies; implement RFM scoring and pairwise Apriori association mining natively in PHP.
- Ensure all product image URLs resolve cleanly via `asset('storage/' . ltrim($path, '/'))` with `onerror` image placeholders.
- All monetary values must follow Vietnamese Dong convention (`number_format($val, 0, ',', '.') . ' đ'`).
- Ensure all queries are compatible with MySQL 8.0 `ONLY_FULL_GROUP_BY` and SQLite testing environments.
- Protect all analytics endpoints with `['auth', 'role:admin']`.

---

### Task 1: RFM Scoring Engine & Customer Classification (`RfmAnalyticsService`)

**Files:**
- Create: `app/Services/RfmAnalyticsService.php`
- Test: `tests/Unit/Services/RfmAnalyticsServiceTest.php`

**Interfaces:**
- Produces: `RfmAnalyticsService::calculateCustomerRfmScores(): array` returning list of customers with raw $(R, F, M)$, quintile scores $(R_{\text{score}}, F_{\text{score}}, M_{\text{score}} \in [1, 5])$, and segment labels.
- Produces: `RfmAnalyticsService::scoreRecency(int $days): int`
- Produces: `RfmAnalyticsService::scoreFrequency(int $orders): int`
- Produces: `RfmAnalyticsService::scoreMonetary(float $amount): int`
- Produces: `RfmAnalyticsService::classifySegment(int $r, int $f, int $m): string`

- [ ] **Step 1: Write Unit tests for RFM scoring functions and segment classifications**

Create `tests/Unit/Services/RfmAnalyticsServiceTest.php`:
```php
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
```

- [ ] **Step 2: Run unit test to verify it fails**

Run: `php artisan test tests/Unit/Services/RfmAnalyticsServiceTest.php`  
Expected: FAIL (`Class "App\Services\RfmAnalyticsService" not found`).

- [ ] **Step 3: Implement RFM scoring and classification in RfmAnalyticsService**

Create `app/Services/RfmAnalyticsService.php`:
```php
<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RfmAnalyticsService
{
    public function scoreRecency(int $days): int
    {
        return match (true) {
            $days <= 15 => 5,
            $days <= 30 => 4,
            $days <= 60 => 3,
            $days <= 90 => 2,
            default => 1,
        };
    }

    public function scoreFrequency(int $orders): int
    {
        return match (true) {
            $orders >= 8 => 5,
            $orders >= 5 => 4,
            $orders >= 3 => 3,
            $orders === 2 => 2,
            default => 1,
        };
    }

    public function scoreMonetary(float $amount): int
    {
        return match (true) {
            $amount >= 5000000 => 5,
            $amount >= 2500000 => 4,
            $amount >= 1000000 => 3,
            $amount >= 500000 => 2,
            default => 1,
        };
    }

    public function classifySegment(int $r, int $f, int $m): string
    {
        if ($r >= 4 && $f >= 4 && $m >= 4) {
            return 'Champions';
        }

        if ($r <= 2 && $f >= 3 && $m >= 3) {
            return 'At Risk';
        }

        if ($r >= 3 && $f >= 3) {
            return 'Loyal Customers';
        }

        if ($r >= 4 && $f <= 2 && $m >= 2) {
            return 'Potential Loyalists';
        }

        if ($r >= 4 && $f === 1) {
            return 'New Customers';
        }

        return 'Lost Customers';
    }

    public function calculateCustomerRfmScores(): array
    {
        $orders = Order::where(function($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            })
            ->where('status', '!=', 'cancelled')
            ->select('id', 'user_id', 'customer_name', 'customer_phone', 'total_amount', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $customers = [];
        $now = Carbon::now();

        foreach ($orders as $order) {
            $key = $order->user_id ? 'user_' . $order->user_id : 'phone_' . ($order->customer_phone ?: 'guest_' . $order->id);

            if (!isset($customers[$key])) {
                $customers[$key] = [
                    'key' => $key,
                    'user_id' => $order->user_id,
                    'name' => $order->customer_name ?: 'Khách vãng lai',
                    'phone' => $order->customer_phone ?: 'N/A',
                    'order_ids' => [],
                    'last_order_date' => $order->created_at,
                    'order_count' => 0,
                    'total_spend' => 0.0,
                ];
            }

            $customers[$key]['order_ids'][] = $order->id;
            $customers[$key]['order_count']++;
            $customers[$key]['total_spend'] += (float) $order->total_amount;

            if ($order->created_at->gt($customers[$key]['last_order_date'])) {
                $customers[$key]['last_order_date'] = $order->created_at;
            }
        }

        $results = [];

        foreach ($customers as $c) {
            $daysSinceLast = (int) $now->diffInDays($c['last_order_date']);
            $rScore = $this->scoreRecency($daysSinceLast);
            $fScore = $this->scoreFrequency($c['order_count']);
            $mScore = $this->scoreMonetary($c['total_spend']);
            $segment = $this->classifySegment($rScore, $fScore, $mScore);

            $results[] = [
                'key' => $c['key'],
                'user_id' => $c['user_id'],
                'name' => $c['name'],
                'phone' => $c['phone'],
                'order_ids' => $c['order_ids'],
                'recency_days' => $daysSinceLast,
                'last_order_date' => $c['last_order_date']->format('d/m/Y'),
                'frequency' => $c['order_count'],
                'monetary' => $c['total_spend'],
                'r_score' => $rScore,
                'f_score' => $fScore,
                'm_score' => $mScore,
                'segment' => $segment,
            ];
        }

        return $results;
    }
}
```

- [ ] **Step 4: Run unit test to verify it passes**

Run: `php artisan test tests/Unit/Services/RfmAnalyticsServiceTest.php`  
Expected: PASS.

- [ ] **Step 5: Commit Task 1**

```bash
git add app/Services/RfmAnalyticsService.php tests/Unit/Services/RfmAnalyticsServiceTest.php
git commit -m "feat(analytics): implement RFM scoring rules and customer classification"
```

---

### Task 2: Segment Category Affinity & RFM Aggregator (`RfmAnalyticsService`)

**Files:**
- Modify: `app/Services/RfmAnalyticsService.php`
- Modify: `tests/Unit/Services/RfmAnalyticsServiceTest.php`

**Interfaces:**
- Produces: `RfmAnalyticsService::getSegmentCategoryAffinities(array $customerRecords): array`
- Produces: `RfmAnalyticsService::getRfmDashboardData(): array` returning summary cards, chart series, segment category table, and customer drill-down collection.

- [ ] **Step 1: Write Unit test asserting RFM summary and category affinity**

Add to `tests/Unit/Services/RfmAnalyticsServiceTest.php`:
```php
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
    }
```

- [ ] **Step 2: Implement category affinity aggregation and summary dashboard data**

In `app/Services/RfmAnalyticsService.php`, add:
```php
    public function getSegmentCategoryAffinities(array $customerRecords): array
    {
        $segmentOrderMap = [];
        $segmentMeta = [
            'Champions' => ['color' => '#6366f1', 'badge' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300', 'action' => 'Chăm sóc VIP & Quà tri ân'],
            'Loyal Customers' => ['color' => '#10b981', 'badge' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300', 'action' => 'Tích điểm thành viên & Voucher'],
            'Potential Loyalists' => ['color' => '#3b82f6', 'badge' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300', 'action' => 'Gợi ý sản phẩm kèm ưu đãi'],
            'New Customers' => ['color' => '#f59e0b', 'badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300', 'action' => 'Khảo sát sau mua & Voucher tái mua'],
            'At Risk' => ['color' => '#f43f5e', 'badge' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300', 'action' => 'Chiến dịch gửi ưu đãi kích hoạt lại'],
            'Lost Customers' => ['color' => '#64748b', 'badge' => 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300', 'action' => 'Remarketing xả hàng tồn'],
        ];

        foreach ($customerRecords as $c) {
            $seg = $c['segment'];
            if (!isset($segmentOrderMap[$seg])) {
                $segmentOrderMap[$seg] = [];
            }
            $segmentOrderMap[$seg] = array_merge($segmentOrderMap[$seg], $c['order_ids']);
        }

        $allCategories = \App\Models\Category::all()->keyBy('id');
        $segmentStats = [];

        foreach ($segmentMeta as $segmentName => $meta) {
            $orderIds = array_unique($segmentOrderMap[$segmentName] ?? []);
            
            $favCategory = 'Chưa có dữ liệu';
            $topProduct = 'Chưa có dữ liệu';
            $catRevenue = 0.0;

            if (!empty($orderIds)) {
                $items = \App\Models\OrderItem::join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
                    ->join('products', 'product_variants.product_id', '=', 'products.id')
                    ->leftJoin('category_product', 'products.id', '=', 'category_product.product_id')
                    ->leftJoin('categories', 'category_product.category_id', '=', 'categories.id')
                    ->whereIn('order_items.order_id', $orderIds)
                    ->select(
                        'categories.id as category_id',
                        'categories.name as category_name',
                        'products.name as product_name',
                        DB::raw('SUM(order_items.total) as total_rev')
                    )
                    ->groupBy('categories.id', 'categories.name', 'products.name')
                    ->orderBy('total_rev', 'desc')
                    ->get();

                if ($items->isNotEmpty()) {
                    $favCategory = $items->first()->category_name ?: 'Chưa phân loại';
                    $topProduct = $items->first()->product_name;
                    $catRevenue = (float) $items->first()->total_rev;
                }
            }

            $segmentStats[$segmentName] = [
                'name' => $segmentName,
                'color' => $meta['color'],
                'badge' => $meta['badge'],
                'action' => $meta['action'],
                'favorite_category' => $favCategory,
                'top_product' => $topProduct,
                'category_revenue' => $catRevenue,
            ];
        }

        return $segmentStats;
    }

    public function getRfmDashboardData(): array
    {
        $customers = $this->calculateCustomerRfmScores();
        $affinities = $this->getSegmentCategoryAffinities($customers);

        $totalCustomers = count($customers);
        $totalRevenue = array_sum(array_column($customers, 'monetary'));

        $cohortCounts = [
            'Champions' => 0,
            'Loyal Customers' => 0,
            'Potential Loyalists' => 0,
            'New Customers' => 0,
            'At Risk' => 0,
            'Lost Customers' => 0,
        ];

        $cohortRevenue = [
            'Champions' => 0.0,
            'Loyal Customers' => 0.0,
            'Potential Loyalists' => 0.0,
            'New Customers' => 0.0,
            'At Risk' => 0.0,
            'Lost Customers' => 0.0,
        ];

        foreach ($customers as $c) {
            $cohortCounts[$c['segment']]++;
            $cohortRevenue[$c['segment']] += (float) $c['monetary'];
        }

        $cards = [];
        foreach ($cohortCounts as $seg => $cnt) {
            $share = $totalCustomers > 0 ? round(($cnt / $totalCustomers) * 100, 1) : 0;
            $rev = $cohortRevenue[$seg];
            $cards[$seg] = [
                'name' => $seg,
                'count' => $cnt,
                'share' => $share,
                'revenue' => $rev,
                'affinity' => $affinities[$seg] ?? null,
            ];
        }

        return [
            'total_customers' => $totalCustomers,
            'total_revenue' => $totalRevenue,
            'cards' => $cards,
            'chart' => [
                'labels' => array_keys($cohortCounts),
                'values' => array_values($cohortCounts),
                'colors' => ['#6366f1', '#10b981', '#3b82f6', '#f59e0b', '#f43f5e', '#64748b'],
            ],
            'affinities' => $affinities,
            'customers' => $customers,
        ];
    }
```

- [ ] **Step 3: Run unit test to verify it passes**

Run: `php artisan test tests/Unit/Services/RfmAnalyticsServiceTest.php`  
Expected: PASS.

- [ ] **Step 4: Commit Task 2**

```bash
git add app/Services/RfmAnalyticsService.php tests/Unit/Services/RfmAnalyticsServiceTest.php
git commit -m "feat(analytics): implement category affinity and RFM summary aggregator"
```

---

### Task 3: Market Basket Analysis & Apriori Association Mining (`MarketBasketMiningService`)

**Files:**
- Create: `app/Services/MarketBasketMiningService.php`
- Test: `tests/Unit/Services/MarketBasketMiningServiceTest.php`

**Interfaces:**
- Produces: `MarketBasketMiningService::mineAssociationRules(float $minConfidence = 0.10, float $minLift = 1.05): array` returning:
  - `total_transactions`: int (Orders with $\ge 2$ distinct items)
  - `rules`: array of discovered association pairs $\{A\} \Rightarrow \{B\}$ with `support`, `confidence`, `lift`, and formatted image assets.
  - `top_rules`: top 3 rules with the highest Lift.

- [ ] **Step 1: Write Unit tests for Support, Confidence, and Lift calculations**

Create `tests/Unit/Services/MarketBasketMiningServiceTest.php`:
```php
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
        // T1: [A, B]
        // T2: [A, B, C]
        // T3: [A, C]
        // T4: [B, D]
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
        // Lift(A -> B) = 66.7% / 75% = 0.89

        $ruleAB = collect($rules)->first(fn($r) => $r['antecedent_id'] === 1 && $r['consequent_id'] === 2);
        $this->assertNotNull($ruleAB);
        $this->assertEquals(50.0, $ruleAB['support']);
        $this->assertEquals(66.7, $ruleAB['confidence']);
    }
}
```

- [ ] **Step 2: Implement MarketBasketMiningService**

Create `app/Services/MarketBasketMiningService.php`:
```php
<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class MarketBasketMiningService
{
    public function extractTransactions(): array
    {
        $rows = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->where(function($q) {
                $q->where('orders.payment_status', 'paid')
                  ->orWhere('orders.status', 'completed');
            })
            ->where('orders.status', '!=', 'cancelled')
            ->select('orders.id as order_id', 'product_variants.product_id')
            ->get();

        $transactions = [];
        foreach ($rows as $row) {
            $transactions[$row->order_id][] = (int) $row->product_id;
        }

        // Filter baskets with at least 2 distinct products
        $validTransactions = [];
        foreach ($transactions as $orderId => $items) {
            $unique = array_unique($items);
            if (count($unique) >= 2) {
                $validTransactions[] = array_values($unique);
            }
        }

        return $validTransactions;
    }

    public function calculateRulesFromTransactions(array $transactions, float $minConfidence = 15.0, float $minLift = 1.05): array
    {
        $totalTransactions = count($transactions);
        if ($totalTransactions === 0) {
            return [];
        }

        $itemCounts = [];
        $pairCounts = [];

        foreach ($transactions as $items) {
            $n = count($items);
            for ($i = 0; $i < $n; $i++) {
                $itemA = $items[$i];
                $itemCounts[$itemA] = ($itemCounts[$itemA] ?? 0) + 1;

                for ($j = 0; $j < $n; $j++) {
                    if ($i === $j) continue;
                    $itemB = $items[$j];
                    $pairKey = "{$itemA}_{$itemB}";
                    $pairCounts[$pairKey] = ($pairCounts[$pairKey] ?? 0) + 1;
                }
            }
        }

        $rules = [];

        foreach ($pairCounts as $key => $coCount) {
            [$itemA, $itemB] = array_map('intval', explode('_', $key));

            $countA = $itemCounts[$itemA];
            $countB = $itemCounts[$itemB];

            $supportAB = round(($coCount / $totalTransactions) * 100, 1);
            $supportB = $countB / $totalTransactions;
            $confidence = round(($coCount / $countA) * 100, 1);
            $lift = $supportB > 0 ? round(($coCount / $countA) / $supportB, 2) : 0.0;

            if ($confidence >= $minConfidence && $lift >= $minLift) {
                $rules[] = [
                    'antecedent_id' => $itemA,
                    'consequent_id' => $itemB,
                    'co_count' => $coCount,
                    'support' => $supportAB,
                    'confidence' => $confidence,
                    'lift' => $lift,
                ];
            }
        }

        usort($rules, fn($a, $b) => $b['lift'] <=> $a['lift']);

        return $rules;
    }

    public function mineAssociationRules(float $minConfidence = 15.0, float $minLift = 1.05): array
    {
        $transactions = $this->extractTransactions();
        $rawRules = $this->calculateRulesFromTransactions($transactions, $minConfidence, $minLift);

        $productIds = [];
        foreach ($rawRules as $r) {
            $productIds[] = $r['antecedent_id'];
            $productIds[] = $r['consequent_id'];
        }
        $productIds = array_unique($productIds);

        $products = Product::whereIn('id', $productIds)
            ->with(['variants', 'images', 'categories'])
            ->get()
            ->keyBy('id');

        $enrichedRules = [];

        foreach ($rawRules as $r) {
            $prodA = $products->get($r['antecedent_id']);
            $prodB = $products->get($r['consequent_id']);

            if (!$prodA || !$prodB) continue;

            $formatImg = function($p) {
                $img = $p->primary_image_url;
                return $img ? (str_starts_with($img, 'http') ? $img : asset('storage/' . ltrim($img, '/'))) : null;
            };

            $enrichedRules[] = [
                'antecedent' => [
                    'id' => $prodA->id,
                    'name' => $prodA->name,
                    'sku' => $prodA->variants->first()?->sku ?? 'SP-' . $prodA->id,
                    'category' => $prodA->categories->first()?->name ?? 'N/A',
                    'thumbnail' => $formatImg($prodA),
                ],
                'consequent' => [
                    'id' => $prodB->id,
                    'name' => $prodB->name,
                    'sku' => $prodB->variants->first()?->sku ?? 'SP-' . $prodB->id,
                    'category' => $prodB->categories->first()?->name ?? 'N/A',
                    'thumbnail' => $formatImg($prodB),
                ],
                'co_count' => $r['co_count'],
                'support' => $r['support'],
                'confidence' => $r['confidence'],
                'lift' => $r['lift'],
            ];
        }

        return [
            'total_transactions' => count($transactions),
            'rules' => $enrichedRules,
            'top_rules' => array_slice($enrichedRules, 0, 3),
        ];
    }
}
```

- [ ] **Step 3: Run unit test to verify it passes**

Run: `php artisan test tests/Unit/Services/MarketBasketMiningServiceTest.php`  
Expected: PASS.

- [ ] **Step 4: Commit Task 3**

```bash
git add app/Services/MarketBasketMiningService.php tests/Unit/Services/MarketBasketMiningServiceTest.php
git commit -m "feat(analytics): implement Apriori market basket mining and association rules"
```

---

### Task 4: Controller Multi-Tab Routing (`AnalyticsController`)

**Files:**
- Modify: `app/Http/Controllers/Admin/AnalyticsController.php`
- Create: `tests/Feature/Admin/AnalyticsAdvancedTest.php`

**Interfaces:**
- Produces: `AnalyticsController@index` accepting `?tab=sales|rfm|basket` and supplying `$tabData` for active sub-tab.

- [ ] **Step 1: Write Feature test asserting all 3 sub-tabs render with 200 OK**

Create `tests/Feature/Admin/AnalyticsAdvancedTest.php`:
```php
<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsAdvancedTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_rfm_tab()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/analytics?tab=rfm');
        $response->assertStatus(200);
        $response->assertViewHas('activeTab', 'rfm');
        $response->assertViewHas('rfmData');
    }

    public function test_admin_can_access_market_basket_tab()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/analytics?tab=basket');
        $response->assertStatus(200);
        $response->assertViewHas('activeTab', 'basket');
        $response->assertViewHas('basketData');
    }
}
```

- [ ] **Step 2: Update AnalyticsController to inject and coordinate services**

In `app/Http/Controllers/Admin/AnalyticsController.php`:
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\MarketBasketMiningService;
use App\Services\RfmAnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;
    protected RfmAnalyticsService $rfmService;
    protected MarketBasketMiningService $basketService;

    public function __construct(
        AnalyticsService $analyticsService,
        RfmAnalyticsService $rfmService,
        MarketBasketMiningService $basketService
    ) {
        $this->analyticsService = $analyticsService;
        $this->rfmService = $rfmService;
        $this->basketService = $basketService;
    }

    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'sales');
        $range = $request->input('range', 'last_7_days');
        $from = $request->input('from_date');
        $to = $request->input('to_date');

        $salesData = $this->analyticsService->getAnalyticsData($range, $from, $to);
        $rfmData = $activeTab === 'rfm' ? $this->rfmService->getRfmDashboardData() : null;
        $basketData = $activeTab === 'basket' ? $this->basketService->mineAssociationRules() : null;

        return view('admin.analytics.index', [
            'activeTab' => $activeTab,
            'data' => $salesData,
            'rfmData' => $rfmData,
            'basketData' => $basketData,
        ]);
    }

    public function export(Request $request)
    {
        $range = $request->input('range', 'last_7_days');
        $from = $request->input('from_date');
        $to = $request->input('to_date');

        $data = $this->analyticsService->getAnalyticsData($range, $from, $to);

        return $this->analyticsService->streamCsvReport($data);
    }
}
```

- [ ] **Step 3: Run feature tests to verify they pass**

Run: `php artisan test tests/Feature/Admin/AnalyticsAdvancedTest.php`  
Expected: PASS.

- [ ] **Step 4: Commit Task 4**

```bash
git add app/Http/Controllers/Admin/AnalyticsController.php tests/Feature/Admin/AnalyticsAdvancedTest.php
git commit -m "feat(analytics): connect RFM and Market Basket services to AnalyticsController"
```

---

### Task 5: Blade UI - 3 Sub-Tabs, RFM Dashboards & Apriori Rules Display

**Files:**
- Modify: `resources/views/admin/analytics/index.blade.php`

**Interfaces:**
- Produces: Top-level sub-tab pills (`1. Doanh thu & KPIs`, `2. Phân khúc Khách hàng RFM`, `3. Khai phá Giỏ hàng Apriori`).
- Produces: Tab 2 UI (6 Cohort Cards, Segment Donut Chart, Segment Category Affinity Matrix, Customer Drilldown Table).
- Produces: Tab 3 UI (Basket overview, Top 3 Cross-sell cards, Association Rules Table with Support/Confidence/Lift and "Tạo Combo" CTA).

- [ ] **Step 1: Construct the full 3-tab layout in `resources/views/admin/analytics/index.blade.php`**

Implement:
1. Top sub-tab navigation bar with tab switching.
2. Tab 1: Preserved Sales KPIs, charts, and tables.
3. Tab 2: RFM Cohort cards, Segment ↔ Category table, customer table.
4. Tab 3: Apriori Cross-sell discovery cards, Support/Confidence/Lift table with combo CTA button.

- [ ] **Step 2: Run all analytics tests to verify view rendering**

Run: `php artisan test tests/Feature/Admin/AnalyticsTest.php tests/Feature/Admin/AnalyticsAdvancedTest.php`  
Expected: PASS.

- [ ] **Step 3: Commit Task 5**

```bash
git add resources/views/admin/analytics/index.blade.php
git commit -m "feat(analytics): render 3-tab UI for RFM customer segmentation and Apriori cross-sell rules"
```

---

### Task 6: Final Verification & Push to Production

**Files:**
- Verification: Run complete test suite (`php artisan test`)
- Git: Push to `origin main`

- [ ] **Step 1: Run complete test suite**

Run: `php artisan test`  
Expected: PASS (All tests pass without regression).

- [ ] **Step 2: Push changes to GitHub**

```bash
git push origin main
```
Expected: Pushed to `origin main`, triggering automated Render deployment.
