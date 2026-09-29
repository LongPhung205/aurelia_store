<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Resolves the current date period and an equal-duration previous baseline period.
     */
    public function resolvePeriods(string $range, ?string $fromDate = null, ?string $toDate = null): array
    {
        $now = Carbon::now();

        if ($range === 'custom' && $fromDate && $toDate) {
            $currentStart = Carbon::parse($fromDate)->startOfDay();
            $currentEnd = Carbon::parse($toDate)->endOfDay();
        } else {
            match ($range) {
                'today' => [
                    $currentStart = $now->copy()->startOfDay(),
                    $currentEnd = $now->copy()->endOfDay(),
                ],
                'last_30_days' => [
                    $currentStart = $now->copy()->subDays(29)->startOfDay(),
                    $currentEnd = $now->copy()->endOfDay(),
                ],
                'this_month' => [
                    $currentStart = $now->copy()->startOfMonth(),
                    $currentEnd = $now->copy()->endOfDay(),
                ],
                'this_year' => [
                    $currentStart = $now->copy()->startOfYear(),
                    $currentEnd = $now->copy()->endOfDay(),
                ],
                default => [ // last_7_days
                    $range = 'last_7_days',
                    $currentStart = $now->copy()->subDays(6)->startOfDay(),
                    $currentEnd = $now->copy()->endOfDay(),
                ],
            };
        }

        $durationDays = (int) $currentStart->copy()->startOfDay()->diffInDays($currentEnd->copy()->startOfDay()) + 1;
        $prevEnd = $currentStart->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($durationDays - 1)->startOfDay();

        return [
            'range' => $range,
            'days' => $durationDays,
            'current' => [
                'start' => $currentStart,
                'end' => $currentEnd,
            ],
            'previous' => [
                'start' => $prevStart,
                'end' => $prevEnd,
            ],
        ];
    }

    /**
     * Calculates period-over-period percentage growth.
     */
    public function calculateGrowth(float $current, float $previous): float
    {
        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Main pipeline aggregating all analytical data for the requested period.
     */
    public function getAnalyticsData(string $range, ?string $fromDate = null, ?string $toDate = null): array
    {
        $periods = $this->resolvePeriods($range, $fromDate, $toDate);

        $kpis = $this->calculateKpis($periods);
        $trendChart = $this->getDailyTrends($periods['current']);
        $categoryData = $this->getCategoryDistribution($periods['current']);
        $customerData = $this->getCustomerCohortStats($periods);
        $productRankings = $this->getTopAndSlowProducts($periods['current']);

        $kpis['customers'] = [
            'new_current' => $customerData['new_users_current'],
            'new_previous' => $customerData['new_users_prev'],
            'new_growth' => $customerData['new_users_growth'],
            'returning_rate' => $customerData['returning_rate'],
            'first_time_rate' => $customerData['first_time_rate'],
        ];
        $kpis['orders']['cancel_rate'] = $kpis['orders']['cancellation_rate'];

        return [
            'periods' => $periods,
            'kpis' => $kpis,
            'trendChart' => $trendChart,
            'categoryChart' => $categoryData['chart'],
            'categoryStats' => $categoryData['stats'],
            'customerChart' => $customerData,
            'topProducts' => $productRankings['top'],
            'slowProducts' => $productRankings['slow'],
        ];
    }

    /**
     * Calculates summary KPIs and comparisons.
     */
    protected function calculateKpis(array $periods): array
    {
        // 1. Current Period Metrics
        $currOrdersQuery = Order::whereBetween('created_at', [$periods['current']['start'], $periods['current']['end']]);
        
        $currPaidOrdersQuery = (clone $currOrdersQuery)
            ->where(function($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            })
            ->where('status', '!=', 'cancelled');

        $currRevenue = (float) $currPaidOrdersQuery->sum('total_amount');
        $currCompletedOrders = (int) $currPaidOrdersQuery->count();
        $currTotalOrders = (int) $currOrdersQuery->count();
        $currCancelledOrders = (int) (clone $currOrdersQuery)->where('status', 'cancelled')->count();
        $currCancellationRate = $currTotalOrders > 0 ? round(($currCancelledOrders / $currTotalOrders) * 100, 1) : 0.0;
        $currAov = $currCompletedOrders > 0 ? round($currRevenue / $currCompletedOrders, 0) : 0.0;

        // 2. Previous Period Metrics
        $prevOrdersQuery = Order::whereBetween('created_at', [$periods['previous']['start'], $periods['previous']['end']]);
        
        $prevPaidOrdersQuery = (clone $prevOrdersQuery)
            ->where(function($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            })
            ->where('status', '!=', 'cancelled');

        $prevRevenue = (float) $prevPaidOrdersQuery->sum('total_amount');
        $prevCompletedOrders = (int) $prevPaidOrdersQuery->count();
        $prevTotalOrders = (int) $prevOrdersQuery->count();
        $prevCancelledOrders = (int) (clone $prevOrdersQuery)->where('status', 'cancelled')->count();
        $prevCancellationRate = $prevTotalOrders > 0 ? round(($prevCancelledOrders / $prevTotalOrders) * 100, 1) : 0.0;
        $prevAov = $prevCompletedOrders > 0 ? round($prevRevenue / $prevCompletedOrders, 0) : 0.0;

        return [
            'revenue' => [
                'current' => $currRevenue,
                'previous' => $prevRevenue,
                'growth' => $this->calculateGrowth($currRevenue, $prevRevenue),
            ],
            'orders' => [
                'current' => $currCompletedOrders,
                'previous' => $prevCompletedOrders,
                'growth' => $this->calculateGrowth($currCompletedOrders, $prevCompletedOrders),
                'total' => $currTotalOrders,
                'cancelled' => $currCancelledOrders,
                'cancellation_rate' => $currCancellationRate,
            ],
            'aov' => [
                'current' => $currAov,
                'previous' => $prevAov,
                'growth' => $this->calculateGrowth($currAov, $prevAov),
            ],
        ];
    }

    /**
     * Calculates daily revenue and order trends for ApexCharts.
     */
    protected function getDailyTrends(array $currentPeriod): array
    {
        $dailyData = Order::select(
                DB::raw('DATE(created_at) as order_date'),
                DB::raw("SUM(CASE WHEN (payment_status = 'paid' OR status = 'completed') AND status != 'cancelled' THEN total_amount ELSE 0 END) as revenue"),
                DB::raw("COUNT(CASE WHEN (payment_status = 'paid' OR status = 'completed') AND status != 'cancelled' THEN id ELSE NULL END) as order_count")
            )
            ->whereBetween('created_at', [$currentPeriod['start'], $currentPeriod['end']])
            ->groupBy('order_date')
            ->orderBy('order_date', 'asc')
            ->get()
            ->keyBy('order_date');

        $period = CarbonPeriod::create($currentPeriod['start']->copy()->startOfDay(), $currentPeriod['end']->copy()->startOfDay());

        $labels = [];
        $revenueSeries = [];
        $orderSeries = [];

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $label = $date->format('d/m');
            $labels[] = $label;

            $record = $dailyData->get($dateStr);
            $revenueSeries[] = $record ? (float) $record->revenue : 0.0;
            $orderSeries[] = $record ? (int) $record->order_count : 0;
        }

        return [
            'labels' => $labels,
            'revenue' => $revenueSeries,
            'orders' => $orderSeries,
        ];
    }

    /**
     * Aggregates revenue breakdown by Root Category.
     */
    protected function getCategoryDistribution(array $currentPeriod): array
    {
        $items = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->leftJoin('category_product', 'products.id', '=', 'category_product.product_id')
            ->leftJoin('categories', 'category_product.category_id', '=', 'categories.id')
            ->whereBetween('orders.created_at', [$currentPeriod['start'], $currentPeriod['end']])
            ->where(function($q) {
                $q->where('orders.payment_status', 'paid')
                  ->orWhere('orders.status', 'completed');
            })
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                'categories.parent_id',
                DB::raw('SUM(order_items.total) as total_revenue'),
                DB::raw('SUM(order_items.quantity) as total_quantity')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.parent_id')
            ->get();

        // Group into primary/root categories
        $allCategories = Category::all()->keyBy('id');

        $groupedStats = [];
        $totalRev = 0;

        foreach ($items as $item) {
            $catId = $item->category_id;
            $cat = $allCategories->get($catId);
            
            // Traverse up to root category
            while ($cat && $cat->parent_id && $allCategories->has($cat->parent_id)) {
                $cat = $allCategories->get($cat->parent_id);
            }

            $rootName = $cat ? $cat->name : 'Chưa phân loại';
            $rev = (float) $item->total_revenue;
            $qty = (int) $item->total_quantity;

            if (!isset($groupedStats[$rootName])) {
                $groupedStats[$rootName] = [
                    'name' => $rootName,
                    'revenue' => 0,
                    'quantity' => 0,
                ];
            }

            $groupedStats[$rootName]['revenue'] += $rev;
            $groupedStats[$rootName]['quantity'] += $qty;
            $totalRev += $rev;
        }

        // Calculate % share
        foreach ($groupedStats as &$stat) {
            $stat['share'] = $totalRev > 0 ? round(($stat['revenue'] / $totalRev) * 100, 1) : 0;
        }

        // Prepare chart structure
        $chartLabels = array_keys($groupedStats);
        $chartValues = array_column($groupedStats, 'revenue');

        return [
            'chart' => [
                'labels' => !empty($chartLabels) ? $chartLabels : ['Không có dữ liệu'],
                'values' => !empty($chartValues) ? $chartValues : [0],
            ],
            'stats' => array_values($groupedStats),
        ];
    }

    /**
     * Calculates customer cohort statistics (New vs. Returning buyers).
     */
    protected function getCustomerCohortStats(array $periods): array
    {
        // 1. New registered users
        $newUsersCurrent = User::where('role', 'user')
            ->whereBetween('created_at', [$periods['current']['start'], $periods['current']['end']])
            ->count();

        $newUsersPrev = User::where('role', 'user')
            ->whereBetween('created_at', [$periods['previous']['start'], $periods['previous']['end']])
            ->count();

        // 2. Active buyers in current period
        $activeBuyers = Order::whereBetween('created_at', [$periods['current']['start'], $periods['current']['end']])
            ->whereNotNull('user_id')
            ->where(function($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            })
            ->pluck('user_id')
            ->unique();

        $returningCount = 0;
        $firstTimeCount = 0;

        foreach ($activeBuyers as $userId) {
            $priorOrdersCount = Order::where('user_id', $userId)
                ->where('created_at', '<', $periods['current']['start'])
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($priorOrdersCount > 0) {
                $returningCount++;
            } else {
                $firstTimeCount++;
            }
        }

        $totalBuyers = count($activeBuyers);
        $returningRate = $totalBuyers > 0 ? round(($returningCount / $totalBuyers) * 100, 1) : 0.0;
        $firstTimeRate = $totalBuyers > 0 ? round(($firstTimeCount / $totalBuyers) * 100, 1) : 0.0;

        return [
            'new_users_current' => $newUsersCurrent,
            'new_users_prev' => $newUsersPrev,
            'new_users_growth' => $this->calculateGrowth($newUsersCurrent, $newUsersPrev),
            'first_time_buyers' => $firstTimeCount,
            'returning_buyers' => $returningCount,
            'returning_rate' => $returningRate,
            'first_time_rate' => $firstTimeRate,
            'labels' => ['Khách mua lần đầu', 'Khách quay lại'],
            'values' => [$firstTimeCount, $returningCount],
        ];
    }

    /**
     * Resolves Top 10 Best Sellers and Slow Moving Products.
     */
    protected function getTopAndSlowProducts(array $currentPeriod): array
    {
        // 1. Top 10 Best Sellers
        $topItems = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->leftJoin('category_product', 'products.id', '=', 'category_product.product_id')
            ->leftJoin('categories', 'category_product.category_id', '=', 'categories.id')
            ->whereBetween('orders.created_at', [$currentPeriod['start'], $currentPeriod['end']])
            ->where(function($q) {
                $q->where('orders.payment_status', 'paid')
                  ->orWhere('orders.status', 'completed');
            })
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'products.id',
                'products.name',
                'products.slug',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.total) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.slug', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();

        foreach ($topItems as $item) {
            $product = Product::with(['variants', 'images'])->find($item->id);
            $item->thumbnail = $product ? $product->primary_image_url : null;
            $item->total_stock = $product ? $product->variants->sum('stock_quantity') : 0;
            $item->sku = $product && $product->variants->first() ? $product->variants->first()->sku : 'SP-' . $item->id;
        }

        // 2. Slow Moving Products (In-stock, sold < 3 in period)
        $topSoldProductIds = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->whereBetween('orders.created_at', [$currentPeriod['start'], $currentPeriod['end']])
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('product_variants.product_id')
            ->havingRaw('SUM(order_items.quantity) >= 3')
            ->pluck('product_variants.product_id')
            ->toArray();

        $slowProducts = Product::where('is_active', true)
            ->whereNotIn('id', $topSoldProductIds)
            ->with(['variants', 'categories', 'images'])
            ->get()
            ->filter(function($p) {
                return $p->variants->sum('stock_quantity') > 0;
            })
            ->sortByDesc(function($p) {
                return $p->variants->sum('stock_quantity');
            })
            ->take(10)
            ->values();

        foreach ($slowProducts as $p) {
            $soldInPeriod = (int) OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
                ->where('product_variants.product_id', $p->id)
                ->whereBetween('orders.created_at', [$currentPeriod['start'], $currentPeriod['end']])
                ->where('orders.status', '!=', 'cancelled')
                ->sum('order_items.quantity');

            $p->sold_in_period = $soldInPeriod;
            $p->total_stock = $p->variants->sum('stock_quantity');
            $p->sku = $p->variants->first() ? $p->variants->first()->sku : 'SP-' . $p->id;
            $p->category_name = $p->categories->first() ? $p->categories->first()->name : 'Chưa phân loại';
        }

        return [
            'top' => $topItems,
            'slow' => $slowProducts,
        ];
    }
}
