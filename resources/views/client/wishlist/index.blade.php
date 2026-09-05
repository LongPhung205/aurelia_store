@extends('layouts.client')

@section('content')
<div class="bg-white shadow rounded-lg p-6" x-data="wishlistApp()">
    <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
        <i class="bi bi-heart-fill text-brand"></i> Danh sách Yêu thích
    </h1>
    
    <template x-if="items.length === 0">
        <div class="text-center py-10">
            <div class="text-gray-300 mb-4">
                <i class="bi bi-heart text-6xl"></i>
            </div>
            <p class="text-gray-500 mb-4">Bạn chưa có sản phẩm nào trong danh sách yêu thích.</p>
            <a href="/" class="bg-brand text-white px-6 py-2 rounded shadow hover:bg-[#d62800] transition-colors">Khám phá ngay</a>
        </div>
    </template>

    <template x-if="items.length > 0">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <template x-for="item in items" :key="item.id">
                <div class="group bg-white rounded-xl shadow-sm hover:shadow-xl transition-shadow border border-gray-100 overflow-hidden flex flex-col h-full relative">
                    
                    <!-- Nút xóa -->
                    <button @click="remove(item.product.id)" class="absolute top-2 right-2 bg-white/80 hover:bg-red-50 text-gray-400 hover:text-red-500 w-8 h-8 rounded-full flex items-center justify-center shadow-sm z-10 transition-colors">
                        <i class="bi bi-x-lg"></i>
                    </button>

                    <div class="relative bg-gray-100 overflow-hidden aspect-[3/4] border-b border-gray-100 shrink-0">
                        <img :src="getImageUrl(item.product.primary_image_url)" :alt="item.product.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                    
                    <div class="p-4 flex flex-col flex-grow">
                        <div class="text-xs text-brand font-semibold mb-1" x-text="item.product.categories ? item.product.categories[0]?.name : ''"></div>
                        <h3 class="font-medium text-gray-800 text-sm mb-2 line-clamp-2" x-text="item.product.name"></h3>
                        <div class="mt-auto pt-3 flex items-center justify-between">
                            <span class="font-bold text-gray-900" x-text="formatPrice(item.product.variants[0]?.sale_price || item.product.variants[0]?.price || 0)"></span>
                        </div>
                    </div>
                    
                    <!-- Nút xem chi tiết -->
                    <a :href="`/products/${item.product.slug}`" class="block text-center bg-gray-50 hover:bg-brand hover:text-white transition-colors text-sm font-medium py-3 border-t border-gray-100">
                        Tùy chọn & Mua
                    </a>
                </div>
            </template>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
function wishlistApp() {
    return {
        items: @json($wishlists ?? []),
        
        formatPrice(price) {
            if (!price) return '';
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
        },

        getImageUrl(url) {
            if (!url) return 'https://via.placeholder.com/300';
            if (url.startsWith('http')) return url;
            return '/storage/' + url;
        },
        
        async remove(productId) {
            try {
                const response = await fetch('{{ route('wishlist.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.items = this.items.filter(i => i.product.id !== productId);
                    window.dispatchEvent(new CustomEvent('wishlist-updated', { detail: { count: data.wishlist_count } }));
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message, type: 'success' } }));
                }
            } catch (error) {
                console.error(error);
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Lỗi kết nối!', type: 'error' } }));
            }
        }
    }
}
</script>
@endpush
