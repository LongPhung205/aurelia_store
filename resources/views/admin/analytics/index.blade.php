@extends('admin.layouts.admin')

@section('title', 'Phân tích & Báo cáo')
@section('page_title', 'Phân tích & Báo cáo Hoạt động Kinh doanh')

@section('content')
<div class="w-full min-h-[calc(100vh-90px)] p-4 md:p-6 bg-slate-50 dark:bg-slate-900/50 space-y-6">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- 0. Top Sub-Tab Navigation Bar -->
        <div class="bg-white dark:bg-slate-800 p-2 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <!-- Tab 1: Sales & KPIs -->
                <a href="{{ route('admin.analytics.index', array_merge(request()->query(), ['tab' => 'sales'])) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all {{ $activeTab === 'sales' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <i class="bi bi-graph-up"></i>
                    <span>Doanh Thu & KPIs</span>
                </a>

                <!-- Tab 2: RFM Customer Segmentation -->
                <a href="{{ route('admin.analytics.index', array_merge(request()->query(), ['tab' => 'rfm'])) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all {{ $activeTab === 'rfm' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <i class="bi bi-people"></i>
                    <span>Phân Khúc Khách Hàng (RFM)</span>
                    @if(isset($rfmData['total_customers']))
                        <span class="px-2 py-0.5 rounded-full text-[11px] {{ $activeTab === 'rfm' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">{{ $rfmData['total_customers'] }}</span>
                    @endif
                </a>

                <!-- Tab 3: Market Basket Analysis -->
                <a href="{{ route('admin.analytics.index', array_merge(request()->query(), ['tab' => 'basket'])) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all {{ $activeTab === 'basket' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/60 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <i class="bi bi-cart-check"></i>
                    <span>Khai Phá Giỏ Hàng (Apriori)</span>
                    @if(isset($basketData['rules']))
                        <span class="px-2 py-0.5 rounded-full text-[11px] {{ $activeTab === 'basket' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">{{ count($basketData['rules']) }}</span>
                    @endif
                </a>
            </div>

            <div class="text-xs text-slate-400 hidden sm:block">
                Aurelia Store Intelligence Engine v1.3
            </div>
        </div>

        @if($activeTab === 'sales')
            <!-- ========================================== -->
            <!-- SUB-TAB 1: DOANH THU & CHỈ SỐ KINH DOANH  -->
            <!-- ========================================== -->

            <!-- 1. Header Toolbar & Date Filters -->
            <div class="bg-white dark:bg-slate-800 p-4 md:p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center p-2 bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 rounded-xl">
                            <i class="bi bi-graph-up-arrow text-xl"></i>
                        </span>
                        <div>
                            <h2 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Doanh Thu & Xu Hướng Vận Hành</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Khoảng thời gian: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $data['periods']['current']['start']->format('d/m/Y') }}</span> - <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $data['periods']['current']['end']->format('d/m/Y') }}</span>
                                <span class="mx-1 text-slate-300 dark:text-slate-600">|</span>
                                Đối chuẩn kỳ trước: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $data['periods']['previous']['start']->format('d/m/Y') }}</span> - <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $data['periods']['previous']['end']->format('d/m/Y') }}</span> ({{ $data['periods']['days'] }} ngày)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action & Presets Bar -->
                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-700/60 rounded-xl text-xs font-medium">
                        <a href="{{ route('admin.analytics.index', ['tab' => 'sales', 'range' => 'today']) }}"
                           class="px-3 py-1.5 rounded-lg transition-all {{ $data['periods']['range'] === 'today' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                            Hôm nay
                        </a>
                        <a href="{{ route('admin.analytics.index', ['tab' => 'sales', 'range' => 'last_7_days']) }}"
                           class="px-3 py-1.5 rounded-lg transition-all {{ $data['periods']['range'] === 'last_7_days' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                            7 ngày qua
                        </a>
                        <a href="{{ route('admin.analytics.index', ['tab' => 'sales', 'range' => 'last_30_days']) }}"
                           class="px-3 py-1.5 rounded-lg transition-all {{ $data['periods']['range'] === 'last_30_days' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                            30 ngày qua
                        </a>
                        <a href="{{ route('admin.analytics.index', ['tab' => 'sales', 'range' => 'this_month']) }}"
                           class="px-3 py-1.5 rounded-lg transition-all {{ $data['periods']['range'] === 'this_month' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                            Tháng này
                        </a>
                        <a href="{{ route('admin.analytics.index', ['tab' => 'sales', 'range' => 'this_year']) }}"
                           class="px-3 py-1.5 rounded-lg transition-all {{ $data['periods']['range'] === 'this_year' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                            Năm nay
                        </a>
                    </div>

                    <!-- Custom Range Form -->
                    <form action="{{ route('admin.analytics.index') }}" method="GET" class="flex items-center gap-1.5">
                        <input type="hidden" name="tab" value="sales">
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

            <!-- 2. Strategic KPI Cards -->
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

                <!-- KPI 2: Completed Orders -->
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
                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Quay lại: {{ $data['kpis']['customers']['returning_rate'] }}%</span>
                    </div>
                </div>
            </div>

            <!-- 3. Charts Grid -->
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

                <!-- Side Donut Charts -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                        <div class="mb-2">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-white">Cơ Cấu Doanh Thu Theo Danh Mục</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Tỷ trọng đóng góp doanh thu</p>
                        </div>
                        <div id="category-donut-chart" class="w-full h-44 flex items-center justify-center"></div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                        <div class="mb-2">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-white">Khách Hàng Mới vs Quay Lại</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Hành vi người mua trong giai đoạn này</p>
                        </div>
                        <div id="customer-cohort-chart" class="w-full h-44 flex items-center justify-center"></div>
                    </div>
                </div>
            </div>

            <!-- 4. Deep Intelligence Multi-Tab Table -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
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

                <!-- Top 10 Best Sellers -->
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
                                            @php
                                                $topImg = $prod->thumbnail;
                                                if ($topImg && !str_starts_with($topImg, 'http://') && !str_starts_with($topImg, 'https://')) {
                                                    $topImg = asset('storage/' . ltrim($topImg, '/'));
                                                }
                                            @endphp
                                            @if($topImg)
                                                <img src="{{ $topImg }}" alt="{{ $prod->name }}" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 shrink-0\'><i class=\'bi bi-image text-lg\'></i></div>';" class="w-10 h-10 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0">
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

                <!-- Slow Moving Products -->
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
                                            @php
                                                $slowImg = $slow->thumbnail ?? $slow->primary_image_url;
                                                if ($slowImg && !str_starts_with($slowImg, 'http://') && !str_starts_with($slowImg, 'https://')) {
                                                    $slowImg = asset('storage/' . ltrim($slowImg, '/'));
                                                }
                                            @endphp
                                            @if($slowImg)
                                                <img src="{{ $slowImg }}" alt="{{ $slow->name }}" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 shrink-0\'><i class=\'bi bi-image text-lg\'></i></div>';" class="w-10 h-10 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0">
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
                                        <a href="{{ route('admin.flash-sales.index') }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border border-rose-200 dark:border-rose-800 hover:bg-rose-100 transition-colors">
                                            <i class="bi bi-lightning-charge"></i> Giảm giá / Flash Sale
                                        </a>
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

                <!-- Category Performance -->
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
                                        {{ number_format($cat['total_sold'] ?? $cat['quantity'] ?? 0) }} sản phẩm
                                    </td>
                                    <td class="px-4 py-3 text-right font-black text-indigo-600 dark:text-indigo-400">
                                        {{ number_format($cat['total_revenue'] ?? $cat['revenue'] ?? 0, 0, ',', '.') }} đ
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2.5 overflow-hidden">
                                                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $cat['revenue_share'] ?? $cat['share'] ?? 0 }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 min-w-10">{{ $cat['revenue_share'] ?? $cat['share'] ?? 0 }}%</span>
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

        @elseif($activeTab === 'rfm')
            <!-- ================================================= -->
            <!-- SUB-TAB 2: PHÂN KHÚC KHÁCH HÀNG (RFM) & HÀNH VI   -->
            <!-- ================================================= -->

            <!-- Header Description -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 flex items-center justify-center bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-2xl shrink-0">
                        <i class="bi bi-people-fill text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Phân Khúc Khách Hàng Theo Mô Hình RFM</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Chấm điểm tự động dựa trên <span class="font-bold text-slate-700 dark:text-slate-300">Recency</span> (Lần mua cuối), <span class="font-bold text-slate-700 dark:text-slate-300">Frequency</span> (Tần suất đơn), và <span class="font-bold text-slate-700 dark:text-slate-300">Monetary</span> (Tổng tiền chi tiêu).
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="px-4 py-2 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-700 text-right">
                        <span class="text-[11px] uppercase font-semibold text-slate-400 block">Tổng khách phân tích</span>
                        <span class="text-lg font-black text-slate-800 dark:text-white">{{ number_format($rfmData['total_customers'] ?? 0) }} người</span>
                    </div>
                    <div class="px-4 py-2 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-700 text-right">
                        <span class="text-[11px] uppercase font-semibold text-slate-400 block">Doanh thu tích lũy</span>
                        <span class="text-lg font-black text-indigo-600 dark:text-indigo-400">{{ number_format($rfmData['total_revenue'] ?? 0, 0, ',', '.') }} đ</span>
                    </div>
                </div>
            </div>

            <!-- 6 Cohorts Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-5">
                @php
                    $cohortIcons = [
                        'Champions' => 'bi-trophy-fill text-amber-500',
                        'Loyal Customers' => 'bi-heart-fill text-emerald-500',
                        'Potential Loyalists' => 'bi-star-fill text-blue-500',
                        'New Customers' => 'bi-person-plus-fill text-amber-500',
                        'At Risk' => 'bi-exclamation-triangle-fill text-rose-500',
                        'Lost Customers' => 'bi-person-x-fill text-slate-400',
                    ];
                @endphp

                @foreach($rfmData['cards'] ?? [] as $segName => $card)
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center gap-2 font-bold text-sm text-slate-800 dark:text-white">
                                <i class="bi {{ $cohortIcons[$segName] ?? 'bi-person' }}"></i>
                                <span>{{ $segName }}</span>
                            </span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                {{ $card['share'] }}%
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2 mb-1">
                            <span class="text-2xl font-black text-slate-800 dark:text-white">{{ number_format($card['count']) }}</span>
                            <span class="text-xs text-slate-400">khách hàng</span>
                        </div>
                        <div class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold mb-3">
                            Đóng góp: {{ number_format($card['revenue'], 0, ',', '.') }} đ
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-700/60">
                        <span class="text-[11px] block text-slate-400 mb-1 font-semibold uppercase">Hành động khuyến nghị:</span>
                        <div class="text-xs font-medium text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <i class="bi bi-arrow-right-short text-primary-500 text-base"></i>
                            <span>{{ $card['affinity']['action'] ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Middle Grid: RFM Donut & Segment ↔ Category Affinity Matrix -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Donut Chart -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                    <h4 class="text-sm font-bold text-slate-800 dark:text-white mb-1">Phân Bổ Tỷ Trọng Nhóm Khách Hàng</h4>
                    <p class="text-xs text-slate-400 mb-4">Cơ cấu 6 nhóm RFM trong hệ thống</p>
                    <div id="rfm-cohort-chart" class="w-full h-64 flex items-center justify-center"></div>
                </div>

                <!-- Segment ↔ Category Affinity Matrix Table -->
                <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="mb-4">
                        <h4 class="text-base font-bold text-slate-800 dark:text-white">Ma Trận Sở Thích Danh Mục Theo Phân Khúc</h4>
                        <p class="text-xs text-slate-400">Kết nối giữa phân khúc khách hàng và danh mục/sản phẩm được mua nhiều nhất</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                            <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400">
                                <tr>
                                    <th class="px-4 py-3 rounded-l-lg">Phân khúc</th>
                                    <th class="px-4 py-3">Danh mục ưa chuộng nhất</th>
                                    <th class="px-4 py-3">Sản phẩm mua nhiều nhất</th>
                                    <th class="px-4 py-3 text-right">Doanh thu nhóm</th>
                                    <th class="px-4 py-3 rounded-r-lg">Chiến lược đề xuất</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                @forelse($rfmData['cards'] ?? [] as $segName => $card)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-4 py-3 font-bold text-slate-800 dark:text-white whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $card['affinity']['color'] ?? '#6366f1' }}"></span>
                                            {{ $segName }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                            <i class="bi bi-folder2 mr-0.5"></i>{{ $card['affinity']['favorite_category'] ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-200 line-clamp-1 max-w-xs">
                                        {{ $card['affinity']['top_product'] ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-black text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                                        {{ number_format($card['revenue'], 0, ',', '.') }} đ
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
                                        {{ $card['affinity']['action'] ?? 'N/A' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                        Chưa có dữ liệu phân khúc khách hàng.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Customer Cohort Drill-Down Table -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-base font-bold text-slate-800 dark:text-white">Danh Sách Khách Hàng & Điểm RFM Chi Tiết</h4>
                        <p class="text-xs text-slate-400">Chi tiết điểm số R (1-5), F (1-5), M (1-5) của từng khách hàng</p>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-96">
                    <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                        <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3">Khách hàng</th>
                                <th class="px-4 py-3">Số điện thoại</th>
                                <th class="px-4 py-3 text-center">Lần mua cuối</th>
                                <th class="px-4 py-3 text-center">Số đơn (F)</th>
                                <th class="px-4 py-3 text-right">Tổng chi tiêu (M)</th>
                                <th class="px-4 py-3 text-center">Điểm R-F-M</th>
                                <th class="px-4 py-3 text-center">Phân khúc</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse($rfmData['customers'] ?? [] as $cust)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3 font-semibold text-slate-800 dark:text-white">
                                    {{ $cust['name'] }}
                                </td>
                                <td class="px-4 py-3 text-slate-500 font-mono text-xs">
                                    {{ $cust['phone'] }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ $cust['recency_days'] }} ngày trước</span>
                                    <span class="text-[11px] text-slate-400 block">({{ $cust['last_order_date'] }})</span>
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-slate-800 dark:text-white">
                                    {{ $cust['frequency'] }} đơn
                                </td>
                                <td class="px-4 py-3 text-right font-black text-indigo-600 dark:text-indigo-400">
                                    {{ number_format($cust['monetary'], 0, ',', '.') }} đ
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-bold text-xs">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200">
                                        R:{{ $cust['r_score'] }} | F:{{ $cust['f_score'] }} | M:{{ $cust['m_score'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold
                                        @if($cust['segment'] === 'Champions') bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300
                                        @elseif($cust['segment'] === 'Loyal Customers') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300
                                        @elseif($cust['segment'] === 'Potential Loyalists') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                                        @elseif($cust['segment'] === 'New Customers') bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300
                                        @elseif($cust['segment'] === 'At Risk') bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300
                                        @else bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300
                                        @endif">
                                        {{ $cust['segment'] }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                    Không có dữ liệu khách hàng giao dịch.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        @elseif($activeTab === 'basket')
            <!-- ========================================================= -->
            <!-- SUB-TAB 3: KHAI PHÁ GIỎ HÀNG (APRIORI ASSOCIATION RULES)  -->
            <!-- ========================================================= -->

            <!-- Header Description Banner -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 flex items-center justify-center bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-2xl shrink-0">
                        <i class="bi bi-cart-check-fill text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Khai Phá Giỏ Hàng & Gợi Ý Mua Kèm (Apriori)</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Phát hiện các mặt hàng thường được mua cùng nhau dựa trên 3 chỉ số thống kê: <span class="font-bold text-slate-700 dark:text-slate-300">Support</span> (Độ hỗ trợ), <span class="font-bold text-slate-700 dark:text-slate-300">Confidence</span> (Độ tin cậy) và <span class="font-bold text-slate-700 dark:text-slate-300">Lift</span> (Hệ số nâng).
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="px-4 py-2 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-700 text-right">
                        <span class="text-[11px] uppercase font-semibold text-slate-400 block">Giỏ hàng đa sản phẩm</span>
                        <span class="text-lg font-black text-slate-800 dark:text-white">{{ number_format($basketData['total_transactions'] ?? 0) }} giỏ</span>
                    </div>
                    <div class="px-4 py-2 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-700 text-right">
                        <span class="text-[11px] uppercase font-semibold text-slate-400 block">Luật kết hợp phát hiện</span>
                        <span class="text-lg font-black text-emerald-600 dark:text-emerald-400">{{ count($basketData['rules'] ?? []) }} luật</span>
                    </div>
                </div>
            </div>

            <!-- Top 3 Cross-Sell Rule Highlight Cards -->
            @if(!empty($basketData['top_rules']))
            <div>
                <h4 class="text-sm font-bold text-slate-800 dark:text-white mb-3 flex items-center gap-2">
                    <i class="bi bi-lightning-charge-fill text-amber-500"></i>
                    <span>Top Cặp Đôi Sản Phẩm Bán Kèm Mạnh Nhất (Highest Lift)</span>
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($basketData['top_rules'] as $rule)
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between">
                        <div>
                            <!-- Visual Association Pairing -->
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <!-- Antecedent Product A -->
                                <div class="flex flex-col items-center text-center w-5/12">
                                    @if($rule['antecedent']['thumbnail'])
                                        <img src="{{ $rule['antecedent']['thumbnail'] }}" alt="{{ $rule['antecedent']['name'] }}" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400\'><i class=\'bi bi-image text-xl\'></i></div>';" class="w-12 h-12 rounded-xl object-cover border border-slate-200 dark:border-slate-700 mb-1.5 shadow-sm">
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 mb-1.5">
                                            <i class="bi bi-image text-xl"></i>
                                        </div>
                                    @endif
                                    <span class="text-xs font-bold text-slate-800 dark:text-white line-clamp-1" title="{{ $rule['antecedent']['name'] }}">{{ $rule['antecedent']['name'] }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $rule['antecedent']['sku'] }}</span>
                                </div>

                                <!-- Arrow & Lift Indicator -->
                                <div class="flex flex-col items-center w-2/12 shrink-0">
                                    <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800 mb-1">
                                        {{ $rule['lift'] }}x
                                    </span>
                                    <i class="bi bi-arrow-right text-slate-400 text-lg"></i>
                                </div>

                                <!-- Consequent Product B -->
                                <div class="flex flex-col items-center text-center w-5/12">
                                    @if($rule['consequent']['thumbnail'])
                                        <img src="{{ $rule['consequent']['thumbnail'] }}" alt="{{ $rule['consequent']['name'] }}" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400\'><i class=\'bi bi-image text-xl\'></i></div>';" class="w-12 h-12 rounded-xl object-cover border border-slate-200 dark:border-slate-700 mb-1.5 shadow-sm">
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 mb-1.5">
                                            <i class="bi bi-image text-xl"></i>
                                        </div>
                                    @endif
                                    <span class="text-xs font-bold text-slate-800 dark:text-white line-clamp-1" title="{{ $rule['consequent']['name'] }}">{{ $rule['consequent']['name'] }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $rule['consequent']['sku'] }}</span>
                                </div>
                            </div>

                            <!-- Stat Metrics -->
                            <div class="grid grid-cols-2 gap-2 text-center p-2.5 bg-slate-50 dark:bg-slate-700/40 rounded-xl mb-3">
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase font-semibold block">Độ tin cậy</span>
                                    <span class="text-sm font-black text-slate-800 dark:text-white">{{ $rule['confidence'] }}%</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase font-semibold block">Đã mua chung</span>
                                    <span class="text-sm font-black text-slate-800 dark:text-white">{{ $rule['co_count'] }} đơn</span>
                                </div>
                            </div>
                        </div>

                        <!-- CTA Button -->
                        <a href="{{ route('admin.flash-sales.index') }}" class="w-full py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold text-center transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                            <i class="bi bi-plus-circle"></i>
                            <span>Tạo Khuyến Mãi Combo</span>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Association Rules Data Table -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-base font-bold text-slate-800 dark:text-white">Bảng Toàn Bộ Luật Kết Hợp Khai Phá Được</h4>
                        <p class="text-xs text-slate-400">Các quy luật thỏa mãn điều kiện độ tin cậy và có độ nâng Lift > 1.05</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                        <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-3 rounded-l-lg">Sản phẩm chính (A)</th>
                                <th class="px-4 py-3">Sản phẩm mua kèm (B)</th>
                                <th class="px-4 py-3 text-center">Mua chung</th>
                                <th class="px-4 py-3 text-center">Độ hỗ trợ (Support)</th>
                                <th class="px-4 py-3 text-center">Độ tin cậy (Confidence)</th>
                                <th class="px-4 py-3 text-center">Hệ số nâng (Lift)</th>
                                <th class="px-4 py-3 text-right rounded-r-lg">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse($basketData['rules'] ?? [] as $rule)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($rule['antecedent']['thumbnail'])
                                            <img src="{{ $rule['antecedent']['thumbnail'] }}" alt="{{ $rule['antecedent']['name'] }}" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 shrink-0\'><i class=\'bi bi-image text-lg\'></i></div>';" class="w-10 h-10 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                                                <i class="bi bi-image text-lg"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.products.edit', $rule['antecedent']['id']) }}" class="font-semibold text-slate-800 dark:text-white hover:text-primary-600 transition-colors line-clamp-1">
                                                {{ $rule['antecedent']['name'] }}
                                            </a>
                                            <span class="text-xs text-slate-400 font-mono">SKU: {{ $rule['antecedent']['sku'] }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($rule['consequent']['thumbnail'])
                                            <img src="{{ $rule['consequent']['thumbnail'] }}" alt="{{ $rule['consequent']['name'] }}" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 shrink-0\'><i class=\'bi bi-image text-lg\'></i></div>';" class="w-10 h-10 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                                                <i class="bi bi-image text-lg"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.products.edit', $rule['consequent']['id']) }}" class="font-semibold text-slate-800 dark:text-white hover:text-primary-600 transition-colors line-clamp-1">
                                                {{ $rule['consequent']['name'] }}
                                            </a>
                                            <span class="text-xs text-slate-400 font-mono">SKU: {{ $rule['consequent']['sku'] }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-center font-bold text-slate-800 dark:text-white">
                                    {{ $rule['co_count'] }} đơn
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $rule['support'] }}%</span>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                        {{ $rule['confidence'] }}%
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        {{ $rule['lift'] }}x
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.flash-sales.index') }}" class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-lg text-xs font-bold hover:bg-emerald-100 transition-colors">
                                        <i class="bi bi-plus-circle"></i>
                                        <span>Tạo Combo</span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                    Chưa có đơn hàng nào chứa từ 2 sản phẩm trở lên để khai phá giỏ hàng.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        @endif

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

    @if($activeTab === 'sales')
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
            dataLabels: { enabled: false },
            labels: trendData.labels || [],
            xaxis: {
                labels: { style: { colors: textColor, fontSize: '11px' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: [{
                title: { text: 'Doanh thu (VNĐ)', style: { color: '#6366f1', fontSize: '11px', fontWeight: 600 } },
                labels: {
                    style: { colors: textColor },
                    formatter: (val) => new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(val) + 'đ'
                }
            }, {
                opposite: true,
                title: { text: 'Số đơn hoàn tất', style: { color: '#f59e0b', fontSize: '11px', fontWeight: 600 } },
                labels: { style: { colors: textColor }, formatter: (val) => Math.round(val) }
            }],
            grid: { borderColor: gridColor, strokeDashArray: 4 },
            theme: { mode: isDark ? 'dark' : 'light' },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function (y, { seriesIndex }) {
                        if (seriesIndex === 0) return new Intl.NumberFormat('vi-VN').format(y) + ' đ';
                        return y + ' đơn';
                    }
                }
            },
            legend: { show: false }
        };
        const trendChart = new ApexCharts(document.querySelector("#trend-mixed-chart"), trendOptions);
        trendChart.render();

        // 2. Category Donut Chart
        const categoryData = @json($data['categoryChart']);
        const categoryOptions = {
            series: categoryData.values || [0],
            labels: categoryData.labels || ['Không có dữ liệu'],
            chart: { type: 'donut', height: 180, fontFamily: 'inherit', background: 'transparent' },
            colors: ['#6366f1', '#06b6d4', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6'],
            dataLabels: { enabled: false },
            legend: { position: 'bottom', fontSize: '11px', labels: { colors: textColor } },
            theme: { mode: isDark ? 'dark' : 'light' },
            tooltip: { y: { formatter: (val) => new Intl.NumberFormat('vi-VN').format(val) + ' đ' } }
        };
        const categoryChart = new ApexCharts(document.querySelector("#category-donut-chart"), categoryOptions);
        categoryChart.render();

        // 3. Customer Cohort Donut Chart
        const cohortData = @json($data['customerChart']);
        const cohortOptions = {
            series: cohortData.values || [0, 0],
            labels: cohortData.labels || ['Khách mới', 'Khách quay lại'],
            chart: { type: 'donut', height: 180, fontFamily: 'inherit', background: 'transparent' },
            colors: ['#3b82f6', '#10b981'],
            dataLabels: { enabled: false },
            legend: { position: 'bottom', fontSize: '11px', labels: { colors: textColor } },
            theme: { mode: isDark ? 'dark' : 'light' },
            tooltip: { y: { formatter: (val) => val + ' người mua' } }
        };
        const cohortChart = new ApexCharts(document.querySelector("#customer-cohort-chart"), cohortOptions);
        cohortChart.render();

    @elseif($activeTab === 'rfm')
        // 4. RFM Cohort Donut Chart
        const rfmChartData = @json($rfmData['chart'] ?? []);
        const rfmOptions = {
            series: rfmChartData.values || [0],
            labels: rfmChartData.labels || [],
            colors: rfmChartData.colors || ['#6366f1', '#10b981', '#3b82f6', '#f59e0b', '#f43f5e', '#64748b'],
            chart: { type: 'donut', height: 250, fontFamily: 'inherit', background: 'transparent' },
            dataLabels: { enabled: false },
            legend: { position: 'bottom', fontSize: '11px', labels: { colors: textColor } },
            theme: { mode: isDark ? 'dark' : 'light' },
            tooltip: { y: { formatter: (val) => val + ' khách hàng' } }
        };
        const rfmChart = new ApexCharts(document.querySelector("#rfm-cohort-chart"), rfmOptions);
        rfmChart.render();
    @endif

    // Global Dark/Light Mode Mutation Observer
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.attributeName === 'class') {
                const dark = document.documentElement.classList.contains('dark');
                const tColor = dark ? '#94a3b8' : '#64748b';
                const gColor = dark ? '#334155' : '#f1f5f9';

                @if($activeTab === 'sales')
                    if (typeof trendChart !== 'undefined') {
                        trendChart.updateOptions({
                            theme: { mode: dark ? 'dark' : 'light' },
                            grid: { borderColor: gColor },
                            xaxis: { labels: { style: { colors: tColor } } },
                            yaxis: [
                                { labels: { style: { colors: tColor } } },
                                { opposite: true, labels: { style: { colors: tColor } } }
                            ]
                        });
                    }
                    if (typeof categoryChart !== 'undefined') {
                        categoryChart.updateOptions({ theme: { mode: dark ? 'dark' : 'light' }, legend: { labels: { colors: tColor } } });
                    }
                    if (typeof cohortChart !== 'undefined') {
                        cohortChart.updateOptions({ theme: { mode: dark ? 'dark' : 'light' }, legend: { labels: { colors: tColor } } });
                    }
                @elseif($activeTab === 'rfm')
                    if (typeof rfmChart !== 'undefined') {
                        rfmChart.updateOptions({ theme: { mode: dark ? 'dark' : 'light' }, legend: { labels: { colors: tColor } } });
                    }
                @endif
            }
        });
    });

    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
</script>
@endpush
