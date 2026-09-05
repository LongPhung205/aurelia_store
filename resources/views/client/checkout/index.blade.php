@extends('layouts.client')

@section('content')
<div class="mb-10">
    <h1 class="text-3xl font-bold text-gray-900 mb-6 text-center uppercase tracking-wide">Thanh toán</h1>
    
    @if(session('error'))
    <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 shadow-sm border border-red-100 flex items-center">
        <i class="bi bi-exclamation-circle-fill mr-2"></i> {{ session('error') }}
    </div>
    @endif

    <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form" x-data="checkoutApp()" x-init="initApp()" class="flex flex-col lg:flex-row gap-8">
        @csrf
        <input type="hidden" name="coupon_code" :value="appliedCouponCode">
        
        <!-- Left Column: Shipping Info -->
        <div class="flex-1 bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6 pb-4 border-b border-gray-100">Thông tin nhận hàng</h2>
            
            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Họ và tên <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_name" value="{{ auth()->check() ? auth()->user()->name : old('customer_name') }}" required class="w-full rounded-xl border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50 transition-colors">
                        @error('customer_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_phone" value="{{ auth()->check() ? auth()->user()->phone : old('customer_phone') }}" required class="w-full rounded-xl border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50 transition-colors">
                        @error('customer_phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tỉnh / Thành phố <span class="text-red-500">*</span></label>
                        <select name="province_id" x-model="selectedProvince" @change="fetchDistricts" required class="w-full rounded-xl border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50 transition-colors">
                            <option value="">Chọn Tỉnh/Thành</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province['ProvinceID'] }}">{{ $province['ProvinceName'] }}</option>
                            @endforeach
                        </select>
                        @error('province_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quận / Huyện <span class="text-red-500">*</span></label>
                        <select name="district_id" x-model="selectedDistrict" @change="fetchWards" required :disabled="!selectedProvince || isLoadingDistricts" class="w-full rounded-xl border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50 transition-colors disabled:bg-gray-100">
                            <option value="">Chọn Quận/Huyện</option>
                            <template x-for="d in districts" :key="d.DistrictID">
                                <option :value="d.DistrictID" x-text="d.DistrictName"></option>
                            </template>
                        </select>
                        <span x-show="isLoadingDistricts" class="text-xs text-gray-500 mt-1"><i class="bi bi-arrow-repeat animate-spin inline-block"></i> Đang tải...</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phường / Xã <span class="text-red-500">*</span></label>
                        <select name="ward_code" x-model="selectedWard" @change="calculateFee" required :disabled="!selectedDistrict || isLoadingWards" class="w-full rounded-xl border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50 transition-colors disabled:bg-gray-100">
                            <option value="">Chọn Phường/Xã</option>
                            <template x-for="w in wards" :key="w.WardCode">
                                <option :value="w.WardCode" x-text="w.WardName"></option>
                            </template>
                        </select>
                        <span x-show="isLoadingWards" class="text-xs text-gray-500 mt-1"><i class="bi bi-arrow-repeat animate-spin inline-block"></i> Đang tải...</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Địa chỉ cụ thể (Số nhà, tên đường...) <span class="text-red-500">*</span></label>
                    <input type="text" name="address" value="{{ auth()->check() ? auth()->user()->address : old('address') }}" placeholder="VD: Số 123, Đường ABC..." required class="w-full rounded-xl border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50 transition-colors">
                    @error('address') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ghi chú (Tùy chọn)</label>
                    <textarea name="note" rows="3" placeholder="Lưu ý khi giao hàng..." class="w-full rounded-xl border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50 transition-colors">{{ old('note') }}</textarea>
                </div>
            </div>

            <h2 class="text-xl font-bold text-gray-900 mt-10 mb-6 pb-4 border-b border-gray-100">Phương thức thanh toán</h2>
            <div class="space-y-4">
                <label class="flex items-center p-4 border border-brand rounded-xl cursor-pointer bg-red-50/30 transition-colors">
                    <input type="radio" name="payment_method" value="cod" checked class="w-5 h-5 text-brand focus:ring-brand border-gray-300">
                    <div class="ml-4 flex items-center gap-3">
                        <i class="bi bi-cash-stack text-2xl text-brand"></i>
                        <div>
                            <div class="font-bold text-gray-900">Thanh toán khi nhận hàng (COD)</div>
                            <div class="text-sm text-gray-500">Phí thu hộ tùy thuộc vào đơn vị vận chuyển.</div>
                        </div>
                    </div>
                </label>
                <!-- Thêm PayOS sau -->
                <label class="flex items-center p-4 border border-gray-200 rounded-xl cursor-not-allowed opacity-50 bg-gray-50">
                    <input type="radio" name="payment_method" value="payos" disabled class="w-5 h-5 text-gray-400 focus:ring-gray-400 border-gray-300">
                    <div class="ml-4 flex items-center gap-3">
                        <i class="bi bi-qr-code-scan text-2xl text-gray-600"></i>
                        <div>
                            <div class="font-bold text-gray-600">Thanh toán qua mã QR (PayOS)</div>
                            <div class="text-sm text-gray-500">Sắp ra mắt.</div>
                        </div>
                    </div>
                </label>
            </div>
        </div>
        
        <!-- Right Column: Order Summary -->
        <div class="w-full lg:w-[400px] shrink-0">
            <div class="bg-gray-50 p-6 rounded-2xl shadow-sm border border-gray-100 sticky top-24">
                <h2 class="text-xl font-bold text-gray-900 mb-6 pb-4 border-b border-gray-200">Đơn hàng của bạn</h2>
                
                <div class="space-y-4 mb-6 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                    @foreach($cartItems as $item)
                        @php
                            $variant = $item->productVariant;
                            $product = $variant->product;
                            $imgUrl = $variant->thumbnail_url ?? ($product->primary_image_url ?? 'https://via.placeholder.com/300');
                            if ($imgUrl && !Str::startsWith($imgUrl, ['http://', 'https://'])) {
                                $imgUrl = Storage::url($imgUrl);
                            }
                            $price = $variant->sale_price ?? $variant->price;
                        @endphp
                        <div class="flex gap-4">
                            <div class="w-16 h-20 bg-white rounded-lg overflow-hidden border border-gray-200 shrink-0 relative">
                                <img src="{{ $imgUrl }}" class="w-full h-full object-cover">
                                <span class="absolute -top-2 -right-2 w-5 h-5 bg-gray-900 text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ $item->quantity }}</span>
                            </div>
                            <div class="flex-1 flex flex-col justify-center">
                                <h4 class="font-bold text-gray-800 text-sm line-clamp-2 leading-tight mb-1">{{ $product->name }}</h4>
                                <div class="text-xs text-gray-500 mb-1">{{ $variant->color->name ?? '' }} / {{ $variant->size->name ?? '' }}</div>
                                <div class="text-sm font-bold text-brand">{{ number_format($price * $item->quantity, 0, ',', '.') }}đ</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Mã giảm giá -->
                <div class="border-t border-gray-200 pt-4 mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mã giảm giá</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="couponCode" placeholder="Nhập mã giảm giá..." class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50 transition-colors text-sm uppercase">
                        <button type="button" @click="applyCoupon" :disabled="!couponCode || isApplyingCoupon" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-900 transition-colors disabled:opacity-50 shrink-0">
                            <span x-show="!isApplyingCoupon">Áp dụng</span>
                            <i class="bi bi-arrow-repeat animate-spin" x-show="isApplyingCoupon" style="display: none;"></i>
                        </button>
                    </div>
                    <!-- Coupon Messages -->
                    <div class="mt-2 text-sm">
                        <span x-show="couponError" class="text-red-500" x-text="couponError" style="display: none;"></span>
                        <span x-show="couponMessage" class="text-green-600" x-text="couponMessage" style="display: none;"></span>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4 space-y-3">
                    <div class="flex justify-between text-gray-600">
                        <span>Tạm tính</span>
                        <span class="font-medium text-gray-900">{{ number_format($subtotal, 0, ',', '.') }}đ</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Phí vận chuyển (GHN)</span>
                        <span>
                            <template x-if="isCalculatingFee">
                                <i class="bi bi-arrow-repeat animate-spin inline-block text-brand"></i>
                            </template>
                            <template x-if="!isCalculatingFee">
                                <span class="font-medium" :class="shippingFee > 0 ? 'text-gray-900' : 'text-gray-400'" x-text="shippingFee > 0 ? formatPrice(shippingFee) : 'Chưa xác định'"></span>
                            </template>
                        </span>
                    </div>
                    
                    <!-- Discount Display -->
                    <template x-if="discountAmount > 0">
                        <div class="flex justify-between text-green-600">
                            <span>Giảm giá (Coupon)</span>
                            <span class="font-medium" x-text="'-' + formatPrice(discountAmount)"></span>
                        </div>
                    </template>
                    
                    <div class="border-t border-gray-200 pt-4 mt-4 flex justify-between items-end">
                        <span class="text-lg font-bold text-gray-900">Tổng cộng</span>
                        <div class="text-right">
                            <span class="text-2xl font-black text-brand" x-text="formatPrice(totalAmount)"></span>
                            <div class="text-xs text-gray-500">(Đã bao gồm VAT)</div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" :disabled="isSubmitting || shippingFee === 0" class="w-full bg-brand text-white font-bold py-4 rounded-xl mt-8 shadow-md hover:bg-[#d62800] transition-colors flex items-center justify-center gap-2 uppercase tracking-wide disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="bi bi-arrow-repeat animate-spin text-xl" x-show="isSubmitting" style="display: none;"></i>
                    <span x-text="isSubmitting ? 'Đang xử lý...' : 'Đặt hàng'"></span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endpush

@push('scripts')
<script>
    function checkoutApp() {
        return {
            subtotal: {{ $subtotal }},
            shippingFee: 0,
            
            districts: [],
            wards: [],
            
            selectedProvince: '{{ old('province_id') }}',
            selectedDistrict: '{{ old('district_id') }}',
            selectedWard: '{{ old('ward_code') }}',
            
            isLoadingDistricts: false,
            isLoadingWards: false,
            isCalculatingFee: false,
            isSubmitting: false,
            
            // Coupon state
            couponCode: '',
            appliedCouponCode: '',
            discountAmount: 0,
            couponMessage: '',
            couponError: '',
            isApplyingCoupon: false,
            
            get totalAmount() {
                return this.subtotal + this.shippingFee - this.discountAmount;
            },
            
            initApp() {
                if (this.selectedProvince) {
                    this.fetchDistricts();
                }
                
                // Bắt sự kiện submit form
                document.getElementById('checkout-form').addEventListener('submit', (e) => {
                    if(this.shippingFee === 0 && this.selectedWard) {
                        e.preventDefault();
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Đang tính phí vận chuyển, vui lòng chờ!', type: 'warning' } }));
                        return;
                    }
                    this.isSubmitting = true;
                });
            },
            
            formatPrice(price) {
                return new Intl.NumberFormat('vi-VN').format(price) + 'đ';
            },
            
            fetchDistricts() {
                this.selectedDistrict = '';
                this.selectedWard = '';
                this.districts = [];
                this.wards = [];
                this.shippingFee = 0;
                
                if (!this.selectedProvince) return;
                
                this.isLoadingDistricts = true;
                fetch(`{{ route('checkout.get_districts') }}?province_id=${this.selectedProvince}`)
                    .then(res => res.json())
                    .then(data => {
                        this.districts = data;
                        this.isLoadingDistricts = false;
                        
                        // Nếu có old value và mảng districts đã load xong
                        if('{{ old('district_id') }}' && this.districts.some(d => d.DistrictID == '{{ old('district_id') }}')) {
                            this.selectedDistrict = '{{ old('district_id') }}';
                            this.fetchWards();
                        }
                    })
                    .catch(() => {
                        this.isLoadingDistricts = false;
                    });
            },
            
            fetchWards() {
                this.selectedWard = '';
                this.wards = [];
                this.shippingFee = 0;
                
                if (!this.selectedDistrict) return;
                
                this.isLoadingWards = true;
                fetch(`{{ route('checkout.get_wards') }}?district_id=${this.selectedDistrict}`)
                    .then(res => res.json())
                    .then(data => {
                        this.wards = data;
                        this.isLoadingWards = false;
                        
                        if('{{ old('ward_code') }}' && this.wards.some(w => w.WardCode == '{{ old('ward_code') }}')) {
                            this.selectedWard = '{{ old('ward_code') }}';
                            this.calculateFee();
                        }
                    })
                    .catch(() => {
                        this.isLoadingWards = false;
                    });
            },
            
            calculateFee() {
                if (!this.selectedDistrict || !this.selectedWard) {
                    this.shippingFee = 0;
                    return;
                }
                
                this.isCalculatingFee = true;
                
                fetch('{{ route('checkout.calculate_fee') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        to_district_id: this.selectedDistrict,
                        to_ward_code: this.selectedWard,
                        weight: 200 // Mock weight
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.shippingFee = data.fee || 30000;
                    this.isCalculatingFee = false;
                })
                .catch(() => {
                    this.shippingFee = 30000; // Default fallback
                    this.isCalculatingFee = false;
                });
            },
            
            applyCoupon() {
                if (!this.couponCode) return;
                
                this.isApplyingCoupon = true;
                this.couponError = '';
                this.couponMessage = '';
                
                fetch('{{ route('checkout.apply_coupon') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        coupon_code: this.couponCode,
                        subtotal: this.subtotal
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.isApplyingCoupon = false;
                    if (data.success) {
                        this.discountAmount = data.discount;
                        this.appliedCouponCode = data.coupon_code;
                        this.couponMessage = data.message;
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message, type: 'success' } }));
                    } else {
                        this.discountAmount = 0;
                        this.appliedCouponCode = '';
                        this.couponError = data.message;
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message, type: 'error' } }));
                    }
                })
                .catch(() => {
                    this.isApplyingCoupon = false;
                    this.couponError = 'Lỗi kết nối khi kiểm tra mã.';
                });
            }
        }
    }
</script>
@endpush
