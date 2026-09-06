@extends('layouts.client')

@section('title', 'Thanh toán thành công')

@section('content')
<div class="bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 font-sans">
    <div class="max-w-4xl mx-auto space-y-8">
        
        <!-- 1. Header Section -->
        <div class="bg-white p-8 rounded-3xl shadow-sm text-center border border-gray-100">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-6">
                <i class="bi bi-check-lg text-green-500 text-4xl"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Thanh toán thành công!</h1>
            <p class="text-gray-600 mb-6">Cảm ơn bạn đã mua sắm tại <span class="font-bold text-gray-900">Aurelia</span>. Giao dịch của bạn đã hoàn tất.</p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 text-sm">
                <div class="bg-gray-50 px-4 py-2 rounded-lg border border-gray-200">
                    <span class="text-gray-500">Mã đơn hàng:</span>
                    <span class="font-bold text-gray-900 ml-1">#AUR-{{ $order->id ?? request('orderCode') }}</span>
                </div>
                <div class="bg-blue-50 px-4 py-2 rounded-lg border border-blue-100 text-blue-700">
                    <i class="bi bi-qr-code-scan mr-1"></i> Đã thanh toán qua PayOS
                </div>
            </div>
        </div>

        @if(isset($order))
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Details & Items -->
            <div class="lg:col-span-2 space-y-8">
                <!-- 2. Order Details -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-gray-100"><i class="bi bi-person-lines-fill mr-2 text-brand"></i>Thông tin nhận hàng</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex">
                            <span class="text-gray-500 w-1/3">Người nhận:</span>
                            <span class="font-medium text-gray-900 w-2/3">{{ $order->customer_name }}</span>
                        </div>
                        <div class="flex">
                            <span class="text-gray-500 w-1/3">Số điện thoại:</span>
                            <span class="font-medium text-gray-900 w-2/3">{{ $order->customer_phone }}</span>
                        </div>
                        <div class="flex">
                            <span class="text-gray-500 w-1/3">Địa chỉ giao hàng:</span>
                            <span class="font-medium text-gray-900 w-2/3">{{ $order->address }}</span>
                        </div>
                        <div class="flex">
                            <span class="text-gray-500 w-1/3">Đơn vị vận chuyển:</span>
                            <span class="font-medium text-gray-900 w-2/3">Giao Hàng Nhanh (GHN)</span>
                        </div>
                        @if($order->note)
                        <div class="flex">
                            <span class="text-gray-500 w-1/3">Ghi chú:</span>
                            <span class="font-medium text-gray-900 w-2/3 italic">{{ $order->note }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- 3. Items Summary -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-gray-100"><i class="bi bi-box-seam mr-2 text-brand"></i>Sản phẩm đã đặt</h2>
                    <div class="space-y-4">
                        @foreach($order->items as $item)
                        <div class="flex gap-4 items-center">
                            @php
                                $variant = $item->productVariant;
                                $product = $variant ? $variant->product : null;
                                $imgUrl = $variant->thumbnail_url ?? ($product->primary_image_url ?? 'https://via.placeholder.com/300');
                                if ($imgUrl && !\Illuminate\Support\Str::startsWith($imgUrl, ['http://', 'https://'])) {
                                    $imgUrl = \Illuminate\Support\Facades\Storage::url($imgUrl);
                                }
                            @endphp
                            <div class="w-16 h-20 bg-gray-100 rounded-lg overflow-hidden shrink-0 border border-gray-200">
                                <img src="{{ $imgUrl }}" alt="Product Image" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 text-sm line-clamp-2">{{ $product ? $product->name : 'Sản phẩm đã xóa' }}</h4>
                                <div class="text-xs text-gray-500 mt-1">
                                    Phân loại: {{ $item->variant_attributes }}
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($item->price, 0, ',', '.') }}đ x {{ $item->quantity }}</span>
                                    <span class="text-sm font-bold text-brand">{{ number_format($item->total, 0, ',', '.') }}đ</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Column: Pricing & Actions -->
            <div class="space-y-8">
                <!-- 4. Pricing Breakdown -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-gray-100">Chi tiết thanh toán</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tạm tính:</span>
                            <span class="font-medium text-gray-900">{{ number_format($order->subtotal, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Phí vận chuyển:</span>
                            <span class="font-medium text-gray-900">{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</span>
                        </div>
                        @if($order->discount > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Giảm giá (Voucher):</span>
                            <span class="font-medium">-{{ number_format($order->discount, 0, ',', '.') }}đ</span>
                        </div>
                        @endif
                        <div class="pt-3 border-t border-gray-100 flex justify-between items-center">
                            <span class="text-base font-bold text-gray-900">Tổng thanh toán:</span>
                            <span class="text-xl font-black text-brand">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="text-right text-xs text-gray-400 mt-1">(Đã bao gồm VAT)</div>
                    </div>
                </div>

                <!-- 5. Action Buttons & Support -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                    <a href="{{ route('profile.orders') }}" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-gray-900 hover:bg-gray-800 transition-colors uppercase tracking-wide">
                        Theo dõi đơn hàng
                    </a>
                    <a href="{{ route('home') }}" class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-xl shadow-sm text-sm font-bold text-gray-700 bg-white hover:bg-gray-50 transition-colors uppercase tracking-wide">
                        Tiếp tục mua sắm
                    </a>
                    
                    <div class="pt-4 border-t border-gray-100 text-sm">
                        <p class="text-gray-500 mb-2 font-medium">Bạn cần hỗ trợ?</p>
                        <a href="{{ route('pages.return_policy') }}" class="flex items-center text-gray-600 hover:text-brand mb-2 transition-colors">
                            <i class="bi bi-arrow-return-left mr-2"></i> Chính sách đổi trả
                        </a>
                        <a href="tel:19001234" class="flex items-center text-gray-600 hover:text-brand transition-colors">
                            <i class="bi bi-telephone-fill mr-2"></i> Hotline: 1900 1234
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- Fallback if order not found -->
        <div class="text-center mt-8">
            <a href="{{ route('profile.orders') }}" class="inline-flex justify-center py-3 px-8 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-gray-900 hover:bg-gray-800 transition-colors">
                Xem đơn hàng của tôi
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
