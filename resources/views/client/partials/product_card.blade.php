@php
    $primaryVariant = $product->variants->first(); // Lấy variant đầu tiên làm đại diện
    $imgUrl = $product->primary_image_url ?? ($primaryVariant->thumbnail_url ?? 'https://via.placeholder.com/300');
    if ($imgUrl && !Str::startsWith($imgUrl, ['http://', 'https://'])) {
        $imgUrl = Storage::url($imgUrl);
    }
    $price = $overridePrice ?? ($primaryVariant ? ($primaryVariant->sale_price ?? $primaryVariant->price) : 0);
    $oldPrice = $primaryVariant ? $primaryVariant->price : 0;
    
    // Lấy danh sách các màu độc nhất của sản phẩm
    $colors = $product->variants->map(function($v) { return $v->color; })->filter()->unique('id');
    
    // Dữ liệu variants để AlpineJS dùng đổi ảnh
    $variantsData = $product->variants->map(function($v) {
        $url = $v->thumbnail_url ?? 'https://via.placeholder.com/300';
        if ($url && !Str::startsWith($url, ['http://', 'https://'])) {
            $url = Storage::url($url);
        }
        return [
            'variant_id' => $v->id,
            'color_id' => $v->color_id,
            'image' => $url
        ];
    })->values()->toJson();
@endphp
<div x-data="{ 
        activeImage: '{{ $imgUrl }}', 
        activeColor: {{ $primaryVariant ? ($primaryVariant->color_id ?? 'null') : 'null' }},
        activeVariantId: {{ $primaryVariant ? $primaryVariant->id : 'null' }},
        variants: {{ $variantsData }},
        isAdding: false,
        isWishlisted: {{ in_array($product->id, $wishlistedProductIds ?? []) ? 'true' : 'false' }},
        isTogglingWishlist: false,
        changeColor(colorId) {
            this.activeColor = colorId;
            let v = this.variants.find(x => x.color_id === colorId);
            if(v) {
                if(v.image) this.activeImage = v.image;
                this.activeVariantId = v.variant_id;
            }
        },
        addToCart() {
            if(!this.activeVariantId) {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Sản phẩm chưa có phân loại!', type: 'error' } }));
                return;
            }
            this.isAdding = true;
            fetch('{{ route('cart.add') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    variant_id: this.activeVariantId,
                    quantity: 1
                })
            })
            .then(res => res.json())
            .then(data => {
                this.isAdding = false;
                if(data.success || data.cart_quantity) {
                    const badge = document.getElementById('header-cart-badge');
                    if(badge && data.cart_quantity !== undefined) {
                        badge.textContent = data.cart_quantity;
                        badge.classList.remove('hidden');
                    }
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message || 'Đã thêm vào giỏ hàng!', type: 'success' } }));
                } else {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message || 'Có lỗi xảy ra', type: 'error' } }));
                }
            })
            .catch(err => {
                this.isAdding = false;
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Lỗi kết nối!', type: 'error' } }));
            });
        },
        async toggleWishlist(productId) {
            if (this.isTogglingWishlist) return;
            this.isTogglingWishlist = true;
            
            try {
                const response = await fetch('{{ route('wishlist.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId })
                });
                
                if (response.status === 401) {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Vui lòng đăng nhập để sử dụng tính năng Yêu thích!', type: 'error' } }));
                    this.isTogglingWishlist = false;
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    this.isWishlisted = data.is_wishlisted;
                    window.dispatchEvent(new CustomEvent('wishlist-updated', { detail: { count: data.wishlist_count } }));
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message, type: 'success' } }));
                } else {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message || 'Có lỗi xảy ra', type: 'error' } }));
                }
            } catch (error) {
                console.error(error);
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Lỗi kết nối!', type: 'error' } }));
            } finally {
                this.isTogglingWishlist = false;
            }
        }
    }" class="group bg-white rounded-xl shadow-sm hover:shadow-xl transition-shadow border border-gray-100 overflow-hidden flex flex-col h-full">
    <div class="relative bg-white overflow-hidden aspect-[3/4] border-b border-gray-100 shrink-0 flex items-center justify-center">
        <!-- Background Frame Image -->
        @php
            $productFrame = \App\Models\Setting::get('product_frame', 'images/White Brown Abstract Border Frame Blank Document A4.png');
            $frameUrl = str_starts_with($productFrame, 'storage/') ? asset($productFrame) : asset($productFrame);
        @endphp
        <img src="{{ $frameUrl }}" 
             alt="Frame" 
             class="absolute inset-0 w-full h-full object-fill z-10 pointer-events-none"
             style="mix-blend-mode: multiply;">

        <div class="relative w-full h-full p-2 bg-white z-0 overflow-hidden">
            <a href="{{ route('products.show', $product->slug) }}" class="block w-full h-full">
                <img :src="activeImage" alt="{{ $product->name }}" class="w-full h-full object-cover rounded group-hover:scale-105 transition-transform duration-500 bg-white">
            </a>
        </div>
        
        @if(isset($isFlashSale) && $isFlashSale)
        <div class="absolute top-2 left-2 bg-brand text-white text-xs font-bold px-2 py-1 rounded shadow z-20">FLASH SALE</div>
        @endif
        
        <!-- Nút hành động hiện khi hover -->
        <div class="absolute bottom-8 left-0 right-0 flex justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity transform translate-y-4 group-hover:translate-y-0 duration-300 z-20">
            <button @click.prevent="toggleWishlist({{ $product->id }})" class="bg-white text-gray-800 hover:text-brand hover:bg-gray-50 w-10 h-10 rounded-full flex items-center justify-center shadow-md transition-colors" title="Thêm vào danh sách yêu thích">
                <i class="bi" :class="isWishlisted ? 'bi-heart-fill text-red-500' : 'bi-heart'"></i>
            </button>
            <button @click.prevent="addToCart()" :disabled="isAdding" class="bg-brand text-white hover:bg-[#C2185B] px-4 h-10 rounded-full font-medium shadow-md flex items-center gap-2 disabled:opacity-75 disabled:cursor-wait">
                <i class="bi bi-cart-plus" x-show="!isAdding"></i> 
                <i class="bi bi-arrow-repeat animate-spin" x-show="isAdding" style="display: none;"></i>
                <span x-text="isAdding ? 'Đang thêm...' : 'Add To Cart'"></span>
            </button>
        </div>
    </div>
    
    <div class="text-left p-4 flex flex-col flex-grow">
        <a href="{{ route('products.show', $product->slug) }}">
            <h3 class="font-medium text-gray-800 hover:text-brand transition-colors line-clamp-2">{{ $product->name }}</h3>
        </a>
        
        <!-- Hiển thị các màu hiện có của sản phẩm -->
        @if($colors->count() > 0)
        <div class="flex items-center justify-start gap-2 mt-2">
            @foreach($colors as $color)
                <button type="button" @click.prevent="changeColor({{ $color->id }})"
                        :class="{'ring-2 ring-brand ring-offset-2': activeColor === {{ $color->id }} }"
                        class="w-6 h-6 rounded-full border border-gray-300 shadow-sm transition-all focus:outline-none hover:scale-110" 
                        style="background-color: {{ $color->hex_code ?? '#cccccc' }}" 
                        title="{{ $color->name }}"></button>
            @endforeach
        </div>
        @endif
        
        <div class="mt-auto pt-3 flex items-center justify-start gap-2">
            <span class="font-bold text-brand">{{ number_format($price, 0, ',', '.') }}đ</span>
            @if($price < $oldPrice)
                <span class="text-sm text-gray-400 line-through">{{ number_format($oldPrice, 0, ',', '.') }}đ</span>
            @endif
        </div>
        
        <!-- Thanh tiến độ đã bán (tùy chọn) -->
        @if(isset($item) && isset($item->sold_quantity) && isset($item->quantity))
        @php
            $percent = $item->quantity > 0 ? min(100, round(($item->sold_quantity / $item->quantity) * 100)) : 0;
        @endphp
        <div class="mt-3 relative w-full bg-red-100 h-4 rounded-full overflow-hidden flex items-center justify-center">
            <div class="absolute top-0 left-0 h-full bg-brand rounded-full" style="width: {{ $percent }}%"></div>
            <span class="relative z-10 text-[10px] font-bold text-white leading-none">Đã bán {{ $item->sold_quantity }}</span>
        </div>
        @endif
    </div>
</div>
