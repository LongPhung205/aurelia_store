@extends('admin.layouts.admin')

@section('title', 'Quản lý Đơn hàng')
@section('page_title', 'Danh sách Đơn hàng')

@section('content')
<div class="w-full h-[calc(100vh-90px)] flex flex-col">

    <x-admin.card noPadding="true" title="Danh sách đơn hàng" icon="bi bi-cart3" class="flex-1 flex flex-col min-h-0" bodyClass="flex-1 flex flex-col min-h-0">
        
        <!-- Status Tabs -->
        <div class="border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shrink-0 px-2 pt-2">
            <div class="flex overflow-x-auto custom-scrollbar gap-1">
                @php
                    $currentStatus = request('status', '');
                    $tabs = [
                        '' => 'Tất cả',
                        'pending' => 'Chờ xác nhận',
                        'processing' => 'Đã xác nhận',
                        'ready_to_pick' => 'Chờ lấy hàng',
                        'shipping' => 'Đang giao hàng',
                        'completed' => 'Đã giao',
                        'cancelled' => 'Đã hủy',
                    ];
                @endphp
                
                @foreach($tabs as $key => $label)
                    <a href="{{ request()->fullUrlWithQuery(['status' => $key, 'page' => null]) }}" 
                       class="whitespace-nowrap px-5 py-3 text-sm font-medium border-b-2 transition-colors rounded-t-lg {{ $currentStatus === (string)$key ? 'border-primary-500 text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/10' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-700' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="p-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shrink-0">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                
                <div class="md:col-span-4">
                    <x-admin.input name="search" label="Tìm kiếm" placeholder="Mã đơn, Tên KH, SĐT..." icon="bi bi-search" :value="request('search')" />
                </div>
                
                <div class="md:col-span-3">
                    <x-admin.input type="date" name="date_from" label="Từ ngày" :value="request('date_from')" />
                </div>
                
                <div class="md:col-span-3">
                    <x-admin.input type="date" name="date_to" label="Đến ngày" :value="request('date_to')" />
                </div>
                
                <div class="md:col-span-2 mb-4 flex gap-2">
                    <x-admin.button type="submit" variant="primary" icon="bi bi-funnel" class="w-full py-2.5">
                        Lọc
                    </x-admin.button>
                    <a href="{{ route('admin.orders.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600 text-sm font-medium rounded-lg transition-colors focus:ring-4 focus:ring-slate-200 dark:focus:ring-slate-700 flex items-center justify-center">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="overflow-auto flex-1 relative shadow-inner">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 relative">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.1)]">
                    <tr>
                        <th class="px-6 py-3">Đơn hàng</th>
                        <th class="px-6 py-3">Khách hàng</th>
                        <th class="px-6 py-3">Tổng tiền</th>
                        <th class="px-6 py-3 text-center">Thanh toán</th>
                        <th class="px-6 py-3">Vận chuyển / GHN</th>
                        <th class="px-6 py-3 text-right w-48">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($orders as $order)
                        <tr class="bg-white hover:bg-slate-50 dark:bg-gray-800 dark:hover:bg-gray-700/50 transition-colors">
                            <!-- Order ID -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-primary-600 dark:text-primary-400 hover:underline text-base">
                                    ORD-{{ $order->id }}
                                </a>
                                <div class="text-[11px] text-slate-500 mt-1">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            
                            <!-- Customer -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @php
                                        // Kiểm tra xem khách có tài khoản hay không (nếu có dùng avatar của họ, không thì dùng chữ cái đầu)
                                        $avatarUrl = $order->user && $order->user->avatar ? Storage::url($order->user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($order->customer_name).'&background=E0246F&color=fff&rounded=true&size=40';
                                    @endphp
                                    <img src="{{ $avatarUrl }}" alt="{{ $order->customer_name }}" class="w-9 h-9 rounded-full shadow-sm shrink-0 border border-slate-100 object-cover">
                                    <div>
                                        <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $order->customer_name }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5"><i class="bi bi-telephone mr-1"></i>{{ $order->customer_phone }}</div>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Total Amount -->
                            <td class="px-6 py-4 font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap">
                                {{ number_format($order->total_amount, 0, ',', '.') }}đ
                            </td>
                            
                            <!-- Payment Status -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col gap-1.5 items-center justify-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300 uppercase">
                                        {{ $order->payment_method }}
                                    </span>
                                    @if($order->payment_status == 'paid')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                            <i class="bi bi-check-circle-fill mr-1"></i> Đã thanh toán
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                            <i class="bi bi-clock-fill mr-1"></i> Chưa thanh toán
                                        </span>
                                    @endif
                                </div>
                            </td>
                            
                            <!-- Shipping Status -->
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
                                        'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                        'ready_to_pick' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
                                        'shipping' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                                        'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
                                        'cancelled' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Chờ xác nhận',
                                        'processing' => 'Đã xác nhận',
                                        'ready_to_pick' => 'Chờ lấy hàng',
                                        'shipping' => 'Đang giao hàng',
                                        'completed' => 'Đã giao',
                                        'cancelled' => 'Đã hủy',
                                    ];
                                    $badgeClass = $statusColors[$order->status] ?? 'bg-slate-100 text-slate-800';
                                @endphp
                                <div class="flex flex-col gap-1.5 items-start">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                                        {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                                    </span>
                                    
                                    @if($order->shipping_order_code)
                                        <div class="text-[11px] text-slate-500">
                                            Mã GHN: <strong class="text-slate-700 dark:text-slate-300">{{ $order->shipping_order_code }}</strong>
                                        </div>
                                        <div class="text-[10px] text-indigo-500 font-medium truncate w-40" title="{{ $order->shipping_status }}">{{ $order->shipping_status }}</div>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">Chưa đẩy GHN</span>
                                    @endif
                                </div>
                            </td>
                            
                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 relative">
                                    <x-admin.button href="{{ route('admin.orders.show', $order) }}" variant="secondary" size="sm" icon="bi bi-eye" title="Xem chi tiết">
                                        Chi tiết
                                    </x-admin.button>
                                    
                                    <!-- Nút cập nhật trạng thái nhanh (Dropdown) -->
                                    <button id="dropdownStatusBtn_{{ $order->id }}" data-dropdown-toggle="dropdownStatus_{{ $order->id }}" class="px-3 py-1.5 text-xs font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 flex items-center gap-1" type="button">
                                        Đổi TT <i class="bi bi-chevron-down"></i>
                                    </button>

                                    <!-- Dropdown menu -->
                                    <div id="dropdownStatus_{{ $order->id }}" class="z-50 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700 text-left">
                                        <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownStatusBtn_{{ $order->id }}">
                                            @foreach($statusLabels as $sKey => $sLabel)
                                                @if($sKey != $order->status)
                                                <li>
                                                    <form action="{{ route('admin.orders.update_status', $order) }}" method="POST" class="m-0" onsubmit="return confirm('Xác nhận đổi trạng thái đơn hàng sang: {{ $sLabel }}?');">
                                                        @csrf
                                                        <input type="hidden" name="status" value="{{ $sKey }}">
                                                        <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                                            {{ $sLabel }}
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="bi bi-inbox text-4xl mb-3 text-slate-300 dark:text-slate-600"></i>
                                    <p>Không tìm thấy đơn hàng nào khớp với điều kiện lọc.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 shrink-0">
            {{ $orders->links() }}
        </div>
        @endif

    </x-admin.card>
</div>
@endsection
