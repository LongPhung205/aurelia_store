@extends('admin.layouts.admin')

@section('title', 'Chi tiết Đơn hàng')
@section('page_title', 'Chi tiết Đơn hàng: ORD-'.$order->id)

@push('styles')
<style>
    @media print {
        /* Giấu tất cả UI web khi in */
        aside, nav, header { display: none !important; }
        .web-ui-container { display: none !important; }
        
        /* Hiển thị duy nhất tem giao hàng */
        #print-label { 
            display: block !important; 
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            margin: 0 !important;
        }

        /* Cấu hình trang in A6 hoặc khổ giấy in nhiệt (10x15cm) */
        @page { size: 100mm 150mm; margin: 0; }
        body, html { margin: 0 !important; padding: 0 !important; background: white !important; }
    }
</style>
@endpush

@section('content')
@inject('ghn', 'App\Services\GhnService')
@php
    $statusColors = [
        'pending' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        'processing' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
        'ready_to_pick' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
        'shipping' => 'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400',
        'completed' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        'cancelled' => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
    ];
    $statusLabels = [
        'pending' => 'Chờ xác nhận',
        'processing' => 'Đã xác nhận',
        'ready_to_pick' => 'Đã chuẩn bị hàng',
        'shipping' => 'Đang giao hàng',
        'completed' => 'Đã giao',
        'cancelled' => 'Đã hủy',
    ];
    $badgeClass = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800';

    // Resolve address from GHN
    $provinceName = '';
    $districtName = '';
    $wardName = '';
    if ($order->province_id) {
        $p = collect($ghn->getProvinces())->firstWhere('ProvinceID', $order->province_id);
        $provinceName = $p ? $p['ProvinceName'] : '';
    }
    if ($order->district_id) {
        $d = collect($ghn->getDistricts($order->province_id))->firstWhere('DistrictID', $order->district_id);
        $districtName = $d ? $d['DistrictName'] : '';
    }
    if ($order->ward_code) {
        $w = collect($ghn->getWards($order->district_id))->firstWhere('WardCode', $order->ward_code);
        $wardName = $w ? $w['WardName'] : '';
    }
    $ghnAddressStr = collect([$wardName, $districtName, $provinceName])->filter()->implode(', ');
@endphp

<div class="web-ui-container px-4 md:px-6 py-4 w-full h-[calc(100vh-64px)] flex flex-col bg-gray-50 dark:bg-[#050505] overflow-y-auto custom-scrollbar">

    <!-- HEADER BLOCK -->
    <div class="bg-white dark:bg-[#0a0a0a] rounded-[1.5rem] p-5 shadow-sm ring-1 ring-black/5 dark:ring-white/10 mb-4 shrink-0">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white uppercase font-mono">Đơn hàng #ORD-{{ $order->id }}</h1>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $badgeClass }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current {{ in_array($order->status, ['processing', 'shipping']) ? 'animate-pulse' : '' }}"></span>
                        {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                    </span>
                    <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300 uppercase tracking-wider border border-gray-200 dark:border-white/10">B2C Retail</span>
                </div>
                
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-[11px] font-medium text-gray-500 dark:text-gray-400">
                    <div class="flex items-center gap-1.5"><i class="bi bi-clock"></i> Tạo lúc: {{ $order->created_at->format('H:i:s - d/m/Y') }}</div>
                    <div class="hidden sm:block w-px h-3 bg-gray-300 dark:bg-white/10"></div>
                    <div class="flex items-center gap-1.5"><i class="bi bi-globe"></i> Kênh bán: Website (Aurelia)</div>
                    @if($order->shipping_order_code)
                        <div class="hidden sm:block w-px h-3 bg-gray-300 dark:bg-white/10"></div>
                        <div class="flex items-center gap-1.5 text-primary-600 dark:text-primary-400 font-bold"><i class="bi bi-truck"></i> GHN: {{ $order->shipping_order_code }}</div>
                    @endif
                </div>
            </div>
            
            <div class="flex gap-2">
                <button onclick="window.print()" class="px-4 py-2 rounded-xl font-bold text-xs bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:text-primary-600 focus:ring-4 focus:ring-primary-50 dark:bg-[#111] dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/5 transition-all flex items-center gap-2 shadow-sm">
                    <i class="bi bi-printer text-primary-500"></i> In đơn
                </button>
                <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 rounded-xl font-bold text-xs bg-primary-50 text-primary-600 hover:bg-primary-100 focus:ring-4 focus:ring-primary-50 dark:bg-primary-500/10 dark:text-primary-400 dark:hover:bg-primary-500/20 transition-all flex items-center gap-2 shadow-sm border border-primary-100 dark:border-primary-500/20">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <!-- ACTION BAR (Điều khiển luồng trạng thái) -->
    @if($order->status != 'cancelled' && $order->status != 'completed')
    <div class="print-hide bg-blue-50/50 dark:bg-blue-900/10 rounded-[1.5rem] p-4 md:p-5 shadow-sm ring-1 ring-blue-100 dark:ring-blue-900/30 mb-4 flex flex-col md:flex-row items-center justify-between gap-4 shrink-0">
        <div class="flex items-center gap-4 w-full md:w-auto">
            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400 flex items-center justify-center shrink-0">
                <i class="bi bi-sliders2 text-lg"></i>
            </div>
            <div>
                <h4 class="font-bold text-gray-900 dark:text-white text-sm">Điều khiển luồng trạng thái</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Đơn hàng đang ở trạng thái <strong>{{ $statusLabels[$order->status] }}</strong>. Chọn hành động tiếp:</p>
            </div>
        </div>
        
        <div class="flex flex-wrap gap-2 w-full md:w-auto">
            @if($order->status == 'pending')
                <form action="{{ route('admin.orders.update_status', $order) }}" method="POST" class="m-0 flex-1 md:flex-none">
                    @csrf
                    <input type="hidden" name="status" value="processing">
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl font-bold text-xs bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-md flex items-center justify-center gap-2">
                        <i class="bi bi-check-circle"></i> Xác nhận đơn
                    </button>
                </form>
            @elseif($order->status == 'processing')
                <form action="{{ route('admin.orders.update_status', $order) }}" method="POST" class="m-0 flex-1 md:flex-none">
                    @csrf
                    <input type="hidden" name="status" value="ready_to_pick">
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl font-bold text-xs bg-indigo-600 text-white hover:bg-indigo-700 transition-all shadow-md flex items-center justify-center gap-2">
                        <i class="bi bi-box-seam"></i> Đã đóng gói (Chờ lấy)
                    </button>
                </form>
            @elseif($order->status == 'ready_to_pick')
                <form action="{{ route('admin.orders.update_status', $order) }}" method="POST" class="m-0 flex-1 md:flex-none">
                    @csrf
                    <input type="hidden" name="status" value="shipping">
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl font-bold text-xs bg-purple-600 text-white hover:bg-purple-700 transition-all shadow-md flex items-center justify-center gap-2">
                        <i class="bi bi-truck"></i> Bắt đầu giao hàng
                    </button>
                </form>
            @elseif($order->status == 'shipping')
                <form action="{{ route('admin.orders.update_status', $order) }}" method="POST" class="m-0 flex-1 md:flex-none">
                    @csrf
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl font-bold text-xs bg-emerald-600 text-white hover:bg-emerald-700 transition-all shadow-md flex items-center justify-center gap-2">
                        <i class="bi bi-check-all"></i> Xác nhận Giao thành công
                    </button>
                </form>
            @endif

            <form action="{{ route('admin.orders.update_status', $order) }}" method="POST" class="form-delete m-0 flex-none" data-confirm-title="Hủy đơn hàng?" data-confirm-text="Xác nhận hủy đơn hàng này?">
                @csrf
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl font-bold text-xs bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 dark:bg-[#111] dark:border-rose-500/20 dark:text-rose-400 dark:hover:bg-rose-500/10 transition-all flex items-center justify-center gap-2 shadow-sm">
                    <i class="bi bi-x-octagon"></i> Hủy đơn
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- HORIZONTAL TIMELINE -->
    <div class="print-hide bg-white dark:bg-[#0a0a0a] rounded-[1.5rem] p-5 shadow-sm ring-1 ring-black/5 dark:ring-white/10 mb-4 shrink-0">
        <div class="flex items-center gap-2 mb-8">
            <div class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 flex items-center justify-center shrink-0">
                <i class="bi bi-signpost-split text-[10px]"></i>
            </div>
            <h3 class="font-bold text-gray-900 dark:text-white text-sm">Tiến trình Xử lý & Vận chuyển</h3>
        </div>

        @php
            $currentStep = 1;
            if ($order->status == 'cancelled') {
                $currentStep = -1;
            } elseif ($order->status == 'completed' || $order->shipping_status == 'delivered') {
                $currentStep = 4;
            } elseif ($order->status == 'shipping' || in_array($order->shipping_status, ['delivering', 'picking'])) {
                $currentStep = 3;
            } elseif ($order->status == 'ready_to_pick' || $order->shipping_status == 'ready_to_pick') {
                $currentStep = 2;
            } elseif ($order->status == 'processing') {
                $currentStep = 2;
            }
        @endphp

        @if($currentStep === -1)
            <div class="flex items-center gap-4 p-4 rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 border border-rose-100 dark:border-rose-500/20">
                <i class="bi bi-x-circle-fill text-2xl"></i>
                <div>
                    <div class="font-bold">Đơn hàng đã bị hủy</div>
                    <div class="text-xs mt-1">Lúc: {{ $order->updated_at->format('H:i d/m/Y') }}</div>
                </div>
            </div>
        @else
            <div class="relative w-full pb-2">
                <!-- Background Line -->
                <div class="absolute top-5 left-[12.5%] right-[12.5%] h-1 bg-gray-100 dark:bg-white/5 rounded-full z-0"></div>
                <!-- Progress Line -->
                <div class="absolute top-5 left-[12.5%] h-1 bg-emerald-500 rounded-full z-0 transition-all duration-500" 
                     style="width: {{ ($currentStep - 1) * 25 }}%"></div>
                
                <div class="relative z-10 flex justify-between">
                    <!-- Step 1 -->
                    <div class="flex flex-col items-center w-1/4 px-1">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center ring-4 ring-white dark:ring-[#0a0a0a] shadow-sm mb-3 {{ $currentStep >= 1 ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-gray-800 text-gray-400' }}">
                            <i class="bi bi-check-lg text-xl"></i>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-[11px] uppercase tracking-wide {{ $currentStep >= 1 ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">1. Đặt hàng</div>
                            <div class="text-[10px] font-medium text-gray-500 mt-1">{{ $order->created_at->format('H:i d/m') }}</div>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="flex flex-col items-center w-1/4 px-1">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center ring-4 ring-white dark:ring-[#0a0a0a] shadow-sm mb-3 {{ $currentStep >= 2 ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-gray-800 text-gray-400' }}">
                            <i class="bi bi-check-lg text-xl"></i>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-[11px] uppercase tracking-wide {{ $currentStep >= 2 ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">2. Đóng gói</div>
                            <div class="text-[10px] font-medium text-gray-500 mt-1">{{ $currentStep >= 2 ? $order->updated_at->format('H:i d/m') : 'Chờ xử lý' }}</div>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="flex flex-col items-center w-1/4 px-1">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center ring-4 ring-white dark:ring-[#0a0a0a] shadow-sm mb-3 {{ $currentStep >= 3 ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-800 text-gray-400' }}">
                            @if($currentStep == 3)
                                <i class="bi bi-truck text-lg animate-bounce"></i>
                            @else
                                <i class="bi bi-truck text-lg"></i>
                            @endif
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-[11px] uppercase tracking-wide {{ $currentStep >= 3 ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">3. Vận chuyển</div>
                            <div class="text-[10px] font-medium text-gray-500 mt-1">{{ $currentStep >= 3 ? 'Đang giao hàng' : 'Chờ lấy hàng' }}</div>
                        </div>
                    </div>
                    <!-- Step 4 -->
                    <div class="flex flex-col items-center w-1/4 px-1">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center ring-4 ring-white dark:ring-[#0a0a0a] shadow-sm mb-3 {{ $currentStep >= 4 ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-gray-800 text-gray-400' }}">
                            <i class="bi bi-check-all text-xl"></i>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-[11px] uppercase tracking-wide {{ $currentStep >= 4 ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">4. Hoàn thành</div>
                            <div class="text-[10px] font-medium text-gray-500 mt-1">{{ $currentStep >= 4 ? $order->updated_at->format('H:i d/m') : 'Dự kiến' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 text-sm font-semibold text-emerald-800 rounded-2xl bg-emerald-50/50 border border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400 flex items-center gap-3 shrink-0">
            <i class="bi bi-check-circle-fill text-lg"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 text-sm font-semibold text-rose-800 rounded-2xl bg-rose-50/50 border border-rose-100 dark:bg-rose-500/10 dark:border-rose-500/20 dark:text-rose-400 flex items-center gap-3 shrink-0">
            <i class="bi bi-exclamation-octagon-fill text-lg"></i> {{ session('error') }}
        </div>
    @endif

    <!-- MAIN GRID (Invoice + Sidebar) -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 shrink-0 pb-6">
        
        <!-- INVOICE SECTION (Left 2/3) -->
        <div class="xl:col-span-2">
            <div class="invoice-print-area bg-white dark:bg-[#0a0a0a] rounded-[1.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-none ring-1 ring-black/5 dark:ring-white/10 p-6 md:p-8 relative overflow-hidden h-full flex flex-col">
                <!-- Watermark -->
                <div class="absolute -top-10 -right-10 opacity-[0.03] dark:opacity-5 pointer-events-none">
                    <i class="bi bi-receipt text-[15rem]"></i>
                </div>
                
                <!-- Invoice Header -->
                <div class="flex flex-col md:flex-row justify-between items-start gap-6 border-b border-gray-100 dark:border-white/5 pb-6 mb-6 relative z-10">
                    <div class="flex gap-4">

                        <div>
                            <h2 class="font-bold text-gray-900 dark:text-white text-lg">Aurelia Fashion</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cửa hàng Thời trang & Phụ kiện cao cấp</p>
                            <p class="text-[10px] font-medium text-gray-400 mt-1">Hotline: 1900 6969 - Website: aurelia.vn</p>
                        </div>
                    </div>
                    <div class="text-left md:text-right">
                        <div class="inline-block px-3 py-1.5 bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 text-[10px] font-bold uppercase tracking-widest rounded-md mb-2 border border-blue-100 dark:border-blue-500/20">Hóa đơn điện tử</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Số hóa đơn: <strong class="text-gray-900 dark:text-white font-mono text-sm tracking-wider">HD-{{ date('Y') }}-{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</strong></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Ngày lập: {{ $order->created_at->format('d/m/Y') }}</div>
                    </div>
                </div>

                <!-- Info Blocks (Customer + Payment side by side) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-8 relative z-10">
                    <!-- Customer -->
                    <div class="bg-blue-50/50 dark:bg-blue-900/10 p-5 rounded-2xl ring-1 ring-blue-100/50 dark:ring-blue-900/30">
                        <div class="text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                            <i class="bi bi-person"></i> Thông tin khách hàng
                        </div>
                        <div class="font-bold text-gray-900 dark:text-white text-base mb-1">{{ $order->customer_name }}</div>
                        <div class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-3"><i class="bi bi-telephone text-[10px] mr-1"></i> {{ $order->customer_phone }}</div>
                        <div class="text-xs font-medium text-gray-600 dark:text-gray-400 leading-relaxed bg-white dark:bg-[#111] p-3 rounded-xl ring-1 ring-black/5 dark:ring-white/5 shadow-sm">
                            @if($ghnAddressStr)
                                <div class="text-gray-900 dark:text-white font-bold mb-1">{{ $ghnAddressStr }}</div>
                            @endif
                            {{ $order->address }}
                        </div>
                        @if($order->note)
                            <div class="mt-3 text-[11px] text-amber-600 dark:text-amber-400 font-medium italic border-l-2 border-amber-400 pl-2">
                                "{{ $order->note }}"
                            </div>
                        @endif
                    </div>
                    
                    <!-- Payment -->
                    <div class="bg-emerald-50/50 dark:bg-emerald-900/10 p-5 rounded-2xl ring-1 ring-emerald-100/50 dark:ring-emerald-900/30 flex flex-col">
                        <div class="text-[10px] font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                            <i class="bi bi-credit-card"></i> Phương thức & Quyết toán
                        </div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-gray-900 dark:text-white uppercase">{{ $order->payment_method }}</span>
                            @if($order->payment_status == 'paid')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30">KHỚP 100%</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30">CHƯA KHỚP</span>
                            @endif
                        </div>
                        @if($order->payment_method == 'banking')
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Cổng thanh toán: <span class="font-bold text-gray-700 dark:text-gray-300">PayOS (Napas 247)</span></div>
                        @elseif($order->payment_method == 'cod')
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Loại hình: <span class="font-bold text-gray-700 dark:text-gray-300">Thanh toán khi nhận hàng (COD)</span></div>
                        @endif
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-auto pt-4 border-t border-gray-200 dark:border-white/5">
                            Thời gian quyết toán: {{ $order->created_at->format('H:i:s d/m/Y') }}
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="mb-6 relative z-10 flex-1">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center justify-between">
                        <span>Danh mục sản phẩm xuất bán</span>
                        <span class="normal-case font-medium text-gray-500">{{ $order->items->sum('quantity') }} sản phẩm</span>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap min-w-[500px]">
                            <thead class="text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-white/10">
                                <tr>
                                    <th class="pb-3 pl-2 w-10">STT</th>
                                    <th class="pb-3">Sản phẩm & Mã SKU</th>
                                    <th class="pb-3 text-center">SL</th>
                                    <th class="pb-3 text-right">Đơn giá</th>
                                    <th class="pb-3 text-right pr-2">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                                @foreach($order->items as $index => $item)
                                    @php
                                        $variant = $item->productVariant;
                                        $imgUrl = $variant->thumbnail_url ?? ($variant->product->primary_image_url ?? 'https://via.placeholder.com/150');
                                        if ($imgUrl && !Str::startsWith($imgUrl, ['http://', 'https://'])) {
                                            $imgUrl = Storage::url($imgUrl);
                                        }
                                    @endphp
                                    <tr class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="py-4 pl-2 text-xs font-mono text-gray-400">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                        <td class="py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0 ring-1 ring-black/5 dark:ring-white/10 bg-white">
                                                    <img src="{{ $imgUrl }}" class="w-full h-full object-cover">
                                                </div>
                                                <div>
                                                    <div class="font-bold text-gray-900 dark:text-white max-w-[200px] md:max-w-[300px] truncate" title="{{ $item->product_name }}">{{ $item->product_name }}</div>
                                                    <div class="text-[10px] font-medium text-gray-500 uppercase mt-0.5">{{ $item->variant_attributes ?: 'Mặc định' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 text-center font-bold text-gray-900 dark:text-white">{{ $item->quantity }}</td>
                                        <td class="py-4 text-right font-mono text-xs">{{ number_format($item->price, 0, ',', '.') }} đ</td>
                                        <td class="py-4 text-right pr-2 font-mono font-bold text-gray-900 dark:text-white">{{ number_format($item->total, 0, ',', '.') }} đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Financial Totals -->
                <div class="flex flex-col md:flex-row justify-between items-end gap-6 border-t border-gray-100 dark:border-white/10 pt-6 relative z-10 mt-auto">

                    
                    <div class="w-full md:w-80 space-y-2.5">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Tạm tính tiền hàng:</span>
                            <span class="font-mono text-gray-900 dark:text-white font-medium">{{ number_format($order->subtotal, 0, ',', '.') }} đ</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Phí vận chuyển:</span>
                            <span class="font-mono text-gray-900 dark:text-white font-medium">+{{ number_format($order->shipping_fee, 0, ',', '.') }} đ</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400"><i class="bi bi-tag"></i> Giảm giá:</span>
                            <span class="font-mono text-emerald-600 dark:text-emerald-400 font-medium">-0 đ</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Thuế GTGT (VAT 8%):</span>
                            <span class="font-mono text-gray-900 dark:text-white font-medium">+0 đ</span>
                        </div>
                        
                        <div class="bg-gradient-to-r from-primary-600 to-indigo-600 text-white p-5 rounded-2xl shadow-lg mt-4 flex items-center justify-between shadow-primary-500/20">
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-widest opacity-90">Tổng thanh toán</div>
                                <div class="text-[9px] opacity-75 mt-0.5">Đã gồm VAT & Chiết khấu</div>
                            </div>
                            <div class="text-2xl font-black font-mono tracking-tight">{{ number_format($order->total_amount, 0, ',', '.') }} đ</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- RIGHT SIDEBAR (1/3) -->
        <div class="print-hide space-y-6 h-full flex flex-col">
            
            <!-- GHN Shipping Panel -->
            <div class="bg-white dark:bg-[#0a0a0a] rounded-[1.5rem] p-6 shadow-sm ring-1 ring-black/5 dark:ring-white/10 flex-1">
                <div class="flex items-center justify-between mb-5 border-b border-gray-100 dark:border-white/5 pb-4">
                    <div class="flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                        <i class="bi bi-truck text-primary-500"></i> Vận chuyển & Giao nhận
                    </div>
                    <span class="px-2 py-1 bg-blue-50 dark:bg-blue-500/10 rounded text-[9px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest border border-blue-100 dark:border-blue-500/20">GHN Express</span>
                </div>
                
                @if($order->shipping_order_code)
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Đơn vị:</span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">Giao Hàng Nhanh</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Mã vận đơn:</span>
                            <span class="text-sm font-black font-mono text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-500/10 px-2.5 py-1 rounded-md border border-primary-100 dark:border-primary-500/20 tracking-wider">
                                {{ $order->shipping_order_code }}
                            </span>
                        </div>
                        
                        <div class="bg-emerald-50/50 dark:bg-emerald-500/5 p-4 rounded-xl ring-1 ring-emerald-100 dark:ring-emerald-500/20 mt-4">
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-emerald-800 dark:text-emerald-500 uppercase tracking-widest mb-1">Trạng thái từ GHN</div>
                                    <div class="text-xs font-medium text-emerald-700 dark:text-emerald-400 leading-relaxed">
                                        {{ $order->shipping_status ?? 'Đang xử lý...' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('admin.orders.sync_ghn', $order) }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 rounded-xl font-bold text-xs bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 dark:bg-[#111] dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/5 transition-all flex items-center justify-center gap-2 shadow-sm">
                                <i class="bi bi-arrow-clockwise"></i> Tra cứu hành trình trên GHN
                            </button>
                        </form>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-50 dark:bg-[#111] rounded-full flex items-center justify-center mx-auto mb-4 ring-1 ring-black/5 dark:ring-white/5">
                            <i class="bi bi-box-seam text-gray-400 text-2xl"></i>
                        </div>
                        <div class="text-sm font-bold text-gray-900 dark:text-white mb-1">Chưa tạo mã vận đơn</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-6 px-4">Đơn hàng này chưa được đẩy thông tin sang hệ thống Giao Hàng Nhanh.</div>
                        
                        @if(in_array($order->status, ['pending', 'processing', 'ready_to_pick']))
                        <form action="{{ route('admin.orders.confirm_ghn', $order) }}" method="POST" class="form-confirm" data-confirm-title="Tạo đơn GHN?" data-confirm-text="Xác nhận đẩy đơn này sang Giao Hàng Nhanh?">
                            @csrf
                            <button type="submit" class="w-full px-4 py-3 rounded-xl font-bold text-xs bg-primary-600 text-white hover:bg-primary-700 transition-all shadow-md flex items-center justify-center gap-2">
                                <i class="bi bi-send-check"></i> Tạo vận đơn GHN ngay
                            </button>
                        </form>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Manual Status Fallback -->
            <div class="bg-white dark:bg-[#0a0a0a] rounded-[1.5rem] p-6 shadow-sm ring-1 ring-black/5 dark:ring-white/10">
                <div class="flex items-center gap-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">
                    <i class="bi bi-gear"></i> Cập nhật trạng thái thủ công
                </div>
                <form action="{{ route('admin.orders.update_status', $order) }}" method="POST" class="form-confirm" data-confirm-title="Cập nhật trạng thái?" data-confirm-text="Xác nhận lưu thay đổi trạng thái cho đơn hàng này?">
                    @csrf
                    <div class="flex gap-2">
                        <select name="status" class="flex-1 rounded-xl border-0 ring-1 ring-inset ring-gray-200 bg-white dark:bg-[#111] dark:ring-white/10 text-gray-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-primary-500 py-2.5 shadow-sm cursor-pointer transition-shadow">
                            @php
                                $adminStatuses = ['pending', 'processing', 'ready_to_pick', 'shipping', 'completed', 'cancelled'];
                            @endphp
                            @foreach($adminStatuses as $val)
                                <option value="{{ $val }}" {{ $order->status == $val ? 'selected' : '' }}>
                                    {{ $statusLabels[$val] }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-primary-600 text-white font-bold text-xs hover:bg-primary-700 transition-colors shadow-sm">
                            Lưu
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- TEM GIAO HÀNG (Chỉ hiển thị khi in) -->
<div id="print-label" class="hidden print:block w-[100mm] bg-white text-black p-3 font-sans border-2 border-black mx-auto mt-4" style="page-break-after: always; width: 100mm; min-height: 148mm; box-sizing: border-box;">
    <!-- Phần 1: Header / Logo GHN -->
    <div class="flex border-b-2 border-black pb-2 mb-2 items-center">
        <div class="w-1/2 border-r-2 border-black pr-2">
            <h1 class="font-black text-lg leading-none mb-1 tracking-tighter uppercase">AURELIA</h1>
            <p class="text-[10px] font-bold mb-0.5">Hotline: 1900 6969</p>
            <p class="text-[9px] font-medium leading-tight">Đại Học Tài Nguyên và Môi Trường Hà Nội</p>
        </div>
        <div class="w-1/2 pl-2 text-center flex flex-col justify-center items-center">
            <div class="font-black text-xl border-2 border-black px-2 py-0.5 inline-block mb-1 uppercase tracking-widest">
                GHN
            </div>
            @if($order->shipping_order_code)
               <p class="text-sm font-black font-mono tracking-widest">{{$order->shipping_order_code}}</p>
            @else
               <p class="text-[10px] font-bold italic">Chưa tạo vận đơn</p>
            @endif
        </div>
    </div>
    
    <!-- Phần 2: Người nhận -->
    <div class="border-b-2 border-black pb-2 mb-2">
        <div class="text-[10px] font-bold mb-1 uppercase">Đến (Người nhận):</div>
        <div class="font-black text-xl uppercase leading-none mb-1">{{ $order->customer_name }}</div>
        <div class="font-black text-lg tracking-widest mt-1.5"><span class="text-[10px] font-bold uppercase tracking-normal mr-1">SĐT:</span>{{ $order->customer_phone }}</div>
        @if($ghnAddressStr)
            <div class="text-[11px] font-bold mt-1.5 leading-snug">{{ $ghnAddressStr }}</div>
        @endif
        <div class="text-[10px] font-medium mt-0.5 leading-snug italic">{{ $order->address }}</div>
    </div>
    
    <!-- Phần 3: Mã đơn & Tiền thu hộ (COD) -->
    <div class="flex border-b-2 border-black pb-2 mb-2 gap-2">
        <div class="w-1/2 border-r-2 border-black pr-2">
            <div class="text-[10px] font-bold uppercase">Mã đơn hàng:</div>
            <div class="font-black text-sm uppercase">ORD-{{ $order->id }}</div>
            <div class="text-[9px] mt-1 font-bold">Ngày: {{ $order->created_at->format('d/m/Y H:i') }}</div>
        </div>
        <div class="w-1/2 text-center flex flex-col justify-center">
            <div class="text-[10px] font-bold uppercase">Tổng thu hộ (COD)</div>
            @if($order->payment_status == 'paid')
                <div class="font-black text-3xl mt-1 tracking-tighter">0 đ</div>
                <div class="text-[10px] font-black border border-black inline-block px-1 mx-auto mt-1 uppercase">Đã thanh toán trước</div>
            @else
                <div class="font-black text-2xl mt-1 tracking-tighter">{{ number_format($order->total_amount, 0, ',', '.') }} đ</div>
                <div class="text-[9px] font-bold mt-1">Người nhận trả tiền</div>
            @endif
        </div>
    </div>
    
    <!-- Phần 4: Danh sách sản phẩm -->
    <div class="border-b-2 border-black pb-2 mb-2 min-h-[80px]">
        <div class="text-[10px] font-bold mb-1 uppercase">Nội dung hàng (Tổng SL: {{ $order->items->sum('quantity') }}):</div>
        <table class="w-full text-xs font-bold">
            @foreach($order->items as $index => $item)
            <tr class="border-b border-dashed border-gray-300 last:border-0">
                <td class="pr-1 align-top py-1">{{$index+1}}.</td>
                <td class="align-top py-1">{{ $item->product_name }} 
                    @if($item->variant_attributes) <span class="text-[10px] font-normal">({{ $item->variant_attributes }})</span> @endif
                </td>
                <td class="text-right align-top py-1 whitespace-nowrap">SL: <span class="text-sm font-black">{{ $item->quantity }}</span></td>
            </tr>
            @endforeach
        </table>
    </div>

    <!-- Phần 5: Ghi chú -->
    <div class="text-[11px] font-bold text-center border-2 border-black p-1 uppercase">
        Lưu ý: {{ $order->note ?: 'Cho khách xem hàng, không thử. Quay video khi bóc.' }}
    </div>
    
    <!-- Phần 6: Chữ ký -->
    <div class="flex justify-between mt-2 px-4">
        <div class="text-[9px] font-bold text-center">
            Chữ ký bưu tá
            <div class="mt-8 text-[8px] italic text-gray-500">(Ký, ghi rõ họ tên)</div>
        </div>
        <div class="text-[9px] font-bold text-center">
            Chữ ký người nhận
            <div class="mt-8 text-[8px] italic text-gray-500">(Xác nhận hàng nguyên vẹn)</div>
        </div>
    </div>
</div>

@endsection
