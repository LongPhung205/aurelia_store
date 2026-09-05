@extends('layouts.client')

@section('content')
<!-- Breadcrumb -->
<nav class="flex text-sm text-gray-500 mb-6" aria-label="Breadcrumb">
  <ol class="inline-flex items-center space-x-1 md:space-x-3">
    <li class="inline-flex items-center">
      <a href="{{ route('home') }}" class="inline-flex items-center hover:text-brand transition-colors">
        Trang chủ
      </a>
    </li>
    @if($product->categories->count() > 0)
    <li>
      <div class="flex items-center">
        <i class="bi bi-chevron-right text-gray-400 mx-1"></i>
        <a href="#" class="hover:text-brand transition-colors">{{ $product->categories->first()->name }}</a>
      </div>
    </li>
    @endif
    <li aria-current="page">
      <div class="flex items-center">
        <i class="bi bi-chevron-right text-gray-400 mx-1"></i>
        <span class="text-gray-900 font-medium">{{ $product->name }}</span>
      </div>
    </li>
  </ol>
</nav>

@php
    $variantsJson = $product->variants->map(function($v) {
        $url = $v->thumbnail_url;
        if ($url && !Str::startsWith($url, ['http://', 'https://'])) {
            $url = Storage::url($url);
        }
        return [
            'id' => $v->id,
            'color_id' => $v->color_id,
            'size_id' => $v->size_id,
            'price' => $v->price,
            'sale_price' => $v->sale_price,
            'stock_quantity' => $v->stock_quantity,
            'image' => $url
        ];
    })->toJson();

    $imagesJson = $product->images->map(function($i) {
        $url = $i->image_url;
        if ($url && !Str::startsWith($url, ['http://', 'https://'])) {
            $url = Storage::url($url);
        }
        return $url;
    })->values()->toJson();
    
    // Kiểm tra đã wishlist chưa (ẩn danh coi như false)
    $isWishlisted = false;
    if(auth()->check()){
        $isWishlisted = auth()->user()->wishlists()->where('product_id', $product->id)->exists();
    }
@endphp

<div x-data="productApp()" x-init="init()" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-12">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
        
        <!-- 1. Product Gallery -->
        <div class="flex flex-col gap-4 sticky top-24 h-max">
            <!-- Main Image with Zoom on Hover -->
            <div class="relative bg-gray-50 rounded-xl overflow-hidden aspect-[3/4] cursor-crosshair group"
                 @mousemove="zoomImage($event)"
                 @mouseleave="resetZoom()">
                 
                <!-- Badges -->
                <div class="absolute top-4 left-4 z-10 flex flex-col gap-2">
                    @if($product->variants->max('sale_price'))
                        <span class="bg-red-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">SALE</span>
                    @endif
                    <span class="bg-black text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">NEW</span>
                </div>
                
                <img :src="activeImage" :alt="'{{ $product->name }}'" class="w-full h-full object-cover transition-opacity duration-300" id="main-image">
                
                <!-- Zoom effect layer -->
                <div id="zoom-layer" class="absolute inset-0 bg-no-repeat opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none hidden md:block"
                     :style="`background-image: url(${activeImage}); background-size: 200%;`"></div>
            </div>
            
            <!-- Thumbnails -->
            <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
                <template x-for="(img, index) in allImages" :key="index">
                    <button @click="setActiveImage(img)" 
                            class="relative w-20 h-24 flex-shrink-0 rounded-lg overflow-hidden border-2 transition-all"
                            :class="activeImage === img ? 'border-brand' : 'border-transparent hover:border-gray-300'">
                        <img :src="img" class="w-full h-full object-cover">
                    </button>
                </template>
            </div>
        </div>

        <!-- 2. Product Info & Pricing -->
        <div class="flex flex-col">
            <h1 class="text-3xl font-bold text-gray-900 mb-3 leading-tight">{{ $product->name }}</h1>
            
            <!-- Rating & Reviews -->
            <div class="flex items-center gap-4 mb-5">
                <div class="flex items-center text-yellow-400 text-sm gap-0.5">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= round($product->average_rating))
                            <i class="bi bi-star-fill"></i>
                        @else
                            <i class="bi bi-star"></i>
                        @endif
                    @endfor
                    <span class="text-gray-600 font-medium ml-2">{{ number_format($product->average_rating, 1) }}/5</span>
                </div>
                <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                <a href="#reviews" class="text-sm text-brand hover:underline font-medium">{{ $product->review_count }} Đánh giá</a>
                <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                <span class="text-sm text-gray-500 font-medium">{{ number_format($product->view_count) }} lượt xem</span>
            </div>
            
            <!-- Pricing -->
            <div class="bg-gray-50 p-5 rounded-xl mb-6 border border-gray-100 flex items-end gap-4">
                <div class="text-4xl font-black text-brand tracking-tight" x-text="formatPrice(activePrice)"></div>
                <template x-if="activeOldPrice > activePrice">
                    <div class="flex flex-col items-start pb-1">
                        <span class="text-lg text-gray-400 line-through font-medium" x-text="formatPrice(activeOldPrice)"></span>
                        <span class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wide" x-text="`GIẢM ${Math.round((1 - activePrice/activeOldPrice)*100)}%`"></span>
                    </div>
                </template>
            </div>

            <!-- Vouchers (Mục 5) -->
            @if($coupons->count() > 0)
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-3 text-gray-700 font-medium text-sm">
                    <i class="bi bi-ticket-perforated text-brand"></i> Mã giảm giá của Shop
                </div>
                <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
                    @foreach($coupons as $coupon)
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 min-w-[200px] flex-shrink-0 flex items-center justify-between shadow-sm relative overflow-hidden">
                        <!-- Cạnh răng cưa voucher design -->
                        <div class="absolute -left-2 top-1/2 -translate-y-1/2 w-4 h-4 bg-white rounded-full border border-orange-200"></div>
                        <div class="absolute -right-2 top-1/2 -translate-y-1/2 w-4 h-4 bg-white rounded-full border border-orange-200"></div>
                        
                        <div class="pl-2">
                            <div class="text-orange-600 font-bold text-sm">{{ $coupon->type == 'fixed' ? number_format($coupon->value,0,',','.').'đ' : $coupon->value.'%' }}</div>
                            <div class="text-gray-500 text-[10px]">Đơn tối thiểu {{ number_format($coupon->min_order_value,0,',','.') }}đ</div>
                        </div>
                        <button class="bg-orange-600 text-white text-[10px] font-bold px-3 py-1.5 rounded hover:bg-orange-700 transition-colors z-10" onclick="copyToClipboard('{{ $coupon->code }}')">LƯU MÃ</button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <hr class="border-gray-100 mb-6">

            <!-- 3. Variants Selection -->
            <div class="mb-6 space-y-5">
                <!-- Color -->
                @if($colors->count() > 0)
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Màu sắc <span class="text-gray-400 font-normal normal-case ml-1" x-text="activeColorName ? `(${activeColorName})` : ''"></span></span>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @foreach($colors as $color)
                        <button type="button" @click="selectColor({{ $color->id }}, '{{ $color->name }}')"
                                :class="selectedColor === {{ $color->id }} ? 'ring-2 ring-brand ring-offset-2 scale-110' : 'opacity-70 hover:opacity-100'"
                                class="w-9 h-9 rounded-full border shadow-sm transition-all focus:outline-none" 
                                style="background-color: {{ $color->hex_code ?? '#cccccc' }}" 
                                title="{{ $color->name }}"></button>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Size -->
                @if($sizes->count() > 0)
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Kích thước</span>
                        <button type="button" onclick="document.getElementById('sizeGuideModal').classList.remove('hidden')" class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                            <i class="bi bi-rulers"></i> Hướng dẫn chọn size
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2 md:gap-3">
                        @foreach($sizes as $size)
                        <button type="button" @click="selectSize({{ $size->id }})"
                                :class="selectedSize === {{ $size->id }} ? 'bg-gray-900 text-white border-gray-900 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-900'"
                                :disabled="!isSizeAvailable({{ $size->id }})"
                                class="min-w-[48px] h-10 px-3 rounded-lg border font-medium transition-all focus:outline-none disabled:opacity-30 disabled:cursor-not-allowed disabled:bg-gray-100 flex items-center justify-center">
                            {{ $size->name }}
                        </button>
                        @endforeach
                    </div>
                    
                    <!-- Thông báo hết hàng / tình trạng kho -->
                    <div class="mt-3 text-sm" x-show="selectedColor && selectedSize">
                        <template x-if="activeVariantId && activeStock > 0">
                            <span class="text-green-600 font-medium flex items-center gap-1"><i class="bi bi-check-circle-fill"></i> Còn hàng (<span x-text="activeStock"></span> sản phẩm)</span>
                        </template>
                        <template x-if="activeVariantId && activeStock <= 0">
                            <span class="text-red-500 font-medium flex items-center gap-1"><i class="bi bi-x-circle-fill"></i> Hết hàng</span>
                        </template>
                        <template x-if="!activeVariantId">
                            <span class="text-orange-500 font-medium flex items-center gap-1"><i class="bi bi-exclamation-circle-fill"></i> Phiên bản này không tồn tại</span>
                        </template>
                    </div>
                </div>
                @endif
            </div>

            <!-- 4. Call to Action -->
            <div class="flex flex-col sm:flex-row gap-4 mt-auto border-t border-gray-100 pt-6">
                <!-- Quantity -->
                <div class="flex items-center border border-gray-300 rounded-xl bg-white p-1 h-14 w-full sm:w-[130px] shrink-0">
                    <button type="button" @click="quantity > 1 ? quantity-- : null" class="w-10 h-full flex items-center justify-center text-gray-500 hover:text-brand hover:bg-gray-50 rounded-lg transition-colors">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                    <input type="number" x-model="quantity" min="1" :max="activeStock || 1" class="w-full text-center border-none focus:ring-0 font-bold text-gray-900 p-0 text-lg bg-transparent" readonly>
                    <button type="button" @click="quantity < activeStock ? quantity++ : null" class="w-10 h-full flex items-center justify-center text-gray-500 hover:text-brand hover:bg-gray-50 rounded-lg transition-colors">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>

                <div class="flex-1 flex gap-3">
                    <button @click="addToCart()" :disabled="!canAddToCart || isAdding" class="flex-1 bg-white border-2 border-brand text-brand font-bold rounded-xl h-14 flex items-center justify-center gap-2 hover:bg-red-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed uppercase tracking-wide text-sm md:text-base shadow-sm">
                        <i class="bi bi-cart-plus text-xl" x-show="!isAdding"></i>
                        <i class="bi bi-arrow-repeat animate-spin text-xl" x-show="isAdding" style="display: none;"></i>
                        Thêm vào giỏ
                    </button>
                    <button @click="buyNow()" :disabled="!canAddToCart" class="flex-1 bg-brand text-white font-bold rounded-xl h-14 flex items-center justify-center hover:bg-[#C2185B] transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-pink-200 uppercase tracking-wide text-sm md:text-base">
                        Mua ngay
                    </button>
                </div>
                
                <button @click="toggleWishlist()" class="w-14 h-14 shrink-0 rounded-xl border border-gray-200 flex items-center justify-center text-gray-400 hover:text-brand hover:border-brand hover:bg-red-50 transition-colors shadow-sm bg-white">
                    <i class="bi text-2xl transition-colors" :class="isWishlisted ? 'bi-heart-fill text-brand' : 'bi-heart'"></i>
                </button>
            </div>
            
            <!-- Trust Badges -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-8 pt-6 border-t border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-lg shrink-0"><i class="bi bi-shield-check"></i></div>
                    <span class="text-xs text-gray-600 font-medium">Cam kết hàng chuẩn thiết kế</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-50 text-green-500 flex items-center justify-center text-lg shrink-0"><i class="bi bi-box-seam"></i></div>
                    <span class="text-xs text-gray-600 font-medium">Đổi trả 7 ngày toàn quốc</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center text-lg shrink-0"><i class="bi bi-headset"></i></div>
                    <span class="text-xs text-gray-600 font-medium">Hỗ trợ 24/7 qua Zalo/Hotline</span>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Tabs Section -->
<div x-data="{ activeTab: 'description' }" class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-12 overflow-hidden">
    <div class="flex border-b border-gray-100 px-6 pt-2 bg-gray-50/50">
        <button @click="activeTab = 'description'" class="px-6 py-4 font-bold text-sm md:text-base border-b-2 transition-colors uppercase tracking-wide" :class="activeTab === 'description' ? 'border-brand text-brand' : 'border-transparent text-gray-500 hover:text-gray-900'">
            Mô tả sản phẩm
        </button>
        <button @click="activeTab = 'reviews'" id="reviews" class="px-6 py-4 font-bold text-sm md:text-base border-b-2 transition-colors uppercase tracking-wide" :class="activeTab === 'reviews' ? 'border-brand text-brand' : 'border-transparent text-gray-500 hover:text-gray-900'">
            Đánh giá ({{ $product->review_count }})
        </button>
    </div>
    
    <div class="p-6 md:p-10">
        <!-- Tab: Description -->
        <div x-show="activeTab === 'description'" x-transition.opacity>
            @if($product->short_description)
                <p class="text-gray-600 italic mb-6 text-lg border-l-4 border-gray-200 pl-4 py-2">{{ $product->short_description }}</p>
            @endif
            
            <div class="prose prose-pink max-w-none text-gray-700 leading-relaxed">
                {!! $product->description !!}
            </div>
            
            @if($product->material)
            <div class="mt-8 bg-pink-50/50 p-6 rounded-xl border border-pink-100">
                <h4 class="font-bold text-gray-900 mb-2 flex items-center gap-2"><i class="bi bi-stars text-brand"></i> Thông tin chất liệu & Bảo quản</h4>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $product->material }}</p>
            </div>
            @endif
        </div>
        
        <!-- Tab: Reviews -->
        <div x-show="activeTab === 'reviews'" style="display: none;" x-transition.opacity>
            <!-- Review Summary -->
            <div class="flex flex-col md:flex-row gap-8 items-center bg-gray-50 p-6 md:p-8 rounded-xl mb-10 border border-gray-100">
                <div class="text-center md:border-r border-gray-200 md:pr-10">
                    <div class="text-5xl font-black text-brand mb-2">{{ number_format($product->average_rating, 1) }}</div>
                    <div class="flex text-yellow-400 text-xl justify-center gap-1 mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= round($product->average_rating))
                                <i class="bi bi-star-fill"></i>
                            @else
                                <i class="bi bi-star"></i>
                            @endif
                        @endfor
                    </div>
                    <div class="text-gray-500 text-sm font-medium">{{ $product->review_count }} đánh giá</div>
                </div>
                
                <div class="flex-1 w-full space-y-2">
                    @for($i = 5; $i >= 1; $i--)
                        @php
                            $count = $product->reviews->where('is_approved', true)->where('rating', $i)->count();
                            $percent = $product->review_count > 0 ? ($count / $product->review_count) * 100 : 0;
                        @endphp
                        <div class="flex items-center gap-3 text-sm font-medium text-gray-600">
                            <div class="w-8 shrink-0 flex items-center gap-1">{{ $i }} <i class="bi bi-star-fill text-yellow-400 text-xs"></i></div>
                            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-brand rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                            <div class="w-10 text-right">{{ $count }}</div>
                        </div>
                    @endfor
                </div>
            </div>
            
            <!-- Review Form (Only if purchased) -->
            @if($hasPurchased)
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm mb-10 relative overflow-hidden" x-data="{ rating: 5, hoverRating: 0 }">
                    <div class="absolute top-0 left-0 w-1 h-full bg-brand"></div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Gửi đánh giá của bạn</h3>
                    <form action="{{ route('reviews.store', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Đánh giá sao <span class="text-red-500">*</span></label>
                            <div class="flex gap-2 text-2xl cursor-pointer" @mouseleave="hoverRating = 0">
                                <template x-for="i in 5">
                                    <i class="bi transition-transform hover:scale-110" 
                                       :class="(hoverRating >= i || (!hoverRating && rating >= i)) ? 'bi-star-fill text-yellow-400' : 'bi-star text-gray-300'"
                                       @mouseenter="hoverRating = i"
                                       @click="rating = i"></i>
                                </template>
                            </div>
                            <input type="hidden" name="rating" x-model="rating">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nội dung đánh giá <span class="text-red-500">*</span></label>
                            <textarea name="content" rows="4" class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50" placeholder="Chất liệu vải ra sao? Kiểu dáng có đúng như hình ảnh không?..." required></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Thêm hình ảnh thực tế (Tùy chọn)</label>
                            <input type="file" name="images[]" multiple accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-brand hover:file:bg-pink-100 transition-colors">
                        </div>
                        
                        <button type="submit" class="bg-gray-900 text-white font-bold py-2.5 px-6 rounded-lg hover:bg-brand transition-colors">Gửi đánh giá</button>
                    </form>
                </div>
            @elseif(auth()->check())
                <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 text-center mb-10">
                    <i class="bi bi-bag-x text-4xl text-gray-300 mb-2 block"></i>
                    <p class="text-gray-600 font-medium">Bạn cần mua sản phẩm này thành công để có thể viết đánh giá.</p>
                </div>
            @else
                <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 text-center mb-10">
                    <p class="text-gray-600 mb-3 font-medium">Vui lòng đăng nhập để viết đánh giá</p>
                    <a href="{{ route('login') }}" class="inline-block bg-brand text-white font-bold py-2 px-6 rounded-lg shadow hover:bg-[#d62800]">Đăng nhập ngay</a>
                </div>
            @endif

            <!-- Review List -->
            @if($product->review_count > 0)
                <div class="space-y-6">
                    @foreach($product->reviews->where('is_approved', true)->sortByDesc('created_at') as $review)
                        <div class="border-b border-gray-100 pb-6 last:border-0 last:pb-0">
                            <div class="flex items-start gap-4">
                                <!-- Avatar -->
                                <div class="w-12 h-12 rounded-full bg-gray-200 shrink-0 overflow-hidden flex items-center justify-center text-gray-500 font-bold text-lg">
                                    @if($review->user->avatar)
                                        <img src="{{ Storage::url($review->user->avatar) }}" class="w-full h-full object-cover">
                                    @else
                                        {{ substr($review->user->name, 0, 1) }}
                                    @endif
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <h4 class="font-bold text-gray-900">{{ $review->user->name }}</h4>
                                        <span class="text-xs text-gray-400"><i class="bi bi-clock"></i> {{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="flex text-yellow-400 text-xs mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating) <i class="bi bi-star-fill"></i> @else <i class="bi bi-star text-gray-300"></i> @endif
                                        @endfor
                                    </div>
                                    <p class="text-gray-700 text-sm leading-relaxed mb-3 whitespace-pre-line">{{ $review->content }}</p>
                                    
                                    @if($review->images->count() > 0)
                                        <div class="flex gap-2 mt-2">
                                            @foreach($review->images as $img)
                                                <a href="{{ Storage::url($img->image_url) }}" target="_blank" class="block w-20 h-20 rounded border border-gray-200 overflow-hidden hover:opacity-80 transition-opacity">
                                                    <img src="{{ Storage::url($img->image_url) }}" class="w-full h-full object-cover">
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10">
                    <i class="bi bi-chat-square-text text-5xl text-gray-200 mb-3 block"></i>
                    <p class="text-gray-500 font-medium">Chưa có đánh giá nào cho sản phẩm này.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Related Products -->
@if($relatedProducts->count() > 0)
<div class="mb-16">
    <div class="flex items-center justify-between mb-8 border-b border-gray-200 pb-4">
        <h2 class="text-2xl font-black text-gray-900 uppercase tracking-tight">CÓ THỂ BẠN SẼ THÍCH</h2>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
        @foreach($relatedProducts as $relProduct)
        @php
            $relVariant = $relProduct->variants->first();
            $relImg = $relProduct->primary_image_url ?? ($relVariant->thumbnail_url ?? 'https://via.placeholder.com/300');
            if ($relImg && !Str::startsWith($relImg, ['http://', 'https://'])) {
                $relImg = Storage::url($relImg);
            }
            $relPrice = $relVariant ? ($relVariant->sale_price ?? $relVariant->price) : 0;
            $relOldPrice = $relVariant ? $relVariant->price : 0;
        @endphp
        <a href="{{ route('products.show', $relProduct->slug) }}" class="group bg-white rounded-xl shadow-sm hover:shadow-xl transition-shadow border border-gray-100 overflow-hidden flex flex-col h-full">
            <div class="relative bg-gray-100 overflow-hidden aspect-[3/4] border-b border-gray-100 shrink-0">
                <img src="{{ $relImg }}" alt="{{ $relProduct->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @if($relPrice < $relOldPrice)
                    <div class="absolute top-2 right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm">-{{ round((1 - $relPrice/$relOldPrice)*100) }}%</div>
                @endif
            </div>
            <div class="text-left p-4 flex flex-col flex-grow bg-white z-10">
                <h3 class="font-medium text-gray-800 group-hover:text-brand transition-colors line-clamp-2 text-sm mb-2">{{ $relProduct->name }}</h3>
                <div class="mt-auto flex items-center justify-start gap-2">
                    <span class="font-bold text-brand">{{ number_format($relPrice, 0, ',', '.') }}đ</span>
                    @if($relPrice < $relOldPrice)
                        <span class="text-xs text-gray-400 line-through">{{ number_format($relOldPrice, 0, ',', '.') }}đ</span>
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

<!-- Size Guide Modal -->
<div id="sizeGuideModal" class="fixed inset-0 bg-black/60 z-[100] hidden flex items-center justify-center p-4 backdrop-blur-sm" onclick="if(event.target === this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden animate-fade-in-up">
        <div class="flex justify-between items-center p-5 border-b border-gray-100">
            <h3 class="font-bold text-xl text-gray-900"><i class="bi bi-rulers text-brand mr-2"></i> Bảng hướng dẫn chọn size</h3>
            <button onclick="document.getElementById('sizeGuideModal').classList.add('hidden')" class="text-gray-400 hover:text-red-500 text-2xl transition-colors"><i class="bi bi-x-circle-fill"></i></button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[70vh]">
            <!-- Bảng quy đổi tĩnh cho Thời trang Nữ -->
            <table class="w-full text-center border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 font-bold">
                        <th class="border border-gray-200 p-3 rounded-tl-lg">SIZE</th>
                        <th class="border border-gray-200 p-3">CHIỀU CAO (cm)</th>
                        <th class="border border-gray-200 p-3">CÂN NẶNG (kg)</th>
                        <th class="border border-gray-200 p-3">VÒNG EO (cm)</th>
                        <th class="border border-gray-200 p-3 rounded-tr-lg">VÒNG MÔNG (cm)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-gray-200 p-3 font-bold text-brand">S</td>
                        <td class="border border-gray-200 p-3">150 - 155</td>
                        <td class="border border-gray-200 p-3">40 - 45</td>
                        <td class="border border-gray-200 p-3">60 - 64</td>
                        <td class="border border-gray-200 p-3">84 - 88</td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-200 p-3 font-bold text-brand">M</td>
                        <td class="border border-gray-200 p-3">155 - 160</td>
                        <td class="border border-gray-200 p-3">46 - 52</td>
                        <td class="border border-gray-200 p-3">64 - 68</td>
                        <td class="border border-gray-200 p-3">88 - 92</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-200 p-3 font-bold text-brand">L</td>
                        <td class="border border-gray-200 p-3">160 - 165</td>
                        <td class="border border-gray-200 p-3">53 - 58</td>
                        <td class="border border-gray-200 p-3">68 - 72</td>
                        <td class="border border-gray-200 p-3">92 - 96</td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-200 p-3 font-bold text-brand">XL</td>
                        <td class="border border-gray-200 p-3">165 - 170</td>
                        <td class="border border-gray-200 p-3">59 - 65</td>
                        <td class="border border-gray-200 p-3">72 - 76</td>
                        <td class="border border-gray-200 p-3">96 - 100</td>
                    </tr>
                </tbody>
            </table>
            <p class="text-gray-500 text-xs italic mt-4">* Lưu ý: Số đo có thể chênh lệch 1-2cm tùy theo phom dáng thiết kế và chất liệu vải. Nếu số đo của bạn nằm giữa 2 size, hãy chọn size lớn hơn để thoải mái hơn.</p>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Ẩn input type number arrows */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
    
    /* Animation cho modal */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.3s ease-out forwards;
    }
    
    /* Hover Zoom */
    #zoom-layer {
        background-position: 50% 50%;
    }
    
    /* Hide scrollbar for horizontal list */
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush

@push('scripts')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Đã lưu mã ' + text, type: 'success' } }));
        }).catch(err => {
            window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Không thể copy mã', type: 'error' } }));
        });
    }

    function productApp() {
        return {
            variants: {!! $variantsJson !!},
            allImages: {!! $imagesJson !!},
            
            activeImage: '',
            
            selectedColor: null,
            selectedSize: null,
            activeColorName: '',
            
            quantity: 1,
            isAdding: false,
            
            isWishlisted: {{ $isWishlisted ? 'true' : 'false' }},
            isTogglingWishlist: false,
            productId: {{ $product->id }},
            
            init() {
                // Thêm thumbnail của các biến thể vào danh sách ảnh chung nếu chưa có
                let variantImages = this.variants.map(v => v.image).filter(i => i !== null && i !== "");
                let uniqueImages = [...new Set([...this.allImages, ...variantImages])];
                this.allImages = uniqueImages;
                
                // Đặt ảnh mặc định
                if(this.allImages.length > 0) {
                    this.activeImage = this.allImages[0];
                }
                
                // Tự động chọn màu đầu tiên nếu có
                @if($colors->count() > 0)
                    const firstColor = { id: {{ $colors->first()->id }}, name: '{{ $colors->first()->name }}' };
                    this.selectColor(firstColor.id, firstColor.name);
                @elseif($sizes->count() > 0)
                    // Nếu không có màu mà có size, chọn size đầu
                    this.selectSize({{ $sizes->first()->id }});
                @endif
            },
            
            setActiveImage(img) {
                this.activeImage = img;
            },
            
            zoomImage(e) {
                const container = e.currentTarget;
                const layer = container.querySelector('#zoom-layer');
                if(!layer) return;
                
                const rect = container.getBoundingClientRect();
                // Tính tọa độ % của chuột trong container
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;
                
                layer.style.backgroundPosition = `${x}% ${y}%`;
            },
            
            resetZoom() {
                const layer = document.getElementById('zoom-layer');
                if(layer) layer.style.backgroundPosition = '50% 50%';
            },
            
            selectColor(colorId, colorName) {
                this.selectedColor = colorId;
                this.activeColorName = colorName;
                this.quantity = 1;
                
                // Đổi ảnh tương ứng với màu (tìm variant có ảnh)
                let v = this.variants.find(v => v.color_id === colorId && v.image);
                if(v && v.image) {
                    this.activeImage = v.image;
                }
                
                // Kiểm tra xem size đang chọn có hợp lệ với màu mới không
                if (this.selectedSize && !this.isSizeAvailable(this.selectedSize)) {
                    // Nếu không hợp lệ, tự động chọn size đầu tiên có sẵn cho màu này
                    let availableVariant = this.variants.find(v => v.color_id === colorId && v.stock_quantity > 0);
                    if(availableVariant) {
                        this.selectedSize = availableVariant.size_id;
                    } else {
                        this.selectedSize = null;
                    }
                }
            },
            
            selectSize(sizeId) {
                this.selectedSize = sizeId;
                this.quantity = 1;
            },
            
            isSizeAvailable(sizeId) {
                if(!this.selectedColor) return true;
                return this.variants.some(v => v.color_id === this.selectedColor && v.size_id === sizeId);
            },
            
            get activeVariant() {
                if(!this.selectedColor && !this.selectedSize) {
                    return this.variants.length > 0 ? this.variants[0] : null;
                }
                
                if(this.selectedColor && this.selectedSize) {
                    return this.variants.find(v => v.color_id === this.selectedColor && v.size_id === this.selectedSize);
                }
                
                // Nếu chỉ chọn 1 trong 2, lấy variant tiêu biểu
                if(this.selectedColor) {
                    return this.variants.find(v => v.color_id === this.selectedColor);
                }
                
                if(this.selectedSize) {
                    return this.variants.find(v => v.size_id === this.selectedSize);
                }
                
                return null;
            },
            
            get activeVariantId() {
                const v = this.activeVariant;
                return v ? v.id : null;
            },
            
            get activePrice() {
                const v = this.activeVariant;
                return v ? (v.sale_price || v.price) : 0;
            },
            
            get activeOldPrice() {
                const v = this.activeVariant;
                return v ? v.price : 0;
            },
            
            get activeStock() {
                const v = this.activeVariant;
                return v ? v.stock_quantity : 0;
            },
            
            get canAddToCart() {
                // Phải chọn đủ thuộc tính nếu có
                const hasColors = {{ $colors->count() > 0 ? 'true' : 'false' }};
                const hasSizes = {{ $sizes->count() > 0 ? 'true' : 'false' }};
                
                if(hasColors && !this.selectedColor) return false;
                if(hasSizes && !this.selectedSize) return false;
                
                return this.activeVariantId && this.activeStock > 0 && this.quantity > 0 && this.quantity <= this.activeStock;
            },
            
            formatPrice(price) {
                return new Intl.NumberFormat('vi-VN').format(price) + 'đ';
            },
            
            addToCart(redirect = false) {
                if(!this.canAddToCart) return;
                
                this.isAdding = true;
                
                fetch('{{ route('cart.add') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        variant_id: this.activeVariantId,
                        quantity: this.quantity
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
                        
                        if(redirect) {
                            window.location.href = '{{ route('cart.index') }}';
                        } else {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Đã thêm ' + this.quantity + ' sản phẩm vào giỏ hàng!', type: 'success' } }));
                        }
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message || 'Có lỗi xảy ra', type: 'error' } }));
                    }
                })
                .catch(err => {
                    console.error(err);
                    this.isAdding = false;
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Lỗi kết nối mạng!', type: 'error' } }));
                });
            },
            
            buyNow() {
                this.addToCart(true);
            },

            async toggleWishlist() {
                if (this.isTogglingWishlist) return;
                this.isTogglingWishlist = true;
                
                try {
                    const response = await fetch('{{ route('wishlist.toggle') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ product_id: this.productId })
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
        }
    }
</script>
@endpush
