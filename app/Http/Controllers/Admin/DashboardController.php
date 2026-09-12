<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $startOfMonth = $now->startOfMonth()->copy();
        
        // 1. Thống kê tổng quan
        $currentMonthRevenue = Order::where(function($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            })
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_amount');

        $pendingOrdersCount = Order::where('status', 'pending')->count();
        
        $newUsersCount = User::where('role', 'user')
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        $lowStockCount = ProductVariant::where('stock_quantity', '<=', 10)->count();

        // 2. Dữ liệu biểu đồ Doanh thu (30 ngày gần nhất)
        $thirtyDaysAgo = Carbon::now()->subDays(29)->startOfDay();
        
        $revenueData = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->where(function($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            })
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Chuẩn bị mảng 30 ngày để lấp đầy ngày không có doanh thu
        $dates = [];
        $revenues = [];
        $tempRevenue = $revenueData->pluck('revenue', 'date')->toArray();

        for ($i = 0; $i < 30; $i++) {
            $dateString = Carbon::now()->subDays(29 - $i)->format('Y-m-d');
            $dates[] = Carbon::parse($dateString)->format('d/m');
            $revenues[] = $tempRevenue[$dateString] ?? 0;
        }

        $revenueChartData = [
            'labels' => $dates,
            'data' => $revenues
        ];

        // 3. Dữ liệu biểu đồ Thanh toán (Pie chart)
        $paymentMethodsCount = Order::select('payment_method', DB::raw('count(*) as total'))
            ->where('status', '!=', 'cancelled')
            ->groupBy('payment_method')
            ->get();
            
        $paymentChartData = [
            'labels' => $paymentMethodsCount->pluck('payment_method')->map(function($item) {
                return strtoupper($item);
            })->toArray(),
            'data' => $paymentMethodsCount->pluck('total')->toArray()
        ];

        // 4. Bảng Đơn hàng mới nhất (5 đơn)
        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 5. Bảng Sản phẩm bán chạy nhất (Top 5 trong tháng)
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
            'currentMonthRevenue',
            'pendingOrdersCount',
            'newUsersCount',
            'lowStockCount',
            'revenueChartData',
            'paymentChartData',
            'recentOrders',
            'topProducts'
        ));
    }
}
