@extends('layouts.client')

@section('title', 'Thanh toán không thành công')

@section('content')
<div class="bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 font-sans">
    <div class="max-w-4xl mx-auto space-y-8">
        
        <!-- 1. Header Section -->
        <div class="bg-white p-8 rounded-3xl shadow-sm text-center border-t-4 border-red-500">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-100 mb-6">
                <i class="bi bi-x-lg text-red-600 text-4xl"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Thanh toán không thành công</h1>
            <p class="text-gray-600 mb-6">Trạng thái: <span class="font-bold text-red-600">Giao dịch bị gián đoạn</span></p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 text-sm">
                <div class="bg-yellow-50 px-4 py-3 rounded-lg border border-yellow-200 text-yellow-800 flex items-center">
                    <i class="bi bi-clock-history mr-2 text-lg"></i>
                    <div class="text-left">
                        Đơn hàng đang được bảo lưu. Tự động hủy sau: <strong id="countdown-timer" class="text-red-600">--:--</strong>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 rounded-lg border border-gray-200">
                    <span class="text-gray-500">Mã đơn hàng:</span>
                    <span class="font-bold text-gray-900 ml-1">#AUR-{{ $order->id ?? request('orderCode') }}</span>
                </div>
            </div>
        </div>

        @if(isset($order))
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Error Details & Summary -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- 2. Error Details -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-gray-100"><i class="bi bi-exclamation-triangle-fill mr-2 text-red-500"></i>Chi tiết sự cố giao dịch</h2>
                    
                    <div class="space-y-4 text-sm">
                        <div class="flex items-start">
                            <i class="bi bi-bug text-gray-400 mt-0.5 mr-3"></i>
                            <div>
                                <span class="font-medium text-gray-900 block">Mã lỗi hệ thống:</span>
                                <span class="text-gray-600 font-mono text-xs">MÃ: {{ request('code') == '00' ? 'CANCELED_BY_USER' : 'ERR_PAYMENT_FAILED_OR_TIMEOUT' }}</span>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <i class="bi bi-question-circle text-gray-400 mt-0.5 mr-3"></i>
                            <div>
                                <span class="font-medium text-gray-900 block">Lý do thường gặp:</span>
                                <ul class="list-disc pl-5 text-gray-600 mt-1 space-y-1">
                                    <li>Bạn đã chủ động hủy hoặc đóng cửa sổ xác minh thanh toán.</li>
                                    <li>Tài khoản ngân hàng của bạn không đủ số dư.</li>
                                    <li>Thời gian quét mã QR đã quá hạn (Timeout).</li>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-green-50 p-3 rounded-xl border border-green-100 flex items-start mt-4">
                            <i class="bi bi-shield-check text-green-600 text-lg mr-3"></i>
                            <div class="text-green-800">
                                <span class="font-bold block">Cam kết an toàn 100%</span>
                                Không có khoản phí nào bị trừ từ tài khoản của bạn. Hệ thống thanh toán của chúng tôi được bảo mật tuyệt đối.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Reserved Order Summary -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-gray-100"><i class="bi bi-box-seam mr-2 text-brand"></i>Sản phẩm đang bảo lưu</h2>
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
                            <div class="w-16 h-20 bg-gray-100 rounded-lg overflow-hidden shrink-0 border border-gray-200 opacity-80">
                                <img src="{{ $imgUrl }}" alt="Product Image" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 text-sm line-clamp-2">{{ $product ? $product->name : 'Sản phẩm đã xóa' }}</h4>
                                <div class="text-xs text-gray-500 mt-1">
                                    Phân loại: {{ $item->variant_attributes }}
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($item->price, 0, ',', '.') }}đ x {{ $item->quantity }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Column: Pricing & Actions -->
            <div class="space-y-8">
                
                <!-- Pricing Breakdown -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-gray-100">Thanh toán cần xử lý</h2>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-500 font-medium">Tổng tiền:</span>
                        <span class="text-2xl font-black text-red-600">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span>
                    </div>
                    
                    <!-- 4. Action Buttons -->
                    <div class="space-y-3 mt-6 border-t border-gray-100 pt-6" id="action-buttons-container">
                        <a href="{{ route('payos.create', $order->id) }}" id="btn-retry" class="w-full flex justify-center py-3 px-4 rounded-xl shadow-sm text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-colors uppercase tracking-wide">
                            <i class="bi bi-arrow-repeat mr-2"></i> Thử thanh toán lại ngay
                        </a>
                        <a href="{{ route('payos.create', $order->id) }}" class="w-full hidden justify-center py-3 px-4 border-2 border-red-100 rounded-xl shadow-sm text-sm font-bold text-red-600 bg-white hover:bg-red-50 transition-colors uppercase tracking-wide">
                            Quét lại mã QR PayOS
                        </a>
                        
                        <a href="{{ route('profile.orders.show', $order->id) }}" class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-xl shadow-sm text-sm font-bold text-gray-700 bg-white hover:bg-gray-50 transition-colors uppercase tracking-wide">
                            Đổi phương thức thanh toán
                        </a>
                    </div>
                    <div id="expired-message" class="hidden text-center text-red-600 font-medium mt-4 p-3 bg-red-50 rounded-lg">
                        Đơn hàng đã hết hạn bảo lưu. Vui lòng đặt hàng lại!
                    </div>
                </div>

                <!-- 5. Support Section -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900 mb-4 uppercase tracking-wide">Cần hỗ trợ khẩn cấp?</h2>
                    <div class="space-y-4 text-sm">
                        <a href="https://zalo.me/19001234" target="_blank" class="w-full flex items-center justify-center py-2.5 px-4 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors font-medium">
                            <i class="bi bi-chat-dots-fill mr-2"></i> Trò chuyện trực tuyến 24/7
                        </a>
                        <a href="tel:19001234" class="w-full flex items-center justify-center py-2.5 px-4 rounded-lg bg-gray-50 text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                            <i class="bi bi-telephone-fill mr-2"></i> Tổng đài: 1900 1234
                        </a>
                    </div>
                </div>

            </div>
        </div>
        @else
        <!-- Fallback if order not found -->
        <div class="text-center mt-8 space-y-4">
            <a href="{{ route('cart.index') }}" class="inline-flex justify-center py-3 px-8 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-colors">
                Trở về giỏ hàng
            </a>
            <br>
            <a href="{{ route('home') }}" class="inline-flex justify-center py-3 px-8 border border-gray-300 rounded-xl shadow-sm text-sm font-bold text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                Quay lại trang chủ
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
@if(isset($order))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calculate expiration time (created_at + 30 minutes)
        const orderTime = new Date("{{ $order->created_at->toISOString() }}").getTime();
        const expirationTime = orderTime + (30 * 60 * 1000);
        
        const countdownEl = document.getElementById('countdown-timer');
        const btnRetry = document.getElementById('btn-retry');
        const expiredMsg = document.getElementById('expired-message');

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = expirationTime - now;

            if (distance < 0 || "{{ $order->status }}" === "cancelled" || "{{ $order->payment_status }}" !== "pending") {
                clearInterval(interval);
                countdownEl.innerHTML = "Đã hết hạn";
                if(btnRetry) btnRetry.style.display = 'none';
                if(expiredMsg) expiredMsg.classList.remove('hidden');
                return;
            }

            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdownEl.innerHTML = minutes.toString().padStart(2, '0') + ":" + seconds.toString().padStart(2, '0');
        }

        const interval = setInterval(updateCountdown, 1000);
        updateCountdown(); // Run immediately
    });
</script>
@endif
@endsection
