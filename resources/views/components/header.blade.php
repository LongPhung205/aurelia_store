<header x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-50 bg-white shadow-sm transition-all w-full">
    <!-- Desktop Header -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Left Side (Logo + Menu) -->
            <div class="flex items-center gap-8 lg:gap-12 h-full">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="/" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                        <img src="{{ asset('images/nenlogoaureliawwhite.png') }}" alt="Aurelia Logo" class="h-[45px] w-auto object-contain">
                        <span class="text-brand font-playfair font-bold text-2xl tracking-[2px]">AURELIA</span>
                    </a>
                </div>

                <!-- Mega Menu (Desktop) -->
                <nav class="hidden lg:flex space-x-8 items-center h-full">
                <!-- Home Link -->
                <a href="/" class="{{ request()->is('/') ? 'text-brand border-brand' : 'text-gray-700 hover:text-brand border-transparent hover:border-brand' }} font-medium transition-colors h-full flex items-center border-b-2">
                    Trang chủ
                </a>
                
                @foreach ($categories as $category)
                    @php
                        $isActiveCat = request()->is('danh-muc/' . $category->slug);
                    @endphp
                    <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="h-full flex items-center">
                        <a href="{{ route('categories.show', $category->slug) }}" class="{{ $isActiveCat ? 'text-brand border-brand' : 'text-gray-700 hover:text-brand border-transparent hover:border-brand' }} font-medium transition-colors h-full flex items-center border-b-2">
                            {{ $category->name }}
                        </a>
                        
                        <!-- Dropdown panel -->
                        @if ($category->children->count() > 0)
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 translate-y-1"
                                 class="absolute top-20 left-0 w-full bg-white shadow-lg border-t border-gray-100 z-50"
                                 style="display: none;">
                                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                                    <div class="grid grid-cols-4 gap-8">
                                        @foreach ($category->children as $child)
                                            <div>
                                                <a href="{{ route('categories.show', $child->slug) }}" class="font-bold text-gray-900 hover:text-brand transition-colors text-sm mb-2 block uppercase tracking-wider {{ request()->is('danh-muc/' . $child->slug) ? 'text-brand' : '' }}">
                                                    {{ $child->name }}
                                                </a>
                                                @if ($child->children->count() > 0)
                                                <ul class="space-y-2 mt-4">
                                                    @foreach($child->children as $subChild)
                                                        <li>
                                                            <a href="{{ route('categories.show', $subChild->slug) }}" class="text-sm transition-colors block py-0.5 {{ request()->is('danh-muc/' . $subChild->slug) ? 'text-brand font-semibold' : 'text-gray-500 hover:text-brand' }}">
                                                                {{ $subChild->name }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
                </nav>
            </div>

            <!-- Right utilities -->
            <div class="flex items-center gap-4 lg:gap-6">
                <!-- Search Bar (Hidden on very small screens) -->
                <form action="{{ route('search') }}" method="GET" class="hidden md:flex relative group" 
                      x-data="{ query: '{{ request('q') }}', results: [], loading: false, show: false, searchTimeout: null }"
                      @click.away="show = false">
                    <input type="text" name="q" x-model="query" 
                           @input="
                                clearTimeout(searchTimeout); 
                                if (query.length < 2) { results = []; show = false; return; }
                                show = true;
                                loading = true;
                                searchTimeout = setTimeout(() => {
                                    fetch('{{ route('api.search') }}?q=' + encodeURIComponent(query))
                                        .then(res => res.json())
                                        .then(data => { results = data; loading = false; })
                                }, 300);
                           "
                           @focus="if(query.length >= 2) show = true"
                           class="w-48 lg:w-64 pl-4 pr-10 py-2 border border-gray-200 rounded-full text-sm focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand placeholder-gray-400 transition-all" 
                           placeholder="Tìm kiếm sản phẩm..." autocomplete="off">
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand transition-colors">
                        <i class="bi bi-search"></i>
                    </button>

                    <!-- Search Dropdown -->
                    <div x-show="show" x-transition.opacity.duration.200ms style="display: none;" 
                         class="absolute top-full right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden">
                        <div x-show="loading" class="p-4 text-center text-sm text-gray-500">
                            <i class="bi bi-arrow-repeat inline-block animate-spin mr-2"></i>Đang tìm kiếm...
                        </div>
                        
                        <div x-show="!loading && results.length === 0 && query.length >= 2" class="p-4 text-center text-sm text-gray-500">
                            Không tìm thấy sản phẩm nào.
                        </div>

                        <ul x-show="!loading && results.length > 0" class="max-h-[70vh] overflow-y-auto">
                            <template x-for="product in results" :key="product.id">
                                <li>
                                    <a :href="product.url" class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0">
                                        <img :src="product.image_url" :alt="product.name" class="w-12 h-12 object-cover rounded-md border border-gray-100 shrink-0">
                                        <div class="min-w-0 flex-1">
                                            <h4 class="text-sm font-medium text-gray-900 truncate" x-text="product.name"></h4>
                                            <p class="text-brand font-bold text-sm mt-0.5" x-text="product.price_formatted"></p>
                                        </div>
                                    </a>
                                </li>
                            </template>
                        </ul>
                        
                        <div x-show="!loading && results.length > 0" class="p-2 border-t border-gray-100 bg-gray-50 text-center">
                            <button type="submit" class="text-xs font-medium text-brand hover:text-brand/80 transition-colors">Xem tất cả kết quả</button>
                        </div>
                    </div>
                </form>
                
                <!-- Search Icon for Mobile -->
                <button class="md:hidden text-gray-600 hover:text-brand transition-colors text-lg">
                    <i class="bi bi-search"></i>
                </button>

                <!-- Cart -->
                <a href="{{ route('cart.index') }}" class="text-gray-600 hover:text-brand transition-colors text-lg relative" id="header-cart-icon">
                    <i class="bi bi-cart2"></i>
                    @php
                        $cartQty = app(\App\Services\CartService::class)->getCart()->total_quantity;
                    @endphp
                    <span id="header-cart-badge" class="absolute -top-1.5 -right-2 bg-brand text-white text-[10px] font-bold px-[5px] py-[1px] rounded-full min-w-[16px] text-center shadow-sm {{ $cartQty == 0 ? 'hidden' : '' }}">
                        {{ $cartQty }}
                    </span>
                </a>

                <!-- Wishlist -->
                <a href="{{ route('wishlist.index') }}" class="text-gray-600 hover:text-brand transition-colors text-lg relative" id="header-wishlist-icon" x-data="{ count: {{ $wishlistQty }} }" @wishlist-updated.window="count = $event.detail.count">
                    <i class="bi bi-heart"></i>
                    <span id="header-wishlist-badge" class="absolute -top-1.5 -right-2 bg-brand text-white text-[10px] font-bold px-[5px] py-[1px] rounded-full min-w-[16px] text-center shadow-sm" :class="count > 0 ? '' : 'hidden'" x-text="count"></span>
                </a>

                <!-- Account -->
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-sm transition" title="Trang Quản Trị Admin">
                            <i class="bi bi-speedometer2"></i>
                            <span class="hidden sm:inline">Quản Trị</span>
                        </a>
                    @endif
                    <a href="{{ route('profile.index') }}" class="text-gray-600 hover:text-brand transition-colors text-lg" title="Hồ sơ cá nhân">
                        <i class="bi bi-person-fill text-brand"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-brand transition-colors text-lg" title="Đăng nhập">
                        <i class="bi bi-person"></i>
                    </a>
                @endauth

                <!-- Hamburger button -->
                <button @click="mobileMenuOpen = true" class="lg:hidden text-gray-600 hover:text-brand transition-colors text-2xl ml-2 focus:outline-none">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div x-show="mobileMenuOpen" 
         class="fixed inset-0 bg-black/50 z-[60] lg:hidden"
         x-transition.opacity
         @click="mobileMenuOpen = false"
         style="display: none;">
    </div>

    <!-- Mobile Menu Drawer (Sidebar) -->
    <div class="fixed inset-y-0 left-0 w-[280px] bg-white z-[70] transform transition-transform duration-300 ease-in-out lg:hidden overflow-y-auto flex flex-col shadow-2xl"
         :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'">
        
        <div class="flex items-center justify-between p-4 border-b border-gray-100">
            <a href="/" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                <img src="{{ asset('images/nenlogoaureliawwhite.png') }}" alt="Aurelia Logo" class="h-[35px] w-auto object-contain">
                <span class="text-brand font-playfair font-bold text-xl tracking-[2px]">AURELIA</span>
            </a>
            <button @click="mobileMenuOpen = false" class="text-gray-500 hover:text-red-500 text-2xl focus:outline-none">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="p-4">
            <div class="relative w-full mb-6">
                <input type="text" class="w-full pl-4 pr-10 py-2 border border-gray-200 rounded-full text-sm focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand placeholder-gray-400" placeholder="Tìm kiếm...">
                <button class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand focus:outline-none">
                    <i class="bi bi-search"></i>
                </button>
            </div>

            <nav class="space-y-1">
                <!-- Home Link Mobile -->
                <div class="border-b border-gray-50 pb-2">
                    <a href="/" class="block py-2 font-medium {{ request()->is('/') ? 'text-brand font-semibold' : 'text-gray-800 hover:text-brand' }}">
                        Trang chủ
                    </a>
                </div>

                @foreach ($categories as $category)
                    <div x-data="{ expanded: false }" class="border-b border-gray-50 pb-2">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('categories.show', $category->slug) }}" class="block py-2 font-medium hover:text-brand flex-grow {{ request()->is('danh-muc/' . $category->slug) ? 'text-brand font-semibold' : 'text-gray-800' }}">
                                {{ $category->name }}
                            </a>
                            @if ($category->children->count() > 0)
                                <button @click="expanded = !expanded" class="p-2 text-gray-400 hover:text-brand focus:outline-none">
                                    <i class="bi bi-chevron-down transition-transform duration-200 inline-block" :class="expanded ? 'rotate-180' : ''"></i>
                                </button>
                            @endif
                        </div>
                        
                        @if ($category->children->count() > 0)
                            <div x-show="expanded" class="pl-3 pb-2 space-y-3 mt-1" style="display: none;" x-transition>
                                @foreach ($category->children as $child)
                                    <div>
                                        <a href="{{ route('categories.show', $child->slug) }}" class="block py-1 text-sm font-semibold uppercase tracking-wider {{ request()->is('danh-muc/' . $child->slug) ? 'text-brand' : 'text-gray-700 hover:text-brand' }}">
                                            {{ $child->name }}
                                        </a>
                                        @if ($child->children->count() > 0)
                                            <div class="pl-3 space-y-1 mt-1 border-l-2 border-gray-100">
                                                @foreach ($child->children as $subChild)
                                                    <a href="{{ route('categories.show', $subChild->slug) }}" class="block py-1 text-xs {{ request()->is('danh-muc/' . $subChild->slug) ? 'text-brand font-semibold' : 'text-gray-500 hover:text-brand' }}">
                                                        {{ $subChild->name }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </nav>
        </div>
        
        <div class="mt-auto p-4 border-t border-gray-100 bg-gray-50">
            <div class="flex justify-around">
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center text-slate-800 hover:text-primary-600">
                            <i class="bi bi-speedometer2 text-xl mb-1"></i>
                            <span class="text-xs font-medium">Quản trị</span>
                        </a>
                    @endif
                    <a href="{{ route('profile.index') }}" class="flex flex-col items-center text-brand hover:text-brand">
                        <i class="bi bi-person-fill text-xl mb-1"></i>
                        <span class="text-xs font-medium">Tài khoản</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="flex flex-col items-center text-gray-600 hover:text-brand">
                        <i class="bi bi-person text-xl mb-1"></i>
                        <span class="text-xs font-medium">Tài khoản</span>
                    </a>
                @endauth
                <a href="{{ route('wishlist.index') }}" class="flex flex-col items-center text-gray-600 hover:text-brand relative" x-data="{ count: {{ $wishlistQty }} }" @wishlist-updated.window="count = $event.detail.count">
                    <div class="relative">
                        <i class="bi bi-heart text-xl mb-1"></i>
                        <span class="absolute -top-1 -right-2 bg-brand text-white text-[10px] font-bold px-[4px] py-[1px] rounded-full min-w-[14px] text-center" :class="count > 0 ? '' : 'hidden'" x-text="count"></span>
                    </div>
                    <span class="text-xs font-medium">Yêu thích</span>
                </a>
            </div>
        </div>
    </div>
</header>