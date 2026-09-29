@extends('admin.layouts.admin')

@section('title', 'Trung Tâm Điều Hành')
@section('page_title', 'Dashboard Điều Hành')

@section('content')
<div class="w-full min-h-[calc(100vh-90px)] p-4 md:p-6 bg-slate-50/70 dark:bg-slate-900/50">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- 1. Header Cockpit -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-700/80 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400">
                        <i class="bi bi-speedometer2 text-lg"></i>
                    </span>
                    <h2 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">
                        Xin chào, {{ auth()->user()->name }}!
                    </h2>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Bảng điều hành tác nghiệp trực tiếp &bull; Hôm nay, {{ now()->format('d/m/Y') }}
                </p>
            </div>

            <!-- Strategic Action Shortcut -->
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.analytics.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white rounded-xl text-xs font-bold transition-all shadow-sm shadow-indigo-200 dark:shadow-none hover:shadow-md">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Phân Tích Chuyên Sâu (RFM & Apriori)</span>
                    <i class="bi bi-arrow-right text-[11px]"></i>
                </a>
            </div>
        </div>

        <!-- 2. Today's Bento Operational Pulse (4 KPI Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
            <!-- Card 1: Today Revenue -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-700/80 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <i class="bi bi-currency-dollar text-xl"></i>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                        Hôm nay
                    </span>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Doanh thu hôm nay</h3>
                    <div class="text-2xl font-black text-slate-800 dark:text-white tabular-nums tracking-tight">
                        {{ number_format($todayRevenue, 0, ',', '.') }} <span class="text-sm font-semibold text-slate-400">đ</span>
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Tổng <span class="font-bold text-slate-700 dark:text-slate-300">{{ $todayOrdersCount }}</span> đơn phát sinh
                    </div>
                </div>
            </div>

            <!-- Card 2: Pending Orders Urgent -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-700/80 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 flex items-center justify-center bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-xl">
                        <i class="bi bi-hourglass-split text-xl"></i>
                    </div>
                    @if($pendingOrdersCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 animate-pulse">
                            Cần duyệt gấp
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                            Đã xử lý hết
                        </span>
                    @endif
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Đơn chờ đóng gói</h3>
                    <div class="text-2xl font-black text-slate-800 dark:text-white tabular-nums tracking-tight">
                        {{ number_format($pendingOrdersCount) }} <span class="text-sm font-semibold text-slate-400">đơn</span>
                    </div>
                    <div class="mt-1">
                        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline inline-flex items-center gap-1">
                            <span>Mở danh sách chờ duyệt</span> &rarr;
                        </a>
                    </div>
                </div>
            </div>

            <!-- Card 3: Critical Low Stock (< 5) -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-700/80 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 flex items-center justify-center bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-xl">
                        <i class="bi bi-exclamation-octagon text-xl"></i>
                    </div>
                    @if($criticalStockCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                            Kho nguy cấp
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                            Kho an toàn
                        </span>
                    @endif
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tồn kho nguy cấp (&le; 5)</h3>
                    <div class="text-2xl font-black text-slate-800 dark:text-white tabular-nums tracking-tight">
                        {{ number_format($criticalStockCount) }} <span class="text-sm font-semibold text-slate-400">mặt hàng</span>
                    </div>
                    <div class="mt-1">
                        <a href="{{ route('admin.inventory.index') }}" class="text-xs font-bold text-rose-600 dark:text-rose-400 hover:underline inline-flex items-center gap-1">
                            <span>Nhập thêm hàng</span> &rarr;
                        </a>
                    </div>
                </div>
            </div>

            <!-- Card 4: New Users Today -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-700/80 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 flex items-center justify-center bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-xl">
                        <i class="bi bi-person-check text-xl"></i>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                        Tháng này: {{ number_format($newUsersCount) }}
                    </span>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Khách mới hôm nay</h3>
                    <div class="text-2xl font-black text-slate-800 dark:text-white tabular-nums tracking-tight">
                        {{ number_format($newUsersTodayCount) }} <span class="text-sm font-semibold text-slate-400">tài khoản</span>
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Tăng trưởng khách hàng tiềm năng
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Urgent Action Center (2 Cột Bento Grid) -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            <!-- Cột trái: Đơn Hàng Cần Đóng Gói & Xuất Kho (3/5) -->
            <div class="lg:col-span-3 bg-white dark:bg-slate-800 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-700/80 p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700/60 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                            <h3 class="font-bold text-base text-slate-800 dark:text-white">
                                Đơn Hàng Cần Đóng Gói & Xuất Kho
                            </h3>
                        </div>
                        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline">
                            Xem tất cả đơn chờ &rarr;
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left text-slate-600 dark:text-slate-300">
                            <thead class="uppercase bg-slate-50 dark:bg-slate-700/40 text-[10px] text-slate-400 font-semibold tracking-wider">
                                <tr>
                                    <th class="px-3 py-2.5 rounded-l-lg">Mã đơn</th>
                                    <th class="px-3 py-2.5">Khách hàng</th>
                                    <th class="px-3 py-2.5">Tổng tiền</th>
                                    <th class="px-3 py-2.5">Thanh toán</th>
                                    <th class="px-3 py-2.5 text-right rounded-r-lg">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                @forelse($urgentPendingOrders as $order)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-3 py-3 font-mono font-bold text-slate-800 dark:text-white">
                                        #{{ $order->order_code ?? $order->id }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="font-bold text-slate-800 dark:text-slate-200 line-clamp-1">
                                            {{ $order->customer_name }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono">
                                            {{ $order->customer_phone }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 font-bold text-indigo-600 dark:text-indigo-400 tabular-nums">
                                        {{ number_format($order->total_amount, 0, ',', '.') }} đ
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $order->payment_method === 'cod' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' }}">
                                            {{ $order->payment_method ?? 'COD' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <a href="{{ route('admin.orders.show', $order->id) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-primary-50 text-primary-700 dark:bg-primary-950/40 dark:text-primary-300 hover:bg-primary-100 border border-primary-200 dark:border-primary-800 transition-colors">
                                            <span>Xử lý</span>
                                            <i class="bi bi-arrow-right text-[10px]"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-8 text-center text-slate-400">
                                        <i class="bi bi-check-circle-fill text-emerald-500 text-2xl block mb-1"></i>
                                        <span>Tuyệt vời! Hiện tại không có đơn hàng nào bị tồn đọng chờ duyệt.</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Cảnh Báo Tồn Kho Khẩn Cấp (2/5) -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-700/80 p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700/60 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            <h3 class="font-bold text-base text-slate-800 dark:text-white">
                                Tồn Kho Nguy Cấp (&le; 5)
                            </h3>
                        </div>
                        <a href="{{ route('admin.inventory.index') }}" class="text-xs font-bold text-rose-600 dark:text-rose-400 hover:underline">
                            Kiểm kho &rarr;
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($criticalStockProducts as $variant)
                            @php
                                $p = $variant->product;
                                $rawImg = $p ? $p->primary_image_url : null;
                                $imgSrc = $rawImg ? ((str_starts_with($rawImg, 'http://') || str_starts_with($rawImg, 'https://')) ? $rawImg : asset('storage/' . ltrim($rawImg, '/'))) : null;
                            @endphp
                            <div class="flex items-center justify-between gap-3 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-700/30 border border-slate-100 dark:border-slate-700/60">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    @if($imgSrc)
                                        <img src="{{ $imgSrc }}" alt="{{ $p->name ?? '' }}" class="w-9 h-9 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-9 h-9 rounded-lg bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-400 shrink-0\'><i class=\'bi bi-image\'></i></div>';">
                                    @else
                                        <div class="w-9 h-9 rounded-lg bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="font-bold text-xs text-slate-800 dark:text-white truncate">
                                            {{ $p->name ?? 'Sản phẩm' }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono">
                                            SKU: {{ $variant->sku }} &bull; {{ $variant->size->name ?? '' }} {{ $variant->color->name ?? '' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-black bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 tabular-nums">
                                        Còn {{ $variant->stock_quantity }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-400 text-xs">
                                <i class="bi bi-box-seam text-2xl text-emerald-500 block mb-1"></i>
                                <span>Kho hàng dồi dào, không có sản phẩm nào dưới 5 cái.</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                @if($criticalStockCount > 0)
                <div class="pt-4 mt-2 border-t border-slate-100 dark:border-slate-700/60">
                    <a href="{{ route('admin.imports.create') }}" class="w-full py-2 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white rounded-xl text-xs font-bold text-center block transition-colors">
                        <i class="bi bi-plus-circle mr-1"></i> Tạo Phiếu Nhập Hàng Mới
                    </a>
                </div>
                @endif
            </div>

        </div>

        <!-- 4. Charts Overview (7-Day Trend Sparkline & Payment Methods) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- 7-Day Trend Chart -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-700/80">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-base text-slate-800 dark:text-white">Xu Hướng Doanh Số 7 Ngày Qua</h3>
                        <p class="text-xs text-slate-400">Doanh thu và số lượng đơn hàng ghi nhận theo ngày</p>
                    </div>
                    <a href="{{ route('admin.analytics.index', ['tab' => 'sales']) }}" class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline">
                        Báo cáo chi tiết &rarr;
                    </a>
                </div>
                <div id="revenue-chart" class="w-full h-64"></div>
            </div>

            <!-- Payment Methods Chart -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-700/80 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-base text-slate-800 dark:text-white mb-1">Cơ Cấu Phương Thức Thanh Toán</h3>
                    <p class="text-xs text-slate-400 mb-4">Tỷ lệ các kênh thanh toán khách lựa chọn</p>
                    <div id="payment-chart" class="w-full h-56 flex items-center justify-center"></div>
                </div>
            </div>
        </div>

        <!-- 5. Advanced Analytics Intelligence Banner -->
        <div class="bg-gradient-to-r from-indigo-900 via-slate-900 to-indigo-950 p-6 rounded-2xl text-white shadow-md relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="space-y-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 text-[11px] font-bold border border-indigo-400/30">
                        <i class="bi bi-stars"></i> Trí Tuệ Dữ Liệu & Thuật Toán E-Commerce
                    </span>
                    <h3 class="text-lg font-black tracking-tight">Khai Phá Tiềm Năng Khách Hàng Với RFM & Apriori</h3>
                    <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
                        Khám phá 6 nhóm phân khúc khách hàng (Champions, Loyal, At Risk) và tìm ra các cặp sản phẩm thường được mua cùng nhau để tạo chiến dịch khuyến mãi combo tăng vọt doanh số.
                    </p>
                </div>
                <a href="{{ route('admin.analytics.index', ['tab' => 'rfm']) }}" class="px-5 py-2.5 rounded-xl bg-white text-indigo-950 font-bold text-xs hover:bg-indigo-50 transition-colors shadow-sm shrink-0 text-center">
                    Khám Phá Ngay &rarr;
                </a>
            </div>
        </div>

    </div>
</div>

<!-- ApexCharts Script -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDarkMode = document.documentElement.classList.contains('dark');
    const textColor = isDarkMode ? '#94a3b8' : '#64748b';
    const gridColor = isDarkMode ? '#334155' : '#f1f5f9';

    // 1. 7-Day Revenue Sparkline
    const chartData = @json($revenueChartData);
    
    const revenueOptions = {
        series: [{
            name: 'Doanh thu',
            data: chartData.data
        }],
        chart: {
            type: 'area',
            height: 250,
            toolbar: { show: false },
            fontFamily: 'inherit',
            background: 'transparent'
        },
        colors: ['#6366f1'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.35,
                opacityTo: 0.05,
                stops: [0, 100]
            }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2.5 },
        xaxis: {
            categories: chartData.labels,
            labels: { style: { colors: textColor } },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                style: { colors: textColor },
                formatter: (val) => (val / 1000000).toFixed(1) + "M"
            }
        },
        grid: {
            borderColor: gridColor,
            strokeDashArray: 4,
            yaxis: { lines: { show: true } }
        },
        theme: { mode: isDarkMode ? 'dark' : 'light' },
        tooltip: {
            y: {
                formatter: (val) => new Intl.NumberFormat('vi-VN').format(val) + " đ"
            }
        }
    };
    
    const revenueChart = new ApexCharts(document.querySelector("#revenue-chart"), revenueOptions);
    revenueChart.render();

    // 2. Payment Methods Doughnut
    const paymentData = @json($paymentChartData);
    const hasPaymentData = paymentData.data && paymentData.data.length > 0;
    
    const paymentOptions = {
        series: hasPaymentData ? paymentData.data : [1],
        labels: hasPaymentData ? paymentData.labels : ['Chưa có đơn'],
        chart: {
            type: 'donut',
            height: 220,
            fontFamily: 'inherit',
            background: 'transparent'
        },
        colors: ['#6366f1', '#10b981', '#f59e0b', '#3b82f6', '#ec4899'],
        plotOptions: {
            pie: {
                donut: {
                    size: '72%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
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
        theme: { mode: isDarkMode ? 'dark' : 'light' }
    };
    
    const paymentChart = new ApexCharts(document.querySelector("#payment-chart"), paymentOptions);
    paymentChart.render();

    // Support dark mode toggles
    const observer = new MutationObserver(() => {
        const dark = document.documentElement.classList.contains('dark');
        const tColor = dark ? '#94a3b8' : '#64748b';
        const gColor = dark ? '#334155' : '#f1f5f9';

        revenueChart.updateOptions({
            theme: { mode: dark ? 'dark' : 'light' },
            xaxis: { labels: { style: { colors: tColor } } },
            yaxis: { labels: { style: { colors: tColor } } },
            grid: { borderColor: gColor }
        });

        paymentChart.updateOptions({
            theme: { mode: dark ? 'dark' : 'light' },
            legend: { labels: { colors: tColor } }
        });
    });

    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
</script>
@endsection
