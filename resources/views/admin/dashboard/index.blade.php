@extends('admin.layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Tổng quan')

@section('content')
<div class="w-full min-h-[calc(100vh-90px)] p-4 md:p-6 bg-slate-50 dark:bg-slate-900/50">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- 1. Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            <!-- Revenue Card -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 flex items-center justify-center bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <i class="bi bi-currency-dollar text-2xl"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-400 uppercase">Tháng này</span>
                </div>
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-1">Doanh thu</h3>
                <div class="text-2xl md:text-3xl font-black text-slate-800 dark:text-white">
                    {{ number_format($currentMonthRevenue, 0, ',', '.') }}<span class="text-lg md:text-xl text-slate-400">đ</span>
                </div>
            </div>

            <!-- Orders Card -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl">
                        <i class="bi bi-bag text-2xl"></i>
                    </div>
                    <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="text-xs font-bold text-primary-600 hover:underline">Xem tất cả</a>
                </div>
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-1">Đơn chờ xử lý</h3>
                <div class="text-2xl md:text-3xl font-black text-slate-800 dark:text-white">
                    {{ number_format($pendingOrdersCount) }}
                </div>
            </div>

            <!-- Users Card -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 flex items-center justify-center bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-xl">
                        <i class="bi bi-people text-2xl"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-400 uppercase">Khách mới</span>
                </div>
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-1">Khách hàng</h3>
                <div class="text-2xl md:text-3xl font-black text-slate-800 dark:text-white">
                    {{ number_format($newUsersCount) }}
                </div>
            </div>

            <!-- Low Stock Card -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 flex items-center justify-center bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-xl">
                        <i class="bi bi-exclamation-triangle text-2xl"></i>
                    </div>
                    <a href="{{ route('admin.inventory.index') }}" class="text-xs font-bold text-primary-600 hover:underline">Nhập hàng</a>
                </div>
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-1">Sản phẩm sắp hết</h3>
                <div class="text-2xl md:text-3xl font-black text-slate-800 dark:text-white">
                    {{ number_format($lowStockCount) }}
                </div>
            </div>
        </div>

        <!-- 2. Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Revenue Area Chart -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-4">Doanh thu 30 ngày qua</h2>
                <div id="revenue-chart" class="w-full h-80"></div>
            </div>

            <!-- Payment Methods Doughnut Chart -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-4">Cơ cấu thanh toán</h2>
                <div id="payment-chart" class="w-full h-80 flex items-center justify-center"></div>
            </div>
        </div>

        <!-- 3. Tables Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Products -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">Top 5 sản phẩm bán chạy (Tháng này)</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 text-xs uppercase font-medium">
                            <tr>
                                <th class="px-6 py-4">Sản phẩm</th>
                                <th class="px-6 py-4 text-center">Đã bán</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($topProducts as $item)
                                @php
                                    $imgUrl = $item->productVariant->thumbnail_url ?? ($item->productVariant->product->primary_image_url ?? 'https://via.placeholder.com/150');
                                    if ($imgUrl && !Str::startsWith($imgUrl, ['http://', 'https://'])) {
                                        $imgUrl = Storage::url($imgUrl);
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/20">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $imgUrl }}" alt="{{ $item->product_name }}" class="w-12 h-12 rounded-lg object-cover border border-slate-200 dark:border-slate-600">
                                            <div>
                                                <div class="font-bold text-slate-800 dark:text-slate-200 line-clamp-1 max-w-[200px]" title="{{ $item->product_name }}">{{ $item->product_name }}</div>
                                                <div class="text-xs text-slate-500 mt-1">{{ $item->variant_attributes ?: 'Mặc định' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                            {{ $item->total_sold }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-8 text-center text-slate-500">Chưa có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">Đơn hàng chờ xử lý</h2>
                    <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-primary-600 hover:underline">Xem tất cả</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 text-xs uppercase font-medium">
                            <tr>
                                <th class="px-6 py-4">Mã đơn</th>
                                <th class="px-6 py-4">Khách hàng</th>
                                <th class="px-6 py-4 text-right">Tổng tiền</th>
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($recentOrders as $order)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/20">
                                    <td class="px-6 py-4 font-bold text-slate-800 dark:text-slate-200">
                                        #ORD-{{ $order->id }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-800 dark:text-slate-200">{{ $order->customer_name }}</div>
                                        <div class="text-xs text-slate-500">{{ $order->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-rose-600 dark:text-rose-400">
                                        {{ number_format($order->total_amount, 0, ',', '.') }}đ
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-flex items-center justify-center p-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-primary-100 hover:text-primary-600 dark:hover:bg-primary-900/30 transition-colors">
                                            <i class="bi bi-chevron-right text-xs"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-500">Tuyệt vời! Không có đơn hàng nào bị tồn đọng.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<!-- Load ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDarkMode = document.documentElement.classList.contains('dark');
    const textColor = isDarkMode ? '#94a3b8' : '#64748b';

    // 1. Revenue Area Chart
    const revenueData = @json($revenueChartData);
    
    const revenueOptions = {
        series: [{
            name: 'Doanh thu',
            data: revenueData.data
        }],
        chart: {
            type: 'area',
            height: 320,
            toolbar: { show: false },
            fontFamily: 'inherit',
            background: 'transparent'
        },
        colors: ['#6366f1'], // Primary color
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.05,
                stops: [0, 100]
            }
        },
        dataLabels: { enabled: false },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        xaxis: {
            categories: revenueData.labels,
            labels: {
                style: { colors: textColor }
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                style: { colors: textColor },
                formatter: (value) => {
                    return (value / 1000000).toFixed(1) + "M";
                }
            }
        },
        grid: {
            borderColor: isDarkMode ? '#334155' : '#e2e8f0',
            strokeDashArray: 4,
            yaxis: { lines: { show: true } }
        },
        theme: {
            mode: isDarkMode ? 'dark' : 'light'
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return new Intl.NumberFormat('vi-VN').format(val) + " đ";
                }
            }
        }
    };
    
    const revenueChart = new ApexCharts(document.querySelector("#revenue-chart"), revenueOptions);
    revenueChart.render();

    // 2. Payment Methods Doughnut Chart
    const paymentData = @json($paymentChartData);
    
    // Ensure we have data, otherwise show empty state
    const hasPaymentData = paymentData.data && paymentData.data.length > 0;
    
    const paymentOptions = {
        series: hasPaymentData ? paymentData.data : [1],
        labels: hasPaymentData ? paymentData.labels : ['Chưa có dữ liệu'],
        chart: {
            type: 'donut',
            height: 320,
            fontFamily: 'inherit',
            background: 'transparent'
        },
        colors: ['#3b82f6', '#ec4899', '#10b981', '#f59e0b', '#64748b'],
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            color: textColor
                        },
                        value: {
                            show: true,
                            color: isDarkMode ? '#f8fafc' : '#0f172a',
                            fontSize: '24px',
                            fontWeight: 700
                        },
                        total: {
                            show: true,
                            showAlways: false,
                            label: 'Tổng đơn',
                            color: textColor
                        }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        stroke: { show: false },
        legend: {
            position: 'bottom',
            labels: { colors: textColor }
        },
        theme: {
            mode: isDarkMode ? 'dark' : 'light'
        }
    };
    
    const paymentChart = new ApexCharts(document.querySelector("#payment-chart"), paymentOptions);
    paymentChart.render();
    
    // Support dynamic theme switching for charts
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'class') {
                const newIsDark = document.documentElement.classList.contains('dark');
                const newTheme = newIsDark ? 'dark' : 'light';
                const newTextColor = newIsDark ? '#94a3b8' : '#64748b';
                const newGridColor = newIsDark ? '#334155' : '#e2e8f0';
                
                revenueChart.updateOptions({
                    theme: { mode: newTheme },
                    xaxis: { labels: { style: { colors: newTextColor } } },
                    yaxis: { labels: { style: { colors: newTextColor } } },
                    grid: { borderColor: newGridColor }
                });
                
                paymentChart.updateOptions({
                    theme: { mode: newTheme },
                    legend: { labels: { colors: newTextColor } },
                    plotOptions: { pie: { donut: { labels: { name: { color: newTextColor }, value: { color: newIsDark ? '#f8fafc' : '#0f172a' }, total: { color: newTextColor } } } } }
                });
            }
        });
    });
    
    observer.observe(document.documentElement, { attributes: true });
});
</script>
@endpush
