@extends('layouts.client')

@section('title', $collection->name)

@section('content')

<!-- Collection Banner -->
<section class="bg-gray-100 py-12 md:py-20 relative overflow-hidden">
    @if($collection->image)
        <div class="absolute inset-0">
            <img src="{{ Storage::url($collection->image) }}" alt="{{ $collection->name }}" class="w-full h-full object-cover blur-sm opacity-60">
            <div class="absolute inset-0 bg-black/40"></div>
        </div>
    @endif
    <div class="container mx-auto px-4 relative z-10 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4 {{ $collection->image ? 'text-white' : 'text-gray-900' }}">{{ $collection->name }}</h1>
        @if($collection->description)
            <p class="max-w-2xl mx-auto text-lg {{ $collection->image ? 'text-gray-200' : 'text-gray-600' }}">{{ $collection->description }}</p>
        @endif
        
        <nav class="flex justify-center mt-6 text-sm" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ url('/') }}" class="inline-flex items-center {{ $collection->image ? 'text-gray-300 hover:text-white' : 'text-gray-700 hover:text-brand' }}">
                        Trang chủ
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="bi bi-chevron-right text-gray-400 mx-1"></i>
                        <span class="{{ $collection->image ? 'text-white' : 'text-gray-500' }}">{{ $collection->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Collection Products -->
<section class="py-12">
    <div class="container mx-auto px-4">
        
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Sản phẩm trong bộ sưu tập ({{ $products->total() }})</h2>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Sắp xếp theo:</span>
                <select class="border-gray-300 rounded-lg text-sm focus:ring-brand focus:border-brand">
                    <option>Mới nhất</option>
                    <option>Giá: Thấp đến Cao</option>
                    <option>Giá: Cao đến Thấp</option>
                    <option>Tên: A-Z</option>
                </select>
            </div>
        </div>

        @if($products->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
                @foreach($products as $product)
                    @php
                        $primaryVariant = $product->variants->first();
                        $imgUrl = $product->primary_image_url ?? ($primaryVariant->thumbnail_url ?? 'https://via.placeholder.com/300');
                        if ($imgUrl && !Str::startsWith($imgUrl, ['http://', 'https://'])) {
                            $imgUrl = Storage::url($imgUrl);
                        }
                        $price = $primaryVariant ? ($primaryVariant->sale_price ?? $primaryVariant->price) : 0;
                        $oldPrice = $primaryVariant ? $primaryVariant->price : 0;
                        $isWishlisted = in_array($product->id, $wishlistedProductIds);
                        
                        // Lấy danh sách các màu độc nhất
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
                            isWishlisted: {{ $isWishlisted ? 'true' : 'false' }},
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
                        
                        <!-- Product Image Wrapper -->
                        <div class="relative bg-white overflow-hidden aspect-[3/4] border-b border-gray-100 shrink-0 flex items-center justify-center">
                            <!-- Background Frame Image -->
                            <!-- mix-blend-multiply giúp biến nền trắng của frame thành trong suốt để lộ ảnh ở dưới -->
                            @php
                                $productFrame = \App\Models\Setting::get('product_frame', 'images/White Brown Abstract Border Frame Blank Document A4.png');
                                $frameUrl = str_starts_with($productFrame, 'storage/') ? asset($productFrame) : asset($productFrame);
                            @endphp
                            <img src="{{ $frameUrl }}" 
                                 alt="Frame" 
                                 class="absolute inset-0 w-full h-full object-fill z-10 pointer-events-none"
                                 style="mix-blend-mode: multiply;">
                            
                            <!-- Badges -->
                            <div class="absolute top-2 left-2 z-20 flex flex-col gap-1">
                                @if($oldPrice > $price && $oldPrice > 0)
                                    <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded shadow-sm">
                                        -{{ round((($oldPrice - $price) / $oldPrice) * 100) }}%
                                    </span>
                                @endif
                                @if($product->is_new)
                                    <span class="bg-brand text-white text-xs font-bold px-2 py-1 rounded shadow-sm">
                                        MỚI
                                    </span>
                                @endif
                            </div>

                            <!-- Actual Product Image -->
                            <div class="relative w-full h-full p-8 bg-white z-0 overflow-hidden">
                                <a href="{{ route('products.show', $product->slug) }}" class="block w-full h-full">
                                    <img :src="activeImage" alt="{{ $product->name }}" class="w-full h-full object-contain transition-transform duration-700 group-hover:scale-105 rounded bg-white">
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
                                    <span x-text="isAdding ? 'Đang thêm...' : 'Thêm giỏ hàng'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="text-left p-4 flex flex-col flex-grow bg-white">
                            <a href="{{ route('products.show', $product->slug) }}">
                                <h3 class="font-medium text-gray-800 hover:text-brand transition-colors line-clamp-2">{{ $product->name }}</h3>
                            </a>
                            
                            <!-- Colors -->
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

                            <!-- Price -->
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

            <!-- Pagination -->
            <div class="mt-12 flex justify-center">
                {{ $products->links() }}
            </div>
        @else
            <div class="text-center py-16 bg-white rounded-2xl shadow-sm border border-gray-100">
                <i class="bi bi-emoji-frown text-5xl text-gray-300 mb-4 inline-block"></i>
                <h3 class="text-xl font-medium text-gray-600">Chưa có sản phẩm nào</h3>
                <p class="text-gray-500 mt-2">Bộ sưu tập này hiện tại chưa có sản phẩm nào. Vui lòng quay lại sau!</p>
                <a href="{{ url('/') }}" class="inline-block mt-6 px-6 py-2 bg-brand text-white rounded-full hover:bg-brand-dark transition-colors">
                    Về trang chủ
                </a>
            </div>
        @endif
        
    </div>
</section>

@endsection

@push('scripts')
<script>
    function toggleWishlist(productId, btnElement) {
        // Implementation here (same as home page)
        @if(!auth()->check())
            window.location.href = "{{ route('login') }}";
            return;
        @endif

        fetch('{{ route('wishlist.toggle') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'added') {
                btnElement.innerHTML = '<i class="bi bi-heart-fill text-red-500"></i>';
                toastr.success(data.message);
            } else if (data.status === 'removed') {
                btnElement.innerHTML = '<i class="bi bi-heart"></i>';
                toastr.success(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Có lỗi xảy ra, vui lòng thử lại');
        });
    }
</script>
@endpush
