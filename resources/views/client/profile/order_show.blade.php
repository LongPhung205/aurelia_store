@extends('client.profile.layout')

@section('profile_content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Header -->
    <div class="bg-gray-50 p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('profile.orders') }}" class="text-gray-500 hover:text-brand transition-colors"><i class="bi bi-arrow-left"></i> Trở lại</a>
                <span class="text-gray-300">|</span>
                <h2 class="text-xl font-bold text-gray-900">Chi tiết đơn hàng #ORD-{{ $order->id }}</h2>
            </div>
            <p class="text-sm text-gray-500">Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div>
            @php
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                    'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'ready_to_pick' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                    'shipping' => 'bg-purple-100 text-purple-800 border-purple-200',
                    'completed' => 'bg-green-100 text-green-800 border-green-200',
                    'cancelled' => 'bg-red-100 text-red-800 border-red-200',
                ];
                $statusLabels = [
                    'pending' => 'Chờ xác nhận',
                    'processing' => 'Đã xác nhận',
                    'ready_to_pick' => 'Chờ lấy hàng',
                    'shipping' => 'Đang giao hàng',
                    'completed' => 'Đã giao thành công',
                    'cancelled' => 'Đã hủy',
                ];
                $colorClass = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                $label = $statusLabels[$order->status] ?? ucfirst($order->status);
            @endphp
            <span class="px-4 py-2 rounded-lg text-sm font-bold border {{ $colorClass }} flex items-center gap-2">
                @if($order->status == 'completed')
                    <i class="bi bi-check-circle-fill"></i>
                @elseif($order->status == 'cancelled')
                    <i class="bi bi-x-circle-fill"></i>
                @else
                    <i class="bi bi-arrow-repeat animate-spin"></i>
                @endif
                {{ $label }}
            </span>
        </div>
    </div>

    <!-- Thông tin khách hàng & Vận chuyển -->
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8 border-b border-gray-100">
        <div>
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="bi bi-geo-alt text-brand"></i> Địa chỉ nhận hàng
            </h3>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="font-bold text-gray-900 mb-1">{{ $order->customer_name }}</p>
                <p class="text-gray-600 mb-1">SĐT: {{ $order->customer_phone }}</p>
                <p class="text-gray-600 mb-1">{{ $order->address }}</p>
                @if($order->note)
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <p class="text-sm font-medium text-gray-700">Ghi chú:</p>
                        <p class="text-sm text-gray-600 italic">{{ $order->note }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div>
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="bi bi-truck text-brand"></i> Thông tin vận chuyển
            </h3>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="text-gray-600 mb-2">Đơn vị vận chuyển: <span class="font-medium text-gray-900">Giao Hàng Nhanh</span></p>
                <p class="text-gray-600 mb-2">Mã vận đơn: 
                    @if($order->shipping_order_code)
                        <span class="font-bold text-brand bg-brand/10 px-2 py-0.5 rounded">{{ $order->shipping_order_code }}</span>
                    @else
                        <span class="italic text-gray-400">Đang chờ tạo mã</span>
                    @endif
                </p>
                <p class="text-gray-600 mb-2">Phương thức thanh toán: 
                    <span class="font-medium text-gray-900 uppercase">{{ $order->payment_method }}</span>
                </p>
                <p class="text-gray-600">Trạng thái thanh toán: 
                    @if($order->payment_status == 'paid')
                        <span class="text-green-600 font-medium">Đã thanh toán</span>
                    @else
                        <span class="text-yellow-600 font-medium">Chưa thanh toán</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Danh sách sản phẩm -->
    <div class="p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <i class="bi bi-box-seam text-brand"></i> Sản phẩm đã đặt
        </h3>
        <div class="space-y-4">
            @foreach($order->items as $item)
                @php
                    $variant = $item->productVariant;
                    $product = $variant->product ?? null;
                    $imgUrl = $variant->thumbnail_url ?? ($product->primary_image_url ?? 'https://via.placeholder.com/150');
                    if ($imgUrl && !Str::startsWith($imgUrl, ['http://', 'https://'])) {
                        $imgUrl = Storage::url($imgUrl);
                    }
                @endphp
                <div class="flex gap-4 p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition-colors">
                    <div class="w-20 h-24 bg-white rounded-lg overflow-hidden border border-gray-200 shrink-0">
                        <img src="{{ $imgUrl }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-grow flex flex-col justify-center">
                        <h4 class="font-medium text-gray-900 line-clamp-2 leading-snug"><a href="{{ $product ? route('products.show', $product->slug) : '#' }}" class="hover:text-brand">{{ $item->product_name }}</a></h4>
                        <div class="text-sm text-gray-500 mt-1">Phân loại: {{ $item->variant_attributes }}</div>
                        <div class="text-sm font-medium text-gray-700 mt-1">Đơn giá: {{ number_format($item->price, 0, ',', '.') }}đ</div>
                    </div>
                    <div class="shrink-0 flex flex-col justify-center items-end text-right">
                        <div class="text-sm text-gray-500 mb-1">Số lượng: x{{ $item->quantity }}</div>
                        <div class="font-bold text-gray-900">{{ number_format($item->total, 0, ',', '.') }}đ</div>
                        
                        @if($order->status == 'completed')
                            <a href="{{ $product ? route('products.show', $product->slug) : '#' }}" class="mt-2 text-xs font-medium text-brand hover:underline">Đánh giá sản phẩm</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Tổng kết tiền -->
    <div class="bg-gray-50 p-6 border-t border-gray-100">
        <div class="w-full sm:w-1/2 lg:w-1/3 ml-auto space-y-3">
            <div class="flex justify-between text-gray-600">
                <span>Tạm tính</span>
                <span>{{ number_format($order->subtotal, 0, ',', '.') }}đ</span>
            </div>
            <div class="flex justify-between text-gray-600">
                <span>Phí vận chuyển</span>
                <span>{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</span>
            </div>
            @if($order->discount > 0)
            <div class="flex justify-between text-green-600">
                <span>Giảm giá</span>
                <span>-{{ number_format($order->discount, 0, ',', '.') }}đ</span>
            </div>
            @endif
            <div class="border-t border-gray-200 pt-3 mt-3 flex justify-between items-center">
                <span class="text-lg font-bold text-gray-900">Tổng cộng</span>
                <span class="text-2xl font-black text-brand">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span>
            </div>
        </div>
    </div>
</div>
@endsection
