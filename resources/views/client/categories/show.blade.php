@extends('layouts.client')

@section('title', $category->name . ' — Aurelia')

@push('styles')
<style>
/* Custom scrollbar for filter list */
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0; 
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1; 
}
</style>
@endpush
@section('full_width_top')
{{-- Category Banner --}}
<section class="bg-gray-900 py-12 md:py-16 relative overflow-hidden mb-8">
    @php
        $heroBannerUrl = $category->hero_banner_url;
    @endphp
    @if($heroBannerUrl)
        <div class="absolute inset-0">
            <img src="{{ $heroBannerUrl }}" alt="{{ $category->name }}" class="w-full h-full object-cover opacity-40">
            <div class="absolute inset-0 bg-gradient-to-b from-black/40 to-black/70"></div>
        </div>
    @endif
    <div class="container mx-auto px-4 relative z-10 text-center">
        <nav class="flex justify-center mb-4 text-sm text-gray-400" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-2">
                <li><a href="{{ url('/') }}" class="hover:text-white transition-colors">Trang chủ</a></li>
                <li><i class="bi bi-chevron-right text-gray-500 mx-1 text-xs"></i></li>
                @if($category->parent)
                    <li><a href="{{ route('categories.show', $category->parent->slug) }}" class="hover:text-white transition-colors">{{ $category->parent->name }}</a></li>
                    <li><i class="bi bi-chevron-right text-gray-500 mx-1 text-xs"></i></li>
                @endif
                <li class="text-white font-medium">{{ $category->name }}</li>
            </ol>
        </nav>
        <h1 class="text-4xl md:text-5xl font-black text-white mb-3 tracking-tight">{{ $category->name }}</h1>
    </div>
</section>
@endsection

@section('content')

{{-- Main content: Sidebar + Grid --}}
<div class="container mx-auto px-4 pb-16" x-data="filterApp()">
    
    {{-- Mobile: Filter toggle button --}}
    <div class="flex items-center justify-between mb-4 lg:hidden">
        <span class="text-sm text-gray-600 font-medium">{{ $products->total() }} sản phẩm</span>
        <button @click="sidebarOpen = !sidebarOpen"
                class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm">
            <i class="bi bi-sliders text-brand"></i>
            Bộ lọc
            @if(request()->hasAny(['colors', 'sizes', 'price_min', 'price_max']))
                <span class="w-5 h-5 bg-brand text-white text-xs rounded-full flex items-center justify-center font-bold">!</span>
            @endif
        </button>
    </div>

    <div class="flex gap-8">
        {{-- SIDEBAR --}}
        <aside class="w-72 shrink-0 hidden lg:block">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sticky top-24 space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900 text-base"><i class="bi bi-sliders text-brand mr-2"></i>Bộ lọc sản phẩm</h3>
                    @if(request()->hasAny(['colors', 'sizes', 'price_min', 'price_max']))
                        <a href="{{ request()->url() }}" class="text-xs text-red-500 hover:text-red-700 font-medium">Xóa tất cả</a>
                    @endif
                </div>

                <form method="GET" action="{{ request()->url() }}" id="filter-form">
                    <input type="hidden" name="sort" value="{{ $sort }}">

                    {{-- Price Range --}}
                    <div class="space-y-4">
                        <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Khoảng giá</h4>
                        <div class="flex items-center justify-between text-sm font-medium text-gray-700">
                            <span x-text="formatPrice(priceMin)"></span>
                            <span x-text="formatPrice(priceMax)"></span>
                        </div>
                        
                        <div class="relative w-full h-8 flex items-center">
                            <!-- Track background -->
                            <div class="absolute left-0 right-0 h-1 bg-gray-200 rounded-full"></div>
                            
                            <!-- Active track -->
                            <div class="absolute h-1 bg-gray-900 rounded-full pointer-events-none" 
                                 :style="`left: ${absoluteMax > absoluteMin ? ((priceMin - absoluteMin) / (absoluteMax - absoluteMin)) * 100 : 0}%; right: ${absoluteMax > absoluteMin ? 100 - ((priceMax - absoluteMin) / (absoluteMax - absoluteMin)) * 100 : 0}%`"></div>
                            
                            <!-- Thumbs -->
                            <input type="range" name="price_min" :min="absoluteMin" :max="absoluteMax" step="10000" x-model="priceMin" @input="priceMin = Math.min(priceMin, priceMax)" 
                                   class="absolute w-full h-full appearance-none bg-transparent pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:h-5 [&::-webkit-slider-thumb]:bg-white [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-gray-900 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:shadow-md [&::-webkit-slider-thumb]:cursor-grab [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:w-5 [&::-moz-range-thumb]:h-5 [&::-moz-range-thumb]:bg-white [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-gray-900 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:shadow-md [&::-moz-range-thumb]:cursor-grab z-10">
                                   
                            <input type="range" name="price_max" :min="absoluteMin" :max="absoluteMax" step="10000" x-model="priceMax" @input="priceMax = Math.max(priceMax, priceMin)" 
                                   class="absolute w-full h-full appearance-none bg-transparent pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:h-5 [&::-webkit-slider-thumb]:bg-white [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-gray-900 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:shadow-md [&::-webkit-slider-thumb]:cursor-grab [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:w-5 [&::-moz-range-thumb]:h-5 [&::-moz-range-thumb]:bg-white [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-gray-900 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:shadow-md [&::-moz-range-thumb]:cursor-grab z-20">
                        </div>
                    </div>

                    <hr class="border-gray-100 my-4">

                    {{-- Color Filter --}}
                    @if($allColors->count() > 0)
                    <div class="space-y-4">
                        <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Màu sắc</h4>
                        <div class="space-y-3 max-h-56 overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($allColors as $color)
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center justify-center">
                                    <input type="checkbox" name="colors[]" value="{{ $color->id }}" {{ in_array($color->id, (array) request('colors', [])) ? 'checked' : '' }} class="peer sr-only">
                                    <div class="w-5 h-5 rounded border border-gray-300 peer-checked:bg-gray-900 peer-checked:border-gray-900 transition-colors flex items-center justify-center">
                                        <i class="bi bi-check text-white text-sm opacity-0 peer-checked:opacity-100"></i>
                                    </div>
                                </div>
                                <span class="w-5 h-5 rounded-full border border-gray-200 shadow-sm shrink-0" style="background-color: {{ $color->hex_code ?? '#cccccc' }}"></span>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors">{{ $color->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <hr class="border-gray-100 my-4">
                    @endif

                    {{-- Size Filter --}}
                    @if($allSizes->count() > 0)
                    <div class="space-y-4">
                        <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Kích thước</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($allSizes as $size)
                            <label for="size_cat_{{ $size->id }}" class="cursor-pointer">
                                <input type="checkbox" name="sizes[]" id="size_cat_{{ $size->id }}" value="{{ $size->id }}" {{ in_array($size->id, (array) request('sizes', [])) ? 'checked' : '' }} class="sr-only peer">
                                <span class="inline-flex items-center justify-center min-w-[44px] h-10 px-3 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 transition-all peer-checked:border-gray-900 peer-checked:bg-gray-900 peer-checked:text-white hover:border-gray-900 hover:text-gray-900">
                                    {{ $size->name }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <hr class="border-gray-100 my-4">
                    @endif

                    <button type="submit" class="w-full bg-gray-900 text-white font-bold py-3 rounded-xl hover:bg-brand transition-colors text-sm uppercase tracking-wide">
                        Áp dụng bộ lọc
                    </button>
                </form>
            </div>
        </aside>

        {{-- PRODUCT GRID --}}
        <div class="flex-1 min-w-0">
            {{-- Toolbar --}}
            <div class="mb-5 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <p class="text-sm text-gray-600 font-medium">
                        Hiển thị <strong>{{ $products->firstItem() }}–{{ $products->lastItem() }}</strong> trong tổng số <strong>{{ $products->total() }}</strong> sản phẩm
                    </p>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500 shrink-0">Sắp xếp:</span>
                        <select onchange="window.location.href = updateQueryParam('sort', this.value)" class="border-gray-200 rounded-xl text-sm py-2 pl-3 pr-8 focus:ring-brand focus:border-brand bg-white shadow-sm">
                            <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Mới nhất</option>
                            <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Giá: Thấp → Cao</option>
                            <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Giá: Cao → Thấp</option>
                            <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Tên: A → Z</option>
                        </select>
                    </div>
                </div>

                {{-- Active Filter Chips --}}
                @php
                    $activeFilters = [];
                    if(request()->filled('colors')) {
                        foreach((array)request('colors') as $cId) {
                            $c = $allColors->find($cId);
                            if($c) $activeFilters[] = ['label' => 'Màu: '.$c->name, 'param' => 'colors', 'value' => $cId];
                        }
                    }
                    if(request()->filled('sizes')) {
                        foreach((array)request('sizes') as $sId) {
                            $s = $allSizes->find($sId);
                            if($s) $activeFilters[] = ['label' => 'Size: '.$s->name, 'param' => 'sizes', 'value' => $sId];
                        }
                    }
                    if(request()->filled('price_min')) $activeFilters[] = ['label' => 'Từ '.number_format(request('price_min'),0,',','.').'đ', 'param' => 'price_min', 'value' => null];
                    if(request()->filled('price_max')) $activeFilters[] = ['label' => 'Đến '.number_format(request('price_max'),0,',','.').'đ', 'param' => 'price_max', 'value' => null];
                @endphp
                @if(count($activeFilters) > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($activeFilters as $filter)
                    <a href="{{ removeQueryParam($filter['param'], $filter['value']) }}" class="inline-flex items-center gap-1.5 bg-brand/10 text-brand text-xs font-semibold px-3 py-1.5 rounded-full hover:bg-brand/20 transition-colors border border-brand/20">
                        {{ $filter['label'] }} <i class="bi bi-x text-sm font-bold"></i>
                    </a>
                    @endforeach
                    <a href="{{ request()->url() }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-red-500 font-medium px-2 py-1.5 transition-colors">
                        <i class="bi bi-x-circle"></i> Xóa tất cả
                    </a>
                </div>
                @endif
            </div>

            @if($products->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-5">
                @foreach($products as $product)
                    @php
                        $primaryVariant = $product->variants->first();
                        $imgUrl = $product->primary_image_url ?? ($primaryVariant->thumbnail_url ?? 'https://via.placeholder.com/300');
                        if ($imgUrl && !Str::startsWith($imgUrl, ['http://', 'https://'])) { $imgUrl = Storage::url($imgUrl); }
                        $price = $primaryVariant ? ($primaryVariant->sale_price ?? $primaryVariant->price) : 0;
                        $oldPrice = $primaryVariant ? $primaryVariant->price : 0;
                        $isWishlisted = in_array($product->id, $wishlistedProductIds);
                        $colors = $product->variants->map(fn($v) => $v->color)->filter()->unique('id');
                        $variantsData = $product->variants->map(function($v) {
                            $url = $v->thumbnail_url ?? 'https://via.placeholder.com/300';
                            if ($url && !Str::startsWith($url, ['http://', 'https://'])) { $url = Storage::url($url); }
                            return ['variant_id' => $v->id, 'color_id' => $v->color_id, 'image' => $url];
                        })->values()->toJson();
                    @endphp
                    <div x-data="{
                            activeImage: '{{ $imgUrl }}',
                            activeColor: {{ $primaryVariant ? ($primaryVariant->color_id ?? 'null') : 'null' }},
                            activeVariantId: {{ $primaryVariant ? $primaryVariant->id : 'null' }},
                            variants: {{ $variantsData }},
                            isAdding: false,
                            isWishlisted: {{ $isWishlisted ? 'true' : 'false' }},
                            isTogglingWishlist: false,
                            changeColor(colorId) {
                                this.activeColor = colorId;
                                let v = this.variants.find(x => x.color_id === colorId);
                                if(v) { if(v.image) this.activeImage = v.image; this.activeVariantId = v.variant_id; }
                            },
                            addToCart() {
                                if(!this.activeVariantId) { window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Sản phẩm chưa có phân loại!', type: 'error' } })); return; }
                                this.isAdding = true;
                                fetch('{{ route('cart.add') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ variant_id: this.activeVariantId, quantity: 1 }) })
                                .then(r => r.json()).then(data => {
                                    this.isAdding = false;
                                    if(data.success || data.cart_quantity) { const badge = document.getElementById('header-cart-badge'); if(badge && data.cart_quantity !== undefined) { badge.textContent = data.cart_quantity; badge.classList.remove('hidden'); } window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Đã thêm vào giỏ hàng!', type: 'success' } })); }
                                    else { window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message || 'Có lỗi xảy ra', type: 'error' } })); }
                                }).catch(() => { this.isAdding = false; });
                            },
                            async toggleWishlist(productId) {
                                if (this.isTogglingWishlist) return; this.isTogglingWishlist = true;
                                try {
                                    const resp = await fetch('{{ route('wishlist.toggle') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: JSON.stringify({ product_id: productId }) });
                                    if (resp.status === 401) { window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Vui lòng đăng nhập!', type: 'error' } })); return; }
                                    const data = await resp.json();
                                    if (data.success) { this.isWishlisted = data.is_wishlisted; window.dispatchEvent(new CustomEvent('wishlist-updated', { detail: { count: data.wishlist_count } })); window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message, type: 'success' } })); }
                                } finally { this.isTogglingWishlist = false; }
                            }
                        }" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden flex flex-col h-full">
                        <div class="relative bg-white overflow-hidden aspect-[3/4] shrink-0">
                            @php
                                $productFrame = \App\Models\Setting::get('product_frame', 'images/White Brown Abstract Border Frame Blank Document A4.png');
                            @endphp
                            <img src="{{ asset($productFrame) }}" alt="" class="absolute inset-0 w-full h-full object-fill z-10 pointer-events-none" style="mix-blend-mode: multiply;">
                            <div class="absolute top-2 left-2 z-20 flex flex-col gap-1">
                                @if($oldPrice > $price && $oldPrice > 0)
                                    <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-full shadow-sm">-{{ round((($oldPrice - $price) / $oldPrice) * 100) }}%</span>
                                @endif
                            </div>
                            <button @click.prevent="toggleWishlist({{ $product->id }})" class="absolute top-2 right-2 z-20 w-9 h-9 rounded-full bg-white/90 flex items-center justify-center shadow-sm transition-all hover:scale-110" :class="isWishlisted ? 'text-red-500' : 'text-gray-400 hover:text-red-400'">
                                <i class="bi text-base" :class="isWishlisted ? 'bi-heart-fill' : 'bi-heart'"></i>
                            </button>
                            <div class="relative w-full h-full p-2 bg-white z-0 overflow-hidden">
                                <a href="{{ route('products.show', $product->slug) }}" class="block w-full h-full">
                                    <img :src="activeImage" alt="{{ $product->name }}" class="w-full h-full object-cover rounded transition-transform duration-700 group-hover:scale-105">
                                </a>
                            </div>
                            <div class="absolute bottom-4 left-0 right-0 flex justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 z-20">
                                <button @click.prevent="addToCart()" :disabled="isAdding" class="bg-gray-900 text-white px-5 h-10 rounded-full font-medium shadow-lg flex items-center gap-2 hover:bg-brand transition-colors text-sm">
                                    <i class="bi bi-cart-plus" x-show="!isAdding"></i>
                                    <i class="bi bi-arrow-repeat animate-spin" x-show="isAdding" style="display: none;"></i>
                                    <span x-text="isAdding ? 'Đang thêm...' : 'Thêm giỏ hàng'"></span>
                                </button>
                            </div>
                        </div>
                        <div class="p-4 flex flex-col flex-grow">
                            <a href="{{ route('products.show', $product->slug) }}" class="block mb-2">
                                <h3 class="font-semibold text-gray-800 group-hover:text-brand transition-colors line-clamp-2 text-sm leading-snug">{{ $product->name }}</h3>
                            </a>
                            @if($colors->count() > 0)
                            <div class="flex items-center gap-1.5 mb-3">
                                @foreach($colors as $color)
                                <button @click.prevent="changeColor({{ $color->id }})" :class="{'ring-2 ring-brand ring-offset-1 scale-110': activeColor === {{ $color->id }} }" class="w-5 h-5 rounded-full border border-gray-200 shadow-sm transition-all hover:scale-110 focus:outline-none" style="background-color: {{ $color->hex_code ?? '#cccccc' }}" title="{{ $color->name }}"></button>
                                @endforeach
                            </div>
                            @endif
                            <div class="mt-auto flex items-baseline gap-2">
                                <span class="font-bold text-brand text-base">{{ number_format($price, 0, ',', '.') }}đ</span>
                                @if($price < $oldPrice)
                                    <span class="text-xs text-gray-400 line-through">{{ number_format($oldPrice, 0, ',', '.') }}đ</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-10 flex justify-center">{{ $products->links() }}</div>
            @else
            <div class="text-center py-24 bg-white rounded-2xl shadow-sm border border-gray-100">
                <i class="bi bi-search text-5xl text-gray-200 mb-4 block"></i>
                <h3 class="text-xl font-bold text-gray-700 mb-2">Không tìm thấy sản phẩm phù hợp</h3>
                <p class="text-gray-500 mb-6">Thử xóa bộ lọc để xem thêm sản phẩm.</p>
                <a href="{{ request()->url() }}" class="inline-block px-6 py-2.5 bg-brand text-white rounded-full font-medium text-sm">Xóa bộ lọc</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function filterApp() {
        return {
            sidebarOpen: false,
            priceMin: {{ request('price_min', $priceStats->min_price ?? 0) }},
            priceMax: {{ request('price_max', $priceStats->max_price ?? 5000000) }},
            absoluteMin: {{ $priceStats->min_price ?? 0 }},
            absoluteMax: {{ $priceStats->max_price ?? 5000000 }},
            formatPrice(val) { return new Intl.NumberFormat('vi-VN').format(val) + 'đ'; }
        }
    }
    function updateQueryParam(key, value) {
        const url = new URL(window.location.href);
        url.searchParams.set(key, value);
        return url.toString();
    }
</script>
@endpush

@php
function removeQueryParam($param, $value = null) {
    $params = request()->query();
    if ($value === null) {
        unset($params[$param]);
    } else {
        if (isset($params[$param]) && is_array($params[$param])) {
            $params[$param] = array_values(array_filter($params[$param], fn($v) => $v != $value));
            if (empty($params[$param])) unset($params[$param]);
        }
    }
    return request()->url() . (count($params) ? '?' . http_build_query($params) : '');
}
@endphp
