<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RfmAnalyticsService
{
    /**
     * Scores Recency on a 1-5 scale based on days since last order.
     */
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

    /**
     * Scores Frequency on a 1-5 scale based on total completed orders count.
     */
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

    /**
     * Scores Monetary on a 1-5 scale based on cumulative spending in VNĐ.
     */
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

    /**
     * Classifies customer into one of 6 cohorts based on R, F, M quintiles.
     */
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

    /**
     * Computes individual RFM scores and segments for all purchasing customers.
     */
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

    /**
     * Computes the favorite product category and top-selling product for each RFM segment.
     */
    public function getSegmentCategoryAffinities(array $customerRecords): array
    {
        $segmentOrderMap = [];
        $segmentMeta = [
            'Champions' => ['color' => '#6366f1', 'badge' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300', 'action' => 'Chăm sóc VIP & Quà tri ân sinh nhật'],
            'Loyal Customers' => ['color' => '#10b981', 'badge' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300', 'action' => 'Tích điểm thành viên & Voucher giới hạn'],
            'Potential Loyalists' => ['color' => '#3b82f6', 'badge' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300', 'action' => 'Gợi ý sản phẩm phối kèm & Giảm giá đơn thứ 2'],
            'New Customers' => ['color' => '#f59e0b', 'badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300', 'action' => 'Khảo sát sau mua & Hướng dẫn bảo quản đồ'],
            'At Risk' => ['color' => '#f43f5e', 'badge' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300', 'action' => 'Chiến dịch email/SMS nhớ bạn & Voucher sâu'],
            'Lost Customers' => ['color' => '#64748b', 'badge' => 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300', 'action' => 'Remarketing sản phẩm xả kho giá sốc'],
        ];

        foreach ($customerRecords as $c) {
            $seg = $c['segment'];
            if (!isset($segmentOrderMap[$seg])) {
                $segmentOrderMap[$seg] = [];
            }
            $segmentOrderMap[$seg] = array_merge($segmentOrderMap[$seg], $c['order_ids']);
        }

        $segmentStats = [];

        foreach ($segmentMeta as $segmentName => $meta) {
            $orderIds = array_unique($segmentOrderMap[$segmentName] ?? []);
            
            $favCategory = 'Chưa có dữ liệu';
            $topProduct = 'Chưa có dữ liệu';
            $catRevenue = 0.0;

            if (!empty($orderIds)) {
                $items = OrderItem::join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
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

    /**
     * Aggregates RFM scores, cards, segment share chart and customers for the admin dashboard.
     */
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
            $share = $totalCustomers > 0 ? round(($cnt / $totalCustomers) * 100, 1) : 0.0;
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
}
