@extends('admin.layouts.admin')

@section('title', 'Chi tiết Đơn hàng')
@section('page_title', 'Chi tiết Đơn hàng: ORD-'.$order->id)

@section('content')
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
        'ready_to_pick' => 'Đã chuẩn bị hàng',
        'shipping' => 'Đang giao hàng',
        'completed' => 'Đã giao',
        'cancelled' => 'Đã hủy',
    ];
    $badgeClass = $statusColors[$order->status] ?? 'bg-slate-100 text-slate-800';
@endphp

<div class="w-full h-[calc(100vh-90px)] flex flex-col">
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        <div class="max-w-7xl mx-auto space-y-6">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-2">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white uppercase tracking-tight">ORD-{{ $order->id }}</h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                            {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        <i class="bi bi-calendar3 mr-1"></i> Đặt lúc: {{ $order->created_at->format('H:i - d/m/Y') }}
                    </p>
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <x-admin.button onclick="window.print()" variant="secondary" icon="bi bi-printer">
                        In đơn
                    </x-admin.button>
                    <x-admin.button href="{{ route('admin.orders.index') }}" variant="ghost" icon="bi bi-arrow-left">
                        Quay lại
                    </x-admin.button>
                </div>
            </div>

            @if(session('success'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Cột Trái (2/3): Bảng SP, Tổng kết, Timeline -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Order Items -->
                    <x-admin.card noPadding="true" title="Chi tiết sản phẩm ({{ $order->items->sum('quantity') }})" icon="bi bi-cart3">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 font-medium text-xs uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">
                                    <tr>
                                        <th class="px-6 py-3">Sản phẩm</th>
                                        <th class="px-6 py-3 text-right">Đơn giá</th>
                                        <th class="px-6 py-3 text-center">SL</th>
                                        <th class="px-6 py-3 text-right">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    @foreach($order->items as $item)
                                        @php
                                            $variant = $item->productVariant;
                                            $imgUrl = $variant->thumbnail_url ?? ($variant->product->primary_image_url ?? 'https://via.placeholder.com/150');
                                            if ($imgUrl && !Str::startsWith($imgUrl, ['http://', 'https://'])) {
                                                $imgUrl = Storage::url($imgUrl);
                                            }
                                        @endphp
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-12 h-12 rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden shrink-0 bg-white">
                                                        <img src="{{ $imgUrl }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[200px] md:max-w-[300px]" title="{{ $item->product_name }}">
                                                            {{ $item->product_name }}
                                                        </div>
                                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                                            {{ $item->variant_attributes ?: 'Mặc định' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-right font-medium">
                                                {{ number_format($item->price, 0, ',', '.') }}đ
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="px-6 py-4 text-right font-bold text-slate-800 dark:text-slate-200">
                                                {{ number_format($item->total, 0, ',', '.') }}đ
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Financial Summary -->
                        <div class="px-6 py-5 flex justify-end border-t border-slate-200 dark:border-slate-700">
                            <table class="w-full sm:w-80 text-sm text-slate-600 dark:text-slate-400">
                                <tbody>
                                    <tr>
                                        <td class="py-2">Tạm tính ({{ $order->items->sum('quantity') }} sản phẩm)</td>
                                        <td class="py-2 text-right font-medium text-slate-800 dark:text-slate-200">{{ number_format($order->subtotal, 0, ',', '.') }}đ</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">Phí vận chuyển</td>
                                        <td class="py-2 text-right font-medium text-slate-800 dark:text-slate-200">{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">Giảm giá</td>
                                        <td class="py-2 text-right font-medium text-emerald-600 dark:text-emerald-400">- 0đ</td>
                                    </tr>
                                    <tr class="border-t border-slate-200 dark:border-slate-700">
                                        <td class="pt-4 mt-2 text-base font-bold text-slate-800 dark:text-white uppercase">Tổng cộng</td>
                                        <td class="pt-4 mt-2 text-right text-2xl font-black text-rose-600 dark:text-rose-400">{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </x-admin.card>

                    <!-- Timeline -->
                    <x-admin.card title="Tiến trình đơn hàng" icon="bi bi-clock-history">
                        @php
                            // Logic xác định bước trong tiến trình
                            $currentStep = 1;
                            if ($order->status == 'cancelled') {
                                $currentStep = -1;
                            } elseif ($order->status == 'completed' || $order->shipping_status == 'delivered') {
                                $currentStep = 4;
                            } elseif ($order->status == 'shipping' || in_array($order->shipping_status, ['delivering', 'picking'])) {
                                $currentStep = 3;
                            } elseif ($order->status == 'ready_to_pick' || $order->shipping_status == 'ready_to_pick') {
                                $currentStep = 2;
                            }
                        @endphp

                        @if($currentStep === -1)
                            <div class="flex flex-col items-center justify-center py-6 text-rose-500">
                                <i class="bi bi-x-circle text-4xl mb-2"></i>
                                <p class="font-bold">Đơn hàng đã bị hủy</p>
                                <p class="text-sm text-slate-500 mt-1">Cập nhật lúc: {{ $order->updated_at->format('H:i d/m/Y') }}</p>
                            </div>
                        @else
                            <div class="p-4">
                                <ol class="relative border-l-2 border-slate-200 dark:border-slate-700 ml-4">                  
                                    <!-- Step 1: Đặt hàng -->
                                    <li class="mb-8" style="margin-left: 40px;">            
                                        <span class="absolute flex items-center justify-center w-8 h-8 bg-primary-100 rounded-full -left-4 ring-4 ring-white dark:ring-slate-800 dark:bg-primary-900">
                                            <i class="bi bi-cart text-sm text-primary-600 dark:text-primary-400"></i>
                                        </span>
                                        <h3 class="font-bold leading-tight text-slate-800 dark:text-white text-base">Đặt hàng thành công</h3>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $order->created_at->format('H:i - d/m/Y') }}</p>
                                    </li>
                                    <!-- Step 2: Xử lý -->
                                    <li class="mb-8" style="margin-left: 40px;">
                                        <span class="absolute flex items-center justify-center w-8 h-8 rounded-full -left-4 ring-4 ring-white dark:ring-slate-800 {{ $currentStep >= 2 ? 'bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-500' }}">
                                            <i class="bi bi-box-seam text-sm"></i>
                                        </span>
                                        <h3 class="font-bold leading-tight {{ $currentStep >= 2 ? 'text-slate-800 dark:text-white' : 'text-slate-500' }} text-base">Đã xác nhận</h3>
                                        @if($currentStep == 2)
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $order->updated_at->format('H:i - d/m/Y') }}</p>
                                        @endif
                                    </li>
                                    <!-- Step 3: Vận chuyển -->
                                    <li class="mb-8" style="margin-left: 40px;">
                                        <span class="absolute flex items-center justify-center w-8 h-8 rounded-full -left-4 ring-4 ring-white dark:ring-slate-800 {{ $currentStep >= 3 ? 'bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-500' }}">
                                            <i class="bi bi-truck text-sm"></i>
                                        </span>
                                        <h3 class="font-bold leading-tight {{ $currentStep >= 3 ? 'text-slate-800 dark:text-white' : 'text-slate-500' }} text-base">Đang giao hàng</h3>
                                        @if($currentStep == 3)
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $order->updated_at->format('H:i - d/m/Y') }}</p>
                                        @endif
                                    </li>
                                    <!-- Step 4: Hoàn thành -->
                                    <li style="margin-left: 40px;">
                                        <span class="absolute flex items-center justify-center w-8 h-8 rounded-full -left-4 ring-4 ring-white dark:ring-slate-800 {{ $currentStep >= 4 ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-500' }}">
                                            <i class="bi bi-check text-xl"></i>
                                        </span>
                                        <h3 class="font-bold leading-tight {{ $currentStep >= 4 ? 'text-slate-800 dark:text-white' : 'text-slate-500' }} text-base">Giao hàng thành công</h3>
                                        @if($currentStep == 4)
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $order->updated_at->format('H:i - d/m/Y') }}</p>
                                        @endif
                                    </li>
                                </ol>
                            </div>
                        @endif
                    </x-admin.card>
                    
                </div>

                <!-- Cột Phải (1/3): KH, Vận chuyển, Admin Actions -->
                <div class="space-y-6">
                    
                    <!-- Customer Info -->
                    <x-admin.card title="Khách hàng" icon="bi bi-person-lines-fill">
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                @php
                                    $avatarUrl = $order->user && $order->user->avatar ? Storage::url($order->user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($order->customer_name).'&background=E0246F&color=fff&rounded=true&size=48';
                                @endphp
                                <img src="{{ $avatarUrl }}" alt="{{ $order->customer_name }}" class="w-10 h-10 rounded-full border border-slate-200 shadow-sm shrink-0 object-cover">
                                <div>
                                    <div class="font-bold text-slate-800 dark:text-white">{{ $order->customer_name }}</div>
                                    <a href="tel:{{ $order->customer_phone }}" class="text-sm text-primary-600 hover:underline"><i class="bi bi-telephone-fill text-xs mr-1"></i>{{ $order->customer_phone }}</a>
                                </div>
                            </div>
                            
                            <hr class="border-slate-100 dark:border-slate-700">
                            
                            <div>
                                <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Giao đến</div>
                                <div class="text-sm text-slate-700 dark:text-slate-300">
                                    {{ $order->address }}
                                </div>
                            </div>

                            @if($order->note)
                            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-900/50 rounded-xl p-3">
                                <div class="text-xs font-bold text-amber-800 dark:text-amber-500 uppercase mb-1"><i class="bi bi-chat-square-text mr-1"></i> Ghi chú của khách</div>
                                <div class="text-sm text-amber-900 dark:text-amber-200 italic">"{{ $order->note }}"</div>
                            </div>
                            @endif
                        </div>
                    </x-admin.card>

                    <!-- Payment Info -->
                    <x-admin.card title="Thanh toán" icon="bi bi-credit-card">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm text-slate-600 dark:text-slate-400">Phương thức:</span>
                            <span class="text-sm font-bold text-slate-800 dark:text-white uppercase">{{ $order->payment_method }}</span>
                        </div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm text-slate-600 dark:text-slate-400">Trạng thái:</span>
                            @php
                                $latestTxn = $order->transactions()->latest()->first();
                                $hasFailedTxn = $latestTxn && $latestTxn->status === 'failed';
                            @endphp
                            
                            @if($order->payment_status == 'paid')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    <i class="bi bi-check-circle-fill mr-1"></i> Đã thanh toán
                                </span>
                            @elseif($hasFailedTxn)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400">
                                    <i class="bi bi-x-circle-fill mr-1"></i> Thanh toán thất bại
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                    <i class="bi bi-clock-fill mr-1"></i> Chưa thanh toán
                                </span>
                            @endif
                        </div>
                    </x-admin.card>

                    <!-- GHN Shipping -->
                    <x-admin.card title="Vận chuyển GHN" icon="bi bi-truck" class="border-primary-200 dark:border-primary-900/50">
                        <x-slot name="headerActions">
                            @if($order->shipping_order_code)
                            <form action="{{ route('admin.orders.sync_ghn', $order) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="text-xs font-bold bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 border border-primary-200 dark:border-primary-700 px-3 py-1 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-colors">
                                    Đồng bộ
                                </button>
                            </form>
                            @endif
                        </x-slot>
                        
                        @if($order->shipping_order_code)
                            <div class="mb-4">
                                <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Mã vận đơn</div>
                                <div class="text-lg font-black text-primary-600 dark:text-primary-400 tracking-wider">{{ $order->shipping_order_code }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Trạng thái từ GHN</div>
                                <div class="text-sm font-medium text-slate-800 dark:text-slate-200 p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                                    {{ $order->shipping_status ?? 'Đang khởi tạo...' }}
                                </div>
                            </div>
                        @else
                            <div class="text-center py-2">
                                <i class="bi bi-box-seam text-4xl mb-3 block text-slate-300 dark:text-slate-600"></i>
                                <p class="text-sm text-slate-500 mb-4">Đơn hàng chưa có mã vận đơn GHN.</p>
                                
                                @if($order->status == 'pending' || $order->status == 'processing')
                                <form action="{{ route('admin.orders.confirm_ghn', $order) }}" method="POST" class="form-confirm"
                                      data-confirm-title="Đẩy đơn sang GHN?"
                                      data-confirm-text="Xác nhận tạo đơn giao hàng và lấy mã vận đơn từ GHN cho đơn hàng #ORD-{{ $order->id }}?"
                                      data-confirm-icon="question"
                                      data-confirm-btn="<i class='bi bi-send-check mr-1'></i> Đẩy đơn GHN"
                                      data-confirm-color="#2563eb">
                                    @csrf
                                    <x-admin.button type="submit" variant="primary" class="w-full justify-center" icon="bi bi-send-check">
                                        Tạo đơn GHN ngay
                                    </x-admin.button>
                                </form>
                                @endif
                            </div>
                        @endif
                    </x-admin.card>

                    <!-- Admin Actions (Update Status) -->
                    <x-admin.card title="Thao tác Đơn hàng" icon="bi bi-gear-fill" class="{{ $order->status == 'cancelled' ? 'opacity-50 pointer-events-none' : '' }}">
                        <div class="space-y-3">
                            <!-- Chuyển trạng thái -->
                            <form action="{{ route('admin.orders.update_status', $order) }}" method="POST" class="form-confirm"
                                  data-confirm-title="Cập nhật trạng thái?"
                                  data-confirm-text="Xác nhận lưu thay đổi trạng thái cho đơn hàng #ORD-{{ $order->id }}?"
                                  data-confirm-icon="question"
                                  data-confirm-btn="<i class='bi bi-check2-circle mr-1'></i> Lưu cập nhật"
                                  data-confirm-color="#2563eb">
                                @csrf
                                <div class="mb-3">
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Cập nhật trạng thái</label>
                                    <select name="status" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500">
                                        @php
                                            $adminStatuses = ['pending', 'processing', 'ready_to_pick', 'shipping', 'completed'];
                                            $currentLevel = array_search($order->status, $adminStatuses);
                                        @endphp
                                        @foreach($adminStatuses as $index => $val)
                                            @php
                                                $disabled = ($currentLevel >= 3 && $index < $currentLevel) ? 'disabled' : '';
                                            @endphp
                                            <option value="{{ $val }}" {{ $order->status == $val ? 'selected' : '' }} {{ $disabled }}>
                                                {{ $statusLabels[$val] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <x-admin.button type="submit" variant="primary" size="sm" class="w-full justify-center" icon="bi bi-check2-circle">
                                    Cập nhật
                                </x-admin.button>
                            </form>

                            @if($order->status != 'cancelled' && $order->status != 'completed')
                                <hr class="border-slate-100 dark:border-slate-700">
                                
                                <!-- Nút Hủy đơn -->
                                <form action="{{ route('admin.orders.update_status', $order) }}" method="POST" class="form-delete" data-confirm-title="Hủy đơn hàng?" data-confirm-text="Bạn có chắc chắn muốn HỦY đơn hàng này không? Hành động này không thể hoàn tác!">
                                    @csrf
                                    <input type="hidden" name="status" value="cancelled">
                                    <x-admin.button type="submit" variant="danger" class="w-full justify-center" icon="bi bi-x-octagon">
                                        Hủy đơn hàng này
                                    </x-admin.button>
                                </form>
                            @endif
                        </div>
                    </x-admin.card>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
