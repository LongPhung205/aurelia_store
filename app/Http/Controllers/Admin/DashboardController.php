<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();
        $startOfMonth = $now->copy()->startOfMonth();

        // 1. Today's Operational Pulse
        $todayPaidOrdersQuery = Order::where(function($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            })
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$todayStart, $todayEnd]);

        $todayRevenue = (float) $todayPaidOrdersQuery->sum('total_amount');
        $todayOrdersCount = Order::whereBetween('created_at', [$todayStart, $todayEnd])->count();
        $pendingOrdersCount = Order::where('status', 'pending')->count();
        $criticalStockCount = ProductVariant::where('stock_quantity', '<=', 5)->count();
        $lowStockCount = ProductVariant::where('stock_quantity', '<=', 10)->count();
        $newUsersCount = User::where('role', 'user')->where('created_at', '>=', $startOfMonth)->count();
        $newUsersTodayCount = User::where('role', 'user')->whereBetween('created_at', [$todayStart, $todayEnd])->count();

        // Monthly revenue for backward compatibility
        $currentMonthRevenue = (float) Order::where(function($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            })
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_amount');

        // 2. Urgent Action Center Data
        // A: Pending fulfillment orders requiring immediate action
        $urgentPendingOrders = Order::with(['user', 'items.productVariant.product'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc') // Oldest first to avoid SLA breach
            ->take(6)
            ->get();

        // B: Critical stock alerts (<= 5 items left)
        $criticalStockProducts = ProductVariant::with(['product.images', 'color', 'size'])
            ->where('stock_quantity', '<=', 5)
            ->whereHas('product', function($q) {
                $q->where('status', 'active');
            })
            ->orderBy('stock_quantity', 'asc')
            ->take(6)
            ->get();

        // 3. 7-Day Sparkline Trend (Compact operational overview)
        $sevenDaysAgo = $now->copy()->subDays(6)->startOfDay();
        $trendData = Order::select(
                DB::raw('DATE(created_at) as order_date'),
                DB::raw("SUM(CASE WHEN (payment_status = 'paid' OR status = 'completed') AND status != 'cancelled' THEN total_amount ELSE 0 END) as revenue"),
                DB::raw("COUNT(id) as total_orders")
            )
            ->whereBetween('created_at', [$sevenDaysAgo, $todayEnd])
            ->groupBy('order_date')
            ->orderBy('order_date', 'asc')
            ->get()
            ->keyBy('order_date');

        $sparklineLabels = [];
        $sparklineRevenues = [];
        $sparklineOrders = [];
        $period = CarbonPeriod::create($sevenDaysAgo, $todayEnd);

        foreach ($period as $date) {
            $dStr = $date->format('Y-m-d');
            $sparklineLabels[] = $date->format('d/m');
            $record = $trendData->get($dStr);
            $sparklineRevenues[] = $record ? (float) $record->revenue : 0.0;
            $sparklineOrders[] = $record ? (int) $record->total_orders : 0;
        }

        $revenueChartData = [
            'labels' => $sparklineLabels,
            'data' => $sparklineRevenues,
            'orders' => $sparklineOrders,
        ];

        // 4. Payment breakdown
        $paymentMethodsCount = Order::select('payment_method', DB::raw('count(*) as total'))
            ->where('status', '!=', 'cancelled')
            ->groupBy('payment_method')
            ->get();

        $paymentChartData = [
            'labels' => $paymentMethodsCount->pluck('payment_method')->map(fn($item) => strtoupper($item ?: 'COD'))->toArray(),
            'data' => $paymentMethodsCount->pluck('total')->toArray(),
        ];

        // 5. Recent orders for legacy fallback
        $recentOrders = Order::with('user')->orderBy('created_at', 'desc')->take(5)->get();

        // 6. Top products for legacy fallback
        $topProducts = OrderItem::select('product_variant_id', 'product_name', 'variant_attributes', DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('order', function($q) use ($startOfMonth) {
                $q->where('status', '!=', 'cancelled')
                  ->where('created_at', '>=', $startOfMonth);
            })
            ->with(['productVariant.product', 'productVariant'])
            ->groupBy('product_variant_id', 'product_name', 'variant_attributes')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        return view('admin.dashboard.index', compact(
            'todayRevenue',
            'todayOrdersCount',
            'pendingOrdersCount',
            'criticalStockCount',
            'lowStockCount',
            'newUsersCount',
            'newUsersTodayCount',
            'currentMonthRevenue',
            'urgentPendingOrders',
            'criticalStockProducts',
            'revenueChartData',
            'paymentChartData',
            'recentOrders',
            'topProducts'
        ));
    }
}
