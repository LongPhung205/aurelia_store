@extends('layouts.client')

@section('full_width_top')
@if(isset($banners) && $banners->isNotEmpty())
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
@endif
@endsection

@section('content')

<!-- Danh mục mức 2 -->
@if(isset($homeCategories) && $homeCategories->count() > 0)
<section class="mb-12 mt-12 md:mt-16">
    <div class="relative group/carousel" x-data="{ 
            scrollLeft() { this.$refs.slider.scrollBy({ left: -300, behavior: 'smooth' }); }, 
            scrollRight() { this.$refs.slider.scrollBy({ left: 300, behavior: 'smooth' }); } 
        }">
        <!-- Prev Button -->
        <button @click="scrollLeft()" class="absolute -left-5 top-1/2 -translate-y-1/2 z-30 w-10 h-10 bg-white rounded-full shadow-[0_2px_10px_rgba(0,0,0,0.1)] flex items-center justify-center text-gray-600 hover:text-brand hover:scale-110 transition-all opacity-0 group-hover/carousel:opacity-100 hidden md:flex">
            <i class="bi bi-chevron-left"></i>
        </button>
        
        <div x-ref="slider" class="flex overflow-x-auto snap-x snap-mandatory gap-4 md:gap-6 pb-6 pt-12 -mx-4 px-4 sm:mx-0 sm:px-0 scroll-smooth" style="scrollbar-width: none;">
            @foreach($homeCategories as $category)
            <a href="{{ route('categories.show', $category->slug) }}" class="flex-none w-[75vw] sm:w-[320px] snap-start group relative bg-[#f4f6f8] rounded-xl flex items-center p-5 md:p-8 hover:shadow-lg transition-all duration-300 h-28 md:h-36">
                <!-- Tên danh mục -->
                <span class="font-bold text-gray-800 uppercase tracking-wide group-hover:text-brand transition-colors relative z-10 text-sm md:text-lg w-1/2 md:w-3/5 break-words">
                    {{ $category->name }}
                </span>
                
                <!-- Ảnh nổi lên -->
                <div class="absolute right-2 md:right-6 bottom-0 w-24 h-32 md:w-32 md:h-44 bg-white rounded-t-xl md:rounded-xl shadow-[0_-4px_15px_-3px_rgba(0,0,0,0.1)] md:shadow-lg shrink-0 z-20 transition-transform duration-500 group-hover:-translate-y-3 p-2 flex items-center justify-center">
                    <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="max-w-full max-h-full object-contain">
                </div>
            </a>
            @endforeach
        </div>

        <!-- Next Button -->
        <button @click="scrollRight()" class="absolute -right-5 top-1/2 -translate-y-1/2 z-30 w-10 h-10 bg-white rounded-full shadow-[0_2px_10px_rgba(0,0,0,0.1)] flex items-center justify-center text-gray-600 hover:text-brand hover:scale-110 transition-all opacity-0 group-hover/carousel:opacity-100 hidden md:flex">
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>
    
    <style>
        /* Hide scrollbar for Chrome, Safari and Opera */
        .group\\/carousel .flex::-webkit-scrollbar {
            display: none;
        }
    </style>
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
            @include('client.partials.product_card', [
                'product' => $item->product, 
                'isFlashSale' => true, 
                'item' => $item, 
                'overridePrice' => $item->flash_sale_price
            ])
        @endforeach
    </div>
</section>
@endif



<!-- Sản phẩm Nổi bật -->
<section class="mb-16">
    <div class="text-center mb-10">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Sản Phẩm Nổi Bật</h2>
        <div class="w-16 h-1 bg-brand mx-auto rounded"></div>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
        @foreach($featuredProducts as $product)
            @include('client.partials.product_card', ['product' => $product])
        @endforeach
    </div>
</section>

<!-- Tất cả Sản phẩm -->
<section class="mb-16">
    <div class="text-center mb-10">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Tất Cả Sản Phẩm</h2>
        <div class="w-16 h-1 bg-brand mx-auto rounded"></div>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
        @foreach($allProducts as $product)
            @include('client.partials.product_card', ['product' => $product])
        @endforeach
    </div>
    
    <div class="mt-10 flex justify-center">
        {{ $allProducts->links() }}
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
