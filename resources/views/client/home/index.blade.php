@extends('layouts.client')

@section('full_width_top')
<!-- Hero Banner Carousel -->
<div x-data="carousel()" x-init="init()" class="relative w-full overflow-hidden h-[300px] md:h-[500px]">
    <!-- Slides -->
    <div class="relative w-full h-full">
        <template x-for="(slide, index) in slides" :key="index">
            <div x-show="activeSlide === index"
                 x-transition.opacity.duration.500ms
                 class="absolute inset-0 w-full h-full">
                <template x-if="slide.link">
                    <a :href="slide.link" class="block w-full h-full">
                        <img :src="slide.image" class="w-full h-full object-cover object-center" alt="Banner">
                    </a>
                </template>
                <template x-if="!slide.link">
                    <img :src="slide.image" class="w-full h-full object-cover object-center" alt="Banner">
                </template>
            </div>
        </template>
    </div>



    <!-- Indicators -->
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2">
        <template x-for="(slide, index) in slides" :key="index">
            <button @click="goTo(index)"
                    :class="{'bg-white opacity-100': activeSlide === index, 'bg-white opacity-50': activeSlide !== index}"
                    class="w-2.5 h-2.5 rounded-full transition-all duration-300 shadow-sm">
            </button>
        </template>
    </div>
</div>
@endsection

@section('content')

<!-- Danh mục mức 2 -->
@if(isset($homeCategories) && $homeCategories->count() > 0)
<section class="mb-12 mt-12 md:mt-16">
    <div class="grid grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-12 md:gap-y-16">
        @foreach($homeCategories as $category)
        <a href="#" class="group relative bg-[#f4f6f8] rounded-xl flex items-center p-5 md:p-8 hover:shadow-lg transition-all duration-300 h-28 md:h-36">
            <!-- Tên danh mục -->
            <span class="font-bold text-gray-800 uppercase tracking-wide group-hover:text-brand transition-colors relative z-10 text-sm md:text-lg w-1/2 md:w-3/5 break-words">
                {{ $category->name }}
            </span>
            
            <!-- Ảnh nổi lên -->
            <div class="absolute right-2 md:right-6 bottom-0 w-24 h-32 md:w-32 md:h-44 bg-white rounded-t-xl md:rounded-xl shadow-[0_-4px_15px_-3px_rgba(0,0,0,0.1)] md:shadow-lg shrink-0 z-20 transition-transform duration-500 group-hover:-translate-y-3 p-2 flex items-center justify-center">
                @if($category->image)
                    <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="max-w-full max-h-full object-contain">
                @else
                    <div class="w-12 h-12 md:w-16 md:h-16 bg-gray-100 rounded-full flex items-center justify-center text-gray-400">
                        <i class="bi bi-image text-xl"></i>
                    </div>
                @endif
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif

<!-- Flash Sale Section -->
@if($flashSale)
<section class="mb-16 p-4 md:p-6 rounded-2xl shadow-lg" style="background-color: #FF78AE;">
    <div class="bg-white rounded-xl p-4 md:p-5 mb-6 flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3">
            <h2 class="text-2xl font-bold text-gray-900 uppercase">{{ $flashSale->name ?? 'FLASH SALE' }}</h2>
        </div>
        <!-- Countdown Timer -->
        <div x-data="countdown('{{ $flashSale->end_time->toIso8601String() }}')" class="flex gap-2 text-white font-bold text-lg items-center">
            <span class="text-gray-600 text-sm font-medium mr-2 hidden md:inline">Kết thúc trong:</span>
            <div class="bg-gray-900 rounded px-3 py-1 shadow min-w-[40px] text-center" x-text="hours">00</div><span class="text-gray-900 font-black">:</span>
            <div class="bg-gray-900 rounded px-3 py-1 shadow min-w-[40px] text-center" x-text="minutes">00</div><span class="text-gray-900 font-black">:</span>
            <div class="bg-gray-900 rounded px-3 py-1 shadow min-w-[40px] text-center" x-text="seconds">00</div>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
        @foreach($flashSale->items as $item)
        @php
            $product = $item->product;
            $primaryVariant = $product->variants->first(); // Lấy variant đại diện
            $imgUrl = $product->primary_image_url ?? ($primaryVariant->thumbnail_url ?? 'https://via.placeholder.com/300');
            if ($imgUrl && !Str::startsWith($imgUrl, ['http://', 'https://'])) {
                $imgUrl = Storage::url($imgUrl);
            }
            
            $price = $item->flash_sale_price;
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
                isWishlisted: {{ in_array($product->id, $wishlistedProductIds) ? 'true' : 'false' }},
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
                
                <div class="absolute top-2 left-2 bg-brand text-white text-xs font-bold px-2 py-1 rounded shadow z-20">FLASH SALE</div>
                
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
                @if(isset($item->sold_quantity) && isset($item->quantity))
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
        @endforeach
    </div>
</section>
@endif

<!-- Bộ Sưu Tập (Collections) -->
<section class="mb-16">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-bold text-gray-900 border-l-4 border-brand pl-3">BỘ SƯU TẬP</h2>
        <a href="#" class="text-brand hover:underline font-medium">Xem tất cả <i class="bi bi-arrow-right"></i></a>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
        @foreach($collections as $collection)
        <a href="{{ route('collections.show', $collection->slug) }}" class="relative aspect-[3/4] rounded-xl overflow-hidden group shadow">
            @if($collection->image)
                <img src="{{ Storage::url($collection->image) }}" alt="{{ $collection->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
            @else
                <img src="https://via.placeholder.com/400x300?text={{ urlencode($collection->name) }}" alt="{{ $collection->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
            <div class="absolute bottom-4 left-4 right-4">
                <h3 class="text-white font-bold text-xl">{{ $collection->name }}</h3>
                <p class="text-gray-200 text-sm mt-1 opacity-0 group-hover:opacity-100 transition-opacity transform translate-y-2 group-hover:translate-y-0 duration-300">Khám phá ngay</p>
            </div>
        </a>
        @endforeach
    </div>
</section>

<!-- Sản phẩm Nổi bật -->
<section class="mb-16">
    <div class="text-center mb-10">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Sản Phẩm Nổi Bật</h2>
        <div class="w-16 h-1 bg-brand mx-auto rounded"></div>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
        @foreach($featuredProducts as $product)
        @php
            $primaryVariant = $product->variants->first(); // Lấy variant đầu tiên làm đại diện
            $imgUrl = $product->primary_image_url ?? ($primaryVariant->thumbnail_url ?? 'https://via.placeholder.com/300');
            if ($imgUrl && !Str::startsWith($imgUrl, ['http://', 'https://'])) {
                $imgUrl = Storage::url($imgUrl);
            }
            $price = $primaryVariant ? ($primaryVariant->sale_price ?? $primaryVariant->price) : 0;
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
                isWishlisted: {{ in_array($product->id, $wishlistedProductIds) ? 'true' : 'false' }},
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
                        if(data.success || data.cart_quantity) { // Laravel might return just cart_quantity or success true
                            // Cập nhật số lượng trên icon giỏ hàng header
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
            </div>
        </div>
        @endforeach
    </div>
</section>

<!-- Tạp chí thời trang / Lookbook -->
@if(isset($lookbooks) && $lookbooks->count() > 0)
<section class="mb-16">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-bold text-gray-900 border-l-4 border-brand pl-3">AURELIA STYLE</h2>
        <a href="{{ url('/posts') }}" class="text-brand hover:underline font-medium">Xem tất cả <i class="bi bi-arrow-right"></i></a>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($lookbooks as $post)
        <a href="{{ url('/' . $post->slug . '-p' . $post->id . '.html') }}" class="group block relative rounded-xl overflow-hidden aspect-[3/4] shadow-sm hover:shadow-lg transition-all duration-300">
            @if($post->image)
                <img src="{{ Storage::url($post->image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
            @else
                <img src="https://via.placeholder.com/400x533?text=Aurelia+Style" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
            @endif
            
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-80 group-hover:opacity-100 transition-opacity"></div>
            
            <div class="absolute bottom-0 left-0 right-0 p-4 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                <span class="text-brand text-xs font-bold uppercase tracking-wider mb-2 block">{{ $post->published_at ? $post->published_at->format('d/m/Y') : 'Mới nhất' }}</span>
                <h3 class="text-white font-bold text-lg leading-tight line-clamp-2 mb-2">{{ $post->title }}</h3>
                <p class="text-gray-200 text-sm line-clamp-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-100">{{ $post->excerpt }}</p>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif

<!-- Ý kiến khách hàng (Testimonials) -->
<section class="py-16 bg-gray-50 rounded-2xl mb-16 -mx-4 px-4 sm:mx-0 sm:px-8">
    <div class="text-center mb-10">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Khách Hàng Nói Gì</h2>
        <div class="w-16 h-1 bg-brand mx-auto rounded"></div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Mockup Card 1 -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 text-center relative mt-6">
            <div class="absolute -top-6 left-1/2 -translate-x-1/2">
                <img src="https://i.pravatar.cc/150?img=1" alt="Avatar" class="w-12 h-12 rounded-full border-2 border-white shadow-md">
            </div>
            <div class="text-yellow-400 text-sm mb-3 mt-4">
                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
            </div>
            <p class="text-gray-600 italic mb-4 text-sm leading-relaxed">"Chất liệu vải cực kỳ thoải mái, thiết kế trẻ trung. Giao hàng rất nhanh chóng và đóng gói cẩn thận. Chắc chắn sẽ quay lại ủng hộ shop!"</p>
            <h4 class="font-bold text-gray-900">Nguyễn Văn A</h4>
            <span class="text-xs text-gray-400">Hà Nội</span>
        </div>

        <!-- Mockup Card 2 -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-brand text-center relative mt-6 transform md:-translate-y-4 shadow-md">
            <div class="absolute -top-6 left-1/2 -translate-x-1/2">
                <img src="https://i.pravatar.cc/150?img=5" alt="Avatar" class="w-14 h-14 rounded-full border-2 border-brand shadow-md">
            </div>
            <div class="text-yellow-400 text-sm mb-3 mt-6">
                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
            </div>
            <p class="text-gray-600 italic mb-4 text-sm leading-relaxed">"Mình đã mua rất nhiều nơi nhưng Form áo ở đây thực sự đỉnh. Tôn dáng, không bị bai nhão sau khi giặt. Dịch vụ cskh quá tuyệt."</p>
            <h4 class="font-bold text-gray-900">Trần Thị B</h4>
            <span class="text-xs text-gray-400">TP. Hồ Chí Minh</span>
        </div>

        <!-- Mockup Card 3 -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 text-center relative mt-6">
            <div class="absolute -top-6 left-1/2 -translate-x-1/2">
                <img src="https://i.pravatar.cc/150?img=3" alt="Avatar" class="w-12 h-12 rounded-full border-2 border-white shadow-md">
            </div>
            <div class="text-yellow-400 text-sm mb-3 mt-4">
                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i>
            </div>
            <p class="text-gray-600 italic mb-4 text-sm leading-relaxed">"Sản phẩm giống hình 100%, chất lượng vượt ngoài mong đợi so với giá tiền. Cảm ơn shop đã tư vấn size rất nhiệt tình."</p>
            <h4 class="font-bold text-gray-900">Lê Văn C</h4>
            <span class="text-xs text-gray-400">Đà Nẵng</span>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // AlpineJS component for Hero Carousel
    function carousel() {
        return {
            activeSlide: 0,
            slides: {!! json_encode($banners->map(function($b) {
                return [
                    'image' => $b->display_image_url,
                    'link' => $b->link
                ];
            })) !!},
            interval: null,
            init() {
                this.startAutoplay();
            },
            startAutoplay() {
                this.stopAutoplay(); // Xóa timer cũ nếu có để tránh chạy đè 2 lần
                this.interval = setInterval(() => {
                    this.next();
                }, 5000);
            },
            stopAutoplay() {
                clearInterval(this.interval);
            },
            next() {
                this.activeSlide = this.activeSlide === this.slides.length - 1 ? 0 : this.activeSlide + 1;
            },
            prev() {
                this.activeSlide = this.activeSlide === 0 ? this.slides.length - 1 : this.activeSlide - 1;
            },
            goTo(index) {
                this.activeSlide = index;
                this.stopAutoplay();
                this.startAutoplay(); // Reset timer
            }
        }
    }

    // AlpineJS component for Flash Sale Countdown
    function countdown(endTimeStr) {
        return {
            hours: '00',
            minutes: '00',
            seconds: '00',
            interval: null,
            init() {
                const end = new Date(endTimeStr).getTime();
                this.update(end);
                this.interval = setInterval(() => {
                    this.update(end);
                }, 1000);
            },
            update(end) {
                const now = new Date().getTime();
                const distance = end - now;
                
                if (distance < 0) {
                    clearInterval(this.interval);
                    this.hours = '00';
                    this.minutes = '00';
                    this.seconds = '00';
                    return;
                }
                
                // Tính toán số giờ, phút, giây còn lại (bao gồm cả ngày đổi thành giờ)
                const d = Math.floor(distance / (1000 * 60 * 60 * 24));
                const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)) + (d * 24);
                const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((distance % (1000 * 60)) / 1000);
                
                this.hours = String(h).padStart(2, '0');
                this.minutes = String(m).padStart(2, '0');
                this.seconds = String(s).padStart(2, '0');
            }
        }
    }
</script>
@endpush
