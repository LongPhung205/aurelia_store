@extends('admin.layouts.admin')

@section('title', 'Phân tích & Báo cáo')
@section('page_title', 'Phân tích & Báo cáo Hoạt động Kinh doanh')

@section('content')
<div class="w-full min-h-[calc(100vh-90px)] p-4 md:p-6 bg-slate-50 dark:bg-slate-900/50 space-y-6">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- 1. Header Toolbar & Date Filters -->
        <div class="bg-white dark:bg-slate-800 p-4 md:p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center p-2 bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 rounded-xl">
                        <i class="bi bi-graph-up-arrow text-xl"></i>
                    </span>
                    <div>
                        <h2 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Trung Tâm Phân Tích & Báo Cáo</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Khoảng thời gian: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $data['periods']['current']['start']->format('d/m/Y') }}</span> - <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $data['periods']['current']['end']->format('d/m/Y') }}</span>
                            <span class="mx-1 text-slate-300 dark:text-slate-600">|</span>
                            So sánh với kỳ trước: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $data['periods']['previous']['start']->format('d/m/Y') }}</span> - <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $data['periods']['previous']['end']->format('d/m/Y') }}</span> ({{ $data['periods']['days'] }} ngày)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action & Presets Bar -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Preset Filter Pills -->
                <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-700/60 rounded-xl text-xs font-medium">
                    <a href="{{ route('admin.analytics.index', ['range' => 'today']) }}"
                       class="px-3 py-1.5 rounded-lg transition-all {{ $data['periods']['range'] === 'today' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                        Hôm nay
                    </a>
                    <a href="{{ route('admin.analytics.index', ['range' => 'last_7_days']) }}"
                       class="px-3 py-1.5 rounded-lg transition-all {{ $data['periods']['range'] === 'last_7_days' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                        7 ngày qua
                    </a>
                    <a href="{{ route('admin.analytics.index', ['range' => 'last_30_days']) }}"
                       class="px-3 py-1.5 rounded-lg transition-all {{ $data['periods']['range'] === 'last_30_days' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                        30 ngày qua
                    </a>
                    <a href="{{ route('admin.analytics.index', ['range' => 'this_month']) }}"
                       class="px-3 py-1.5 rounded-lg transition-all {{ $data['periods']['range'] === 'this_month' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                        Tháng này
                    </a>
                    <a href="{{ route('admin.analytics.index', ['range' => 'this_year']) }}"
                       class="px-3 py-1.5 rounded-lg transition-all {{ $data['periods']['range'] === 'this_year' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                        Năm nay
                    </a>
                </div>

                <!-- Custom Range Dropdown / Form -->
                <form action="{{ route('admin.analytics.index') }}" method="GET" class="flex items-center gap-1.5">
                    <input type="hidden" name="range" value="custom">
                    <input type="date" name="from_date" value="{{ request('from_date', $data['periods']['current']['start']->format('Y-m-d')) }}"
                           class="text-xs py-1.5 px-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 focus:ring-1 focus:ring-primary-500">
                    <span class="text-slate-400 text-xs">-</span>
                    <input type="date" name="to_date" value="{{ request('to_date', $data['periods']['current']['end']->format('Y-m-d')) }}"
                           class="text-xs py-1.5 px-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 focus:ring-1 focus:ring-primary-500">
                    <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white rounded-lg text-xs font-semibold transition-colors">
                        Lọc
                    </button>
                </form>

                <!-- Export Excel Button -->
                <a href="{{ route('admin.analytics.export', request()->all()) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition-all shadow-sm">
                    <i class="bi bi-file-earmark-excel"></i>
                    <span>Xuất Báo Cáo</span>
                </a>
            </div>
        </div>

        <!-- 2. 4 Strategic KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            
            <!-- KPI 1: Net Revenue -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 relative overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 flex items-center justify-center bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl">
                        <i class="bi bi-currency-dollar text-xl"></i>
                    </div>
                    @php $revGrowth = $data['kpis']['revenue']['growth']; @endphp
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold {{ $revGrowth >= 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400' }}">
                        <i class="bi {{ $revGrowth >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ $revGrowth >= 0 ? '+' : '' }}{{ $revGrowth }}%
                    </span>
                </div>
                <h3 class="text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider mb-1">Doanh thu thuần</h3>
                <div class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                    {{ number_format($data['kpis']['revenue']['current'], 0, ',', '.') }}<span class="text-sm font-normal text-slate-400 ml-0.5">đ</span>
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-2">
                    Kỳ trước: {{ number_format($data['kpis']['revenue']['previous'], 0, ',', '.') }} đ
                </p>
            </div>

            <!-- KPI 2: Completed Orders & Cancellation Rate -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 relative overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl">
                        <i class="bi bi-bag-check text-xl"></i>
                    </div>
                    @php $ordGrowth = $data['kpis']['orders']['growth']; @endphp
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold {{ $ordGrowth >= 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400' }}">
                        <i class="bi {{ $ordGrowth >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ $ordGrowth >= 0 ? '+' : '' }}{{ $ordGrowth }}%
                    </span>
                </div>
                <h3 class="text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider mb-1">Đơn hàng thành công</h3>
                <div class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                    {{ number_format($data['kpis']['orders']['current']) }}<span class="text-sm font-normal text-slate-400 ml-1">đơn</span>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500 mt-2">
                    <span>Kỳ trước: {{ number_format($data['kpis']['orders']['previous']) }} đơn</span>
                    <span class="text-rose-500 font-medium">Hủy: {{ $data['kpis']['orders']['cancel_rate'] }}%</span>
                </div>
            </div>

            <!-- KPI 3: Average Order Value (AOV) -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 relative overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 flex items-center justify-center bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-xl">
                        <i class="bi bi-receipt text-xl"></i>
                    </div>
                    @php $aovGrowth = $data['kpis']['aov']['growth']; @endphp
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold {{ $aovGrowth >= 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400' }}">
                        <i class="bi {{ $aovGrowth >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ $aovGrowth >= 0 ? '+' : '' }}{{ $aovGrowth }}%
                    </span>
                </div>
                <h3 class="text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider mb-1">Giá trị trung bình (AOV)</h3>
                <div class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                    {{ number_format($data['kpis']['aov']['current'], 0, ',', '.') }}<span class="text-sm font-normal text-slate-400 ml-0.5">đ</span>
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-2">
                    Kỳ trước: {{ number_format($data['kpis']['aov']['previous'], 0, ',', '.') }} đ
                </p>
            </div>

            <!-- KPI 4: New Customers & Retention -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 relative overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 flex items-center justify-center bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <i class="bi bi-people text-xl"></i>
                    </div>
                    @php $custGrowth = $data['kpis']['customers']['new_growth']; @endphp
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold {{ $custGrowth >= 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400' }}">
                        <i class="bi {{ $custGrowth >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ $custGrowth >= 0 ? '+' : '' }}{{ $custGrowth }}%
                    </span>
                </div>
                <h3 class="text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider mb-1">Khách hàng mới</h3>
                <div class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                    +{{ number_format($data['kpis']['customers']['new_current']) }}<span class="text-sm font-normal text-slate-400 ml-1">người</span>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500 mt-2">
                    <span>Kỳ trước: {{ number_format($data['kpis']['customers']['new_previous']) }}</span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Khách quay lại: {{ $data['kpis']['customers']['returning_rate'] }}%</span>
                </div>
            </div>

        </div>

        <!-- 3. Visual Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Mixed Timeline Chart (2 cols) -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-base font-bold text-slate-800 dark:text-white">Xu Hướng Doanh Thu & Đơn Hàng</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Doanh thu thuần (Cột) và Khối lượng đơn thành công (Đường) theo ngày</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                            <span class="w-3 h-3 rounded bg-indigo-500 inline-block"></span> Doanh thu
                        </span>
                        <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                            <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span> Số đơn
                        </span>
                    </div>
                </div>
                <div id="trend-mixed-chart" class="w-full h-80"></div>
            </div>

            <!-- Side Charts (Category & Customer Cohort) -->
            <div class="space-y-6">
                
                <!-- Category Revenue Share Donut -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                    <div class="mb-2">
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white">Cơ Cấu Doanh Thu Theo Danh Mục</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Tỷ trọng đóng góp doanh thu</p>
                    </div>
                    <div id="category-donut-chart" class="w-full h-44 flex items-center justify-center"></div>
                </div>

                <!-- Customer Cohort Donut -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                    <div class="mb-2">
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white">Khách Hàng Mới vs Khách Quay Lại</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Hành vi người mua trong giai đoạn này</p>
                    </div>
                    <div id="customer-cohort-chart" class="w-full h-44 flex items-center justify-center"></div>
                </div>

            </div>

        </div>

        <!-- 4. Deep Intelligence Multi-Tab Table -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            
            <!-- Tab Headers -->
            <div class="border-b border-slate-200 dark:border-slate-700 px-5 pt-4 flex items-center gap-4">
                <button type="button" onclick="switchAnalyticsTab('tab-top-products')" id="btn-tab-top-products"
                        class="analytics-tab-btn pb-3 text-sm font-bold text-primary-600 dark:text-primary-400 border-b-2 border-primary-600 dark:border-primary-400 transition-all flex items-center gap-2">
                    <i class="bi bi-star-fill text-amber-400"></i>
                    <span>Top 10 Bán Chạy</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 font-semibold">{{ count($data['topProducts']) }}</span>
                </button>

                <button type="button" onclick="switchAnalyticsTab('tab-slow-products')" id="btn-tab-slow-products"
                        class="analytics-tab-btn pb-3 text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 border-b-2 border-transparent transition-all flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-rose-500"></i>
                    <span>Sản Phẩm Bán Chậm (Cảnh Báo Tồn)</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 font-semibold">{{ count($data['slowProducts']) }}</span>
                </button>

                <button type="button" onclick="switchAnalyticsTab('tab-categories')" id="btn-tab-categories"
                        class="analytics-tab-btn pb-3 text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 border-b-2 border-transparent transition-all flex items-center gap-2">
                    <i class="bi bi-folder2-open text-blue-500"></i>
                    <span>Hiệu Suất Danh Mục</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-semibold">{{ count($data['categoryStats']) }}</span>
                </button>
            </div>

            <!-- Tab Content 1: Top 10 Best Sellers -->
            <div id="tab-top-products" class="analytics-tab-panel p-5">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                        <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-3 rounded-l-lg">Hạng</th>
                                <th class="px-4 py-3">Sản phẩm</th>
                                <th class="px-4 py-3">Danh mục</th>
                                <th class="px-4 py-3 text-center">Đã bán</th>
                                <th class="px-4 py-3 text-right">Doanh thu đóng góp</th>
                                <th class="px-4 py-3 text-center rounded-r-lg">Tồn kho hiện tại</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse($data['topProducts'] as $index => $prod)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3 font-black text-slate-400">
                                    @if($index === 0)
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 text-xs font-bold">1</span>
                                    @elseif($index === 1)
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300 text-xs font-bold">2</span>
                                    @elseif($index === 2)
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-800/10 text-amber-800 dark:bg-amber-800/30 dark:text-amber-400 text-xs font-bold">3</span>
                                    @else
                                        <span class="ml-2">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($prod->thumbnail)
                                            <img src="{{ $prod->thumbnail }}" alt="{{ $prod->name }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                                                <i class="bi bi-image text-lg"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.products.edit', $prod->id) }}" class="font-semibold text-slate-800 dark:text-white hover:text-primary-600 transition-colors line-clamp-1">
                                                {{ $prod->name }}
                                            </a>
                                            <span class="text-xs text-slate-400 font-mono">SKU: {{ $prod->sku }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                                    {{ $prod->category_name ?? 'Chưa phân loại' }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-slate-800 dark:text-white">
                                    {{ number_format($prod->total_sold) }}
                                </td>
                                <td class="px-4 py-3 text-right font-black text-indigo-600 dark:text-indigo-400">
                                    {{ number_format($prod->total_revenue, 0, ',', '.') }} đ
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($prod->total_stock <= 5)
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400">
                                            Còn {{ $prod->total_stock }}
                                        </span>
                                    @else
                                        <span class="text-slate-600 dark:text-slate-400 font-medium">{{ $prod->total_stock }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                    Chưa có dữ liệu bán hàng trong khoảng thời gian này.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Content 2: Slow Moving Products -->
            <div id="tab-slow-products" class="analytics-tab-panel p-5 hidden">
                <div class="mb-4 p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-xl flex items-center gap-3 text-xs text-amber-800 dark:text-amber-300">
                    <i class="bi bi-info-circle-fill text-amber-500 text-base shrink-0"></i>
                    <span>Danh sách các sản phẩm đang có hàng tồn trong kho nhưng có ít hơn 3 lượt bán trong khoảng thời gian này. Cân nhắc chạy chương trình khuyến mãi hoặc Flash Sale để giải phóng vốn.</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                        <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-3 rounded-l-lg">Sản phẩm</th>
                                <th class="px-4 py-3">Danh mục</th>
                                <th class="px-4 py-3 text-center">Tồn kho hiện tại</th>
                                <th class="px-4 py-3 text-center">Đã bán trong kỳ</th>
                                <th class="px-4 py-3 text-center rounded-r-lg">Gợi ý hành động</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse($data['slowProducts'] as $slow)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($slow->primary_image_url)
                                            <img src="{{ $slow->primary_image_url }}" alt="{{ $slow->name }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                                                <i class="bi bi-image text-lg"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.products.edit', $slow->id) }}" class="font-semibold text-slate-800 dark:text-white hover:text-primary-600 transition-colors line-clamp-1">
                                                {{ $slow->name }}
                                            </a>
                                            <span class="text-xs text-slate-400 font-mono">SKU: {{ $slow->sku }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                                    {{ $slow->category_name }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-amber-600 dark:text-amber-400">
                                    {{ $slow->total_stock }} cái
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $slow->sold_in_period }} cái
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                        <i class="bi bi-lightning-charge"></i> Giảm giá / Flash Sale
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                    Không có sản phẩm nào thuộc diện tồn đọng chậm bán.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Content 3: Category Performance -->
            <div id="tab-categories" class="analytics-tab-panel p-5 hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                        <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-3 rounded-l-lg">Danh mục</th>
                                <th class="px-4 py-3 text-center">Số lượng bán ra</th>
                                <th class="px-4 py-3 text-right">Tổng doanh thu</th>
                                <th class="px-4 py-3 rounded-r-lg w-1/3">Tỷ trọng doanh thu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse($data['categoryStats'] as $cat)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3 font-semibold text-slate-800 dark:text-white">
                                    <i class="bi bi-folder mr-1.5 text-primary-500"></i>{{ $cat['name'] }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-slate-700 dark:text-slate-300">
                                    {{ number_format($cat['total_sold']) }} sản phẩm
                                </td>
                                <td class="px-4 py-3 text-right font-black text-indigo-600 dark:text-indigo-400">
                                    {{ number_format($cat['total_revenue'], 0, ',', '.') }} đ
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2.5 overflow-hidden">
                                            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $cat['revenue_share'] }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 min-w-10">{{ $cat['revenue_share'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-400">
                                    Chưa có dữ liệu danh mục trong khoảng thời gian này.
                                </td>
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
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
function switchAnalyticsTab(tabId) {
    document.querySelectorAll('.analytics-tab-panel').forEach(panel => {
        panel.classList.add('hidden');
    });
    document.querySelectorAll('.analytics-tab-btn').forEach(btn => {
        btn.classList.remove('text-primary-600', 'dark:text-primary-400', 'border-primary-600', 'dark:border-primary-400', 'font-bold');
        btn.classList.add('text-slate-500', 'dark:text-slate-400', 'border-transparent', 'font-medium');
    });

    const activePanel = document.getElementById(tabId);
    const activeBtn = document.getElementById('btn-' + tabId);

    if (activePanel) activePanel.classList.remove('hidden');
    if (activeBtn) {
        activeBtn.classList.add('text-primary-600', 'dark:text-primary-400', 'border-primary-600', 'dark:border-primary-400', 'font-bold');
        activeBtn.classList.remove('text-slate-500', 'dark:text-slate-400', 'border-transparent', 'font-medium');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? '#334155' : '#f1f5f9';

    // 1. Dual-Axis Mixed Chart (Revenue Bar + Order Line)
    const trendData = @json($data['trendChart']);
    
    const trendOptions = {
        series: [{
            name: 'Doanh thu (VNĐ)',
            type: 'column',
            data: trendData.revenue || []
        }, {
            name: 'Số đơn hàng',
            type: 'line',
            data: trendData.orders || []
        }],
        chart: {
            height: 320,
            type: 'line',
            toolbar: { show: false },
            fontFamily: 'inherit',
            background: 'transparent'
        },
        stroke: {
            width: [0, 3],
            curve: 'smooth'
        },
        colors: ['#6366f1', '#f59e0b'],
        dataLabels: {
            enabled: false
        },
        labels: trendData.labels || [],
        xaxis: {
            labels: {
                style: { colors: textColor, fontSize: '11px' }
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: [{
            title: {
                text: 'Doanh thu (VNĐ)',
                style: { color: '#6366f1', fontSize: '11px', fontWeight: 600 }
            },
            labels: {
                style: { colors: textColor },
                formatter: (val) => new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(val) + 'đ'
            }
        }, {
            opposite: true,
            title: {
                text: 'Số đơn hoàn tất',
                style: { color: '#f59e0b', fontSize: '11px', fontWeight: 600 }
            },
            labels: {
                style: { colors: textColor },
                formatter: (val) => Math.round(val)
            }
        }],
        grid: {
            borderColor: gridColor,
            strokeDashArray: 4
        },
        theme: {
            mode: isDark ? 'dark' : 'light'
        },
        tooltip: {
            shared: true,
            intersect: false,
            y: {
                formatter: function (y, { seriesIndex }) {
                    if (seriesIndex === 0) {
                        return new Intl.NumberFormat('vi-VN').format(y) + ' đ';
                    }
                    return y + ' đơn';
                }
            }
        },
        legend: {
            show: false
        }
    };

    const trendChart = new ApexCharts(document.querySelector("#trend-mixed-chart"), trendOptions);
    trendChart.render();

    // 2. Category Donut Chart
    const categoryData = @json($data['categoryChart']);
    const categoryOptions = {
        series: categoryData.values || [0],
        labels: categoryData.labels || ['Không có dữ liệu'],
        chart: {
            type: 'donut',
            height: 180,
            fontFamily: 'inherit',
            background: 'transparent'
        },
        colors: ['#6366f1', '#06b6d4', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6'],
        dataLabels: { enabled: false },
        legend: {
            position: 'bottom',
            fontSize: '11px',
            labels: { colors: textColor }
        },
        theme: {
            mode: isDark ? 'dark' : 'light'
        },
        tooltip: {
            y: {
                formatter: (val) => new Intl.NumberFormat('vi-VN').format(val) + ' đ'
            }
        }
    };

    const categoryChart = new ApexCharts(document.querySelector("#category-donut-chart"), categoryOptions);
    categoryChart.render();

    // 3. Customer Cohort Donut Chart
    const cohortData = @json($data['customerChart']);
    const cohortOptions = {
        series: cohortData.values || [0, 0],
        labels: cohortData.labels || ['Khách mới', 'Khách quay lại'],
        chart: {
            type: 'donut',
            height: 180,
            fontFamily: 'inherit',
            background: 'transparent'
        },
        colors: ['#3b82f6', '#10b981'],
        dataLabels: { enabled: false },
        legend: {
            position: 'bottom',
            fontSize: '11px',
            labels: { colors: textColor }
        },
        theme: {
            mode: isDark ? 'dark' : 'light'
        },
        tooltip: {
            y: {
                formatter: (val) => val + ' người mua'
            }
        }
    };

    const cohortChart = new ApexCharts(document.querySelector("#customer-cohort-chart"), cohortOptions);
    cohortChart.render();

    // 4. Dark/Light Mode Mutation Observer
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.attributeName === 'class') {
                const dark = document.documentElement.classList.contains('dark');
                const tColor = dark ? '#94a3b8' : '#64748b';
                const gColor = dark ? '#334155' : '#f1f5f9';

                trendChart.updateOptions({
                    theme: { mode: dark ? 'dark' : 'light' },
                    grid: { borderColor: gColor },
                    xaxis: { labels: { style: { colors: tColor } } },
                    yaxis: [
                        { labels: { style: { colors: tColor } } },
                        { opposite: true, labels: { style: { colors: tColor } } }
                    ]
                });

                categoryChart.updateOptions({
                    theme: { mode: dark ? 'dark' : 'light' },
                    legend: { labels: { colors: tColor } }
                });

                cohortChart.updateOptions({
                    theme: { mode: dark ? 'dark' : 'light' },
                    legend: { labels: { colors: tColor } }
                });
            }
        });
    });

    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
</script>
@endpush
