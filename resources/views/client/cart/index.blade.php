@extends('layouts.client')

@section('content')
<div class="bg-white shadow rounded-lg p-6" x-data="cartApp()">
    <h1 class="text-2xl font-bold mb-6">Giỏ Hàng</h1>
    
    <template x-if="items.length === 0">
        <div class="text-center py-10">
            <p class="text-gray-500 mb-4">Giỏ hàng của bạn đang trống.</p>
            <a href="/" class="bg-brand text-white px-6 py-2 rounded">Tiếp tục mua sắm</a>
        </div>
    </template>

    <template x-if="items.length > 0">
        <div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b">
                            <th class="py-4 pl-4 pr-2 w-12">
                                <input type="checkbox" :checked="isAllSelected" @change="toggleAll" class="w-5 h-5 text-brand rounded border-gray-300 focus:ring-brand">
                            </th>
                            <th class="py-4 font-semibold text-gray-600">Sản phẩm</th>
                            <th class="py-4 text-center font-semibold text-gray-600">Đơn giá</th>
                            <th class="py-4 text-center font-semibold text-gray-600">Số lượng</th>
                            <th class="py-4 text-right font-semibold text-gray-600">Thành tiền</th>
                            <th class="py-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="item in items" :key="item.id">
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="py-4 pl-4 pr-2">
                                    <input type="checkbox" :value="item.id" x-model="selectedItems" @change="recalculate()" class="w-5 h-5 text-brand rounded border-gray-300 focus:ring-brand">
                                </td>
                                <td class="py-4 flex items-center gap-4">
                                    <img :src="getImageUrl(item.display_image_url)" class="w-20 h-20 object-cover rounded shadow-sm border border-gray-200">
                                    <div>
                                        <p class="font-bold text-gray-800 text-lg" x-text="item.product_variant.product?.name"></p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Màu: <span class="font-medium" x-text="item.product_variant.color?.name"></span> | 
                                            Size: <span class="font-medium" x-text="item.product_variant.size?.name"></span>
                                        </p>
                                    </div>
                                </td>
                                <td class="py-4 text-center">
                                    <span class="text-gray-800" x-text="formatPrice(item.product_variant.sale_price || item.product_variant.price)"></span>
                                    <template x-if="item.product_variant.sale_price && item.product_variant.sale_price < item.product_variant.price">
                                        <div class="text-xs text-gray-400 line-through" x-text="formatPrice(item.product_variant.price)"></div>
                                    </template>
                                </td>
                                <td class="py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="updateQty(item.id, item.quantity - 1)" class="w-8 h-8 rounded-full border border-gray-300 hover:bg-gray-100 flex items-center justify-center transition-colors"><i class="bi bi-dash"></i></button>
                                        <span class="w-8 text-center font-medium" x-text="item.quantity"></span>
                                        <button @click="updateQty(item.id, item.quantity + 1)" class="w-8 h-8 rounded-full border border-gray-300 hover:bg-gray-100 flex items-center justify-center transition-colors"><i class="bi bi-plus"></i></button>
                                    </div>
                                </td>
                                <td class="py-4 text-right font-bold text-brand">
                                    <span x-text="formatPrice((item.product_variant.sale_price || item.product_variant.price) * item.quantity)"></span>
                                </td>
                                <td class="py-4 text-right">
                                    <button @click="confirmRemove(item.id)" class="text-gray-400 hover:text-red-500 transition-colors p-2 rounded-full hover:bg-red-50">
                                        <i class="bi bi-trash text-lg"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="mt-8 flex justify-end">
                <div class="w-full max-w-md bg-gray-50 p-6 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex justify-between mb-2 text-gray-600">
                        <span>Tạm tính:</span>
                        <span x-text="formatPrice(totalPrice)"></span>
                    </div>
                    <div class="flex justify-between mb-4 text-gray-600 border-b border-gray-200 pb-4">
                        <span>Phí vận chuyển:</span>
                        <span>Miễn phí</span>
                    </div>
                    <div class="flex justify-between mb-6">
                        <span class="font-bold text-gray-800 text-lg">Tổng cộng:</span>
                        <span class="font-bold text-2xl text-brand" x-text="formatPrice(totalPrice)"></span>
                    </div>
                    <!-- Checkout Button -->
                    <button @click="prepareCheckout()" :disabled="selectedItems.length === 0 || isPreparingCheckout" class="w-full bg-brand text-white font-bold py-4 rounded-xl shadow-md hover:bg-[#d62800] transition-colors flex items-center justify-center gap-2 uppercase tracking-wide disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="bi bi-arrow-repeat animate-spin text-xl" x-show="isPreparingCheckout" style="display: none;"></i>
                        <span x-text="isPreparingCheckout ? 'Đang xử lý...' : 'Tiến hành thanh toán'"></span> <i x-show="!isPreparingCheckout" class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- Modal Xác nhận xóa -->
    <div x-show="showConfirmModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showConfirmModal" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showConfirmModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div x-show="showConfirmModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="bi bi-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Xóa sản phẩm
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng không? Hành động này không thể hoàn tác.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="proceedRemove()" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Xóa sản phẩm
                    </button>
                    <button @click="showConfirmModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Hủy
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cross-selling Section -->
@if(isset($crossSellProducts) && $crossSellProducts->count() > 0)
<div class="mt-12 bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-bold mb-6 text-gray-800 border-b pb-2">Có thể bạn sẽ thích</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        @foreach($crossSellProducts as $product)
        <div class="group border border-gray-100 rounded-xl p-4 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 bg-white flex flex-col justify-between">
            <div>
                <a href="{{ route('products.show', $product->slug) }}" class="block overflow-hidden rounded-lg relative">
                    <img src="{{ $product->primary_image_url ? (str_starts_with($product->primary_image_url, 'http') ? $product->primary_image_url : asset('storage/' . $product->primary_image_url)) : 'https://via.placeholder.com/300' }}" 
                         alt="{{ $product->name }}" 
                         class="w-full aspect-[3/4] object-cover transform group-hover:scale-110 transition-transform duration-500 ease-in-out">
                    @if($product->variants && $product->variants->min('sale_price') < $product->variants->min('price'))
                        <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">-{{ round((1 - $product->variants->min('sale_price') / $product->variants->min('price')) * 100) }}%</span>
                    @endif
                </a>
                <div class="mt-4">
                    <a href="{{ route('products.show', $product->slug) }}" class="text-sm font-semibold text-gray-800 hover:text-brand line-clamp-2">
                        {{ $product->name }}
                    </a>
                    <div class="mt-2 flex items-baseline gap-2">
                        @php
                            $minPrice = $product->variants ? $product->variants->min('price') : 0;
                            $minSalePrice = $product->variants ? $product->variants->min('sale_price') : null;
                        @endphp
                        @if($minSalePrice && $minSalePrice < $minPrice)
                            <span class="text-brand font-bold">{{ number_format($minSalePrice, 0, ',', '.') }}đ</span>
                            <span class="text-gray-400 text-xs line-through">{{ number_format($minPrice, 0, ',', '.') }}đ</span>
                        @else
                            <span class="text-brand font-bold">{{ number_format($minPrice, 0, ',', '.') }}đ</span>
                        @endif
                    </div>
                </div>
            </div>
            <a href="{{ route('products.show', $product->slug) }}" class="mt-4 block w-full text-center border border-brand text-brand hover:bg-brand hover:text-white py-2 rounded transition-colors text-sm font-medium">
                Xem chi tiết
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
function cartApp() {
    return {
        items: @json($cart->items ?? []),
        totalPrice: 0, // Sẽ được tính lại trong hàm init
        selectedItems: [],
        showConfirmModal: false,
        itemToRemove: null,
        isPreparingCheckout: false,
        
        init() {
            // Mặc định chọn tất cả khi mới load trang
            this.selectedItems = this.items.map(i => i.id);
            this.recalculate();
        },
        
        get isAllSelected() {
            return this.items.length > 0 && this.selectedItems.length === this.items.length;
        },
        
        toggleAll(e) {
            if (e.target.checked) {
                this.selectedItems = this.items.map(i => i.id);
            } else {
                this.selectedItems = [];
            }
            this.recalculate();
        },
        
        formatPrice(price) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
        },

        getImageUrl(url) {
            if (!url) return 'https://via.placeholder.com/80';
            if (url.startsWith('http')) return url;
            return '/storage/' + url;
        },
        
        async updateQty(itemId, quantity) {
            if (quantity < 1) {
                this.confirmRemove(itemId);
                return;
            }
            
            try {
                const response = await fetch('{{ route('cart.update') }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ cart_item_id: itemId, quantity: quantity })
                });
                
                const data = await response.json();
                if (data.success) {
                    const item = this.items.find(i => i.id === itemId);
                    item.quantity = quantity;
                    this.recalculate();
                    this.updateHeaderBadge(data.cart_quantity);
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message, type: 'success' } }));
                } else {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message || 'Có lỗi xảy ra', type: 'error' } }));
                }
            } catch (error) {
                console.error(error);
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Lỗi kết nối!', type: 'error' } }));
            }
        },
        
        confirmRemove(itemId) {
            this.itemToRemove = itemId;
            this.showConfirmModal = true;
        },
        
        async proceedRemove() {
            if (!this.itemToRemove) return;
            const itemId = this.itemToRemove;
            this.showConfirmModal = false;
            this.itemToRemove = null;
            
            try {
                const response = await fetch('{{ route('cart.remove') }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ cart_item_id: itemId })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.items = this.items.filter(i => i.id !== itemId);
                    this.recalculate();
                    this.updateHeaderBadge(data.cart_quantity);
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message, type: 'success' } }));
                } else {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message || 'Có lỗi xảy ra', type: 'error' } }));
                }
            } catch (error) {
                console.error(error);
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Lỗi kết nối!', type: 'error' } }));
            }
        },
        
        recalculate() {
            this.totalPrice = this.items.filter(item => this.selectedItems.includes(item.id)).reduce((sum, item) => {
                const price = item.product_variant.sale_price || item.product_variant.price;
                return sum + (price * item.quantity);
            }, 0);
        },
        
        async prepareCheckout() {
            if (this.selectedItems.length === 0) return;
            
            this.isPreparingCheckout = true;
            try {
                const response = await fetch('{{ route('checkout.prepare') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ selected_items: this.selectedItems })
                });
                
                const data = await response.json();
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    this.isPreparingCheckout = false;
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message || 'Có lỗi xảy ra', type: 'error' } }));
                }
            } catch (error) {
                console.error(error);
                this.isPreparingCheckout = false;
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Lỗi kết nối!', type: 'error' } }));
            }
        },
        
        updateHeaderBadge(qty) {
            const badge = document.getElementById('header-cart-badge');
            if (badge) {
                badge.innerText = qty;
                if (qty > 0) {
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
        }
    }
}
</script>
@endpush
