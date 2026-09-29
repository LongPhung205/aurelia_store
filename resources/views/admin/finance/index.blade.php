@extends('admin.layouts.admin')

@section('title', 'Thống kê Tài chính')
@section('page_title', 'Thống kê Tài chính & Dòng tiền')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';

    // 1. Revenue Timeline Chart
    const ctxTimeline = document.getElementById('timelineChart')?.getContext('2d');
    if (ctxTimeline) {
        new Chart(ctxTimeline, {
            type: 'line',
            data: {
                labels: @json($chartData['timeline_labels']),
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: @json($chartData['timeline_values']),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ' ' + new Intl.NumberFormat('vi-VN').format(ctx.raw) + ' VNĐ'
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor },
                        ticks: { color: textColor, font: { size: 11 } }
                    },
                    y: {
                        grid: { color: gridColor },
                        ticks: {
                            color: textColor,
                            font: { size: 11 },
                            callback: (val) => new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(val) + 'đ'
                        }
                    }
                }
            }
        });
    }

    // 2. Payment Methods Donut Chart
    const ctxDonut = document.getElementById('donutChart')?.getContext('2d');
    if (ctxDonut) {
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: @json($chartData['donut_labels']),
                datasets: [{
                    data: @json($chartData['donut_values']),
                    backgroundColor: ['#f59e0b', '#3b82f6', '#ec4899'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: textColor, boxWidth: 12, font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ' ' + ctx.label + ': ' + new Intl.NumberFormat('vi-VN').format(ctx.raw) + ' VNĐ'
                        }
                    }
                },
                cutout: '70%',
            }
        });
    }
});
</script>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Date Range Filter Bar -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Quick Preset Tabs -->
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-900 rounded-xl overflow-x-auto w-full md:w-auto">
            <a href="{{ route('admin.finance.index', ['range' => 'today']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $range === 'today' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">Hôm nay</a>
            <a href="{{ route('admin.finance.index', ['range' => 'last_7_days']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $range === 'last_7_days' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">7 ngày qua</a>
            <a href="{{ route('admin.finance.index', ['range' => 'this_month']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $range === 'this_month' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">Tháng này</a>
            <a href="{{ route('admin.finance.index', ['range' => 'this_year']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $range === 'this_year' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">Năm nay</a>
        </div>

        <!-- Custom Date Range Form -->
        <form method="GET" action="{{ route('admin.finance.index') }}" class="flex items-center gap-2 w-full md:w-auto">
            <input type="date" name="from_date" value="{{ $fromDate }}" class="text-xs py-1.5 px-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
            <span class="text-slate-400 text-xs">đến</span>
            <input type="date" name="to_date" value="{{ $toDate }}" class="text-xs py-1.5 px-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
            <button type="submit" class="py-1.5 px-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-medium transition-colors shadow-sm cursor-pointer">
                Áp dụng
            </button>
        </form>
    </div>

    <!-- 4 Main KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Revenue Collected -->
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Doanh thu thực thu</span>
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 text-base"><i class="bi bi-wallet2"></i></span>
            </div>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-2">{{ number_format($kpi['collected_revenue']) }}đ</h3>
            <p class="text-xs text-slate-500 mt-1">Đã vào tài khoản / nhận tiền COD</p>
        </div>

        <!-- Pending In-Transit Revenue -->
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Doanh thu chờ thu (COD)</span>
                <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-500 text-base"><i class="bi bi-truck"></i></span>
            </div>
            <h3 class="text-2xl font-bold text-amber-500 mt-2">{{ number_format($kpi['pending_revenue']) }}đ</h3>
            <p class="text-xs text-slate-500 mt-1">Đơn COD đang giao / chờ đối soát</p>
        </div>

        <!-- Cost of Goods -->
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Chi phí vốn nhập kho</span>
                <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 text-base"><i class="bi bi-box-seam"></i></span>
            </div>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-2">{{ number_format($kpi['cost_of_imports']) }}đ</h3>
            <p class="text-xs text-slate-500 mt-1">Tổng tiền các phiếu nhập hoàn tất</p>
        </div>

        <!-- Estimated Gross Profit -->
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Lợi nhuận gộp ước tính</span>
                <span class="p-2 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 text-base"><i class="bi bi-graph-up-arrow"></i></span>
            </div>
            <h3 class="text-2xl font-bold {{ $kpi['gross_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600' }} mt-2">
                {{ number_format($kpi['gross_profit']) }}đ
            </h3>
            <p class="text-xs text-slate-500 mt-1">Tỷ suất lợi nhuận: <span class="font-bold text-purple-600">{{ $kpi['profit_margin'] }}%</span></p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Revenue Trend Line Chart -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="bi bi-bar-chart-fill text-primary-600"></i> Xu hướng Doanh thu theo thời gian
                </h4>
                <span class="text-xs text-slate-400">{{ Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($toDate)->format('d/m/Y') }}</span>
            </div>
            <div class="h-72">
                <canvas id="timelineChart"></canvas>
            </div>
        </div>

        <!-- Payment Methods Donut Chart -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="bi bi-pie-chart-fill text-pink-500"></i> Tỷ trọng Cổng thanh toán
                </h4>
            </div>
            <div class="h-64 flex-1 flex items-center justify-center">
                <canvas id="donutChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Payment Methods Breakdown Table -->
    <x-admin.card title="Dòng tiền chi tiết theo Phương thức thanh toán" icon="bi bi-credit-card-2-front">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Cổng thanh toán</th>
                        <th class="px-4 py-3 font-semibold text-center">Tổng số giao dịch</th>
                        <th class="px-4 py-3 font-semibold">Doanh thu thực thu</th>
                        <th class="px-4 py-3 font-semibold">Doanh thu chờ thu</th>
                        <th class="px-4 py-3 font-semibold text-right">Tác vụ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @foreach($methodSummary as $m)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                        <td class="px-4 py-3 font-semibold text-slate-800 dark:text-white">
                            @if($m['key'] === 'payos')
                                <span class="inline-flex items-center gap-1.5"><i class="bi bi-bank text-blue-600"></i> {{ $m['name'] }}</span>
                            @elseif($m['key'] === 'momo')
                                <span class="inline-flex items-center gap-1.5"><i class="bi bi-wallet2 text-pink-600"></i> {{ $m['name'] }}</span>
                            @else
                                <span class="inline-flex items-center gap-1.5"><i class="bi bi-cash text-amber-500"></i> {{ $m['name'] }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-slate-600 dark:text-slate-400 font-medium">
                            {{ number_format($m['count']) }}
                        </td>
                        <td class="px-4 py-3 font-bold text-emerald-600 dark:text-emerald-400">
                            {{ number_format($m['collected']) }}đ
                        </td>
                        <td class="px-4 py-3 text-amber-500 font-semibold">
                            {{ number_format($m['pending']) }}đ
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.transactions.index', ['payment_method' => $m['key']]) }}" class="text-primary-600 dark:text-primary-400 hover:underline font-medium">
                                Xem giao dịch <i class="bi bi-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>
</div>
@endsection
