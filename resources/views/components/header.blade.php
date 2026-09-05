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
                <a href="/" class="text-gray-700 hover:text-brand font-medium transition-colors h-full flex items-center border-b-2 border-transparent hover:border-brand">
                    Trang chủ
                </a>
                
                @foreach ($categories as $category)
                    <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="h-full flex items-center">
                        <a href="#" class="text-gray-700 hover:text-brand font-medium transition-colors h-full flex items-center border-b-2 border-transparent hover:border-brand">
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
                                                <a href="#" class="font-bold text-gray-900 hover:text-brand transition-colors text-sm mb-2 block uppercase tracking-wider">
                                                    {{ $child->name }}
                                                </a>
                                                @if ($child->children->count() > 0)
                                                <ul class="space-y-2 mt-4">
                                                    @foreach($child->children as $subChild)
                                                        <li>
                                                            <a href="#" class="text-gray-500 hover:text-brand text-sm transition-colors">
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
                <div class="hidden md:flex relative group">
                    <input type="text" class="w-48 lg:w-64 pl-4 pr-10 py-2 border border-gray-200 rounded-full text-sm focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand placeholder-gray-400 transition-all" placeholder="Tìm kiếm sản phẩm...">
                    <button class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand transition-colors">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                
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
                    <a href="/" class="block py-2 text-gray-800 font-medium hover:text-brand">
                        Trang chủ
                    </a>
                </div>

                @foreach ($categories as $category)
                    <div x-data="{ expanded: false }" class="border-b border-gray-50 pb-2">
                        <div class="flex items-center justify-between">
                            <a href="#" class="block py-2 text-gray-800 font-medium hover:text-brand flex-grow">
                                {{ $category->name }}
                            </a>
                            @if ($category->children->count() > 0)
                                <button @click="expanded = !expanded" class="p-2 text-gray-400 hover:text-brand focus:outline-none">
                                    <i class="bi bi-chevron-down transition-transform duration-200 inline-block" :class="expanded ? 'rotate-180' : ''"></i>
                                </button>
                            @endif
                        </div>
                        
                        @if ($category->children->count() > 0)
                            <div x-show="expanded" class="pl-4 pb-2 space-y-2 mt-1" style="display: none;" x-transition>
                                @foreach ($category->children as $child)
                                    <a href="#" class="block py-1.5 text-sm text-gray-500 hover:text-brand">
                                        {{ $child->name }}
                                    </a>
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