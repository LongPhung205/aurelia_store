<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Váy Công Sở</title>
    <!-- Tailwind CSS & Flowbite (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Bootstrap Icons -->
    <link href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.css') }}" rel="stylesheet">
    <!-- Google Material Symbols & Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb; /* gray-50 */
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Modal Backdrop Blur */
        body > div.fixed.inset-0.z-40 {
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            background-color: rgba(15, 23, 42, 0.5) !important;
        }

        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1; 
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1; /* slate-300 */
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8; /* slate-400 */
        }

        /* Sidebar scrollbar */
        .sidebar-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-scrollbar::-webkit-scrollbar-track {
            background: transparent; 
        }
        .sidebar-scrollbar::-webkit-scrollbar-thumb {
            background: #ffffff;
            border-radius: 10px;
        }
        .sidebar-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #f1f1f1;
        }
        
        /* Utility for Dropzone/Uploads later */
        .image-preview-wrapper {
            position: relative;
            display: inline-block;
        }
        .image-preview-wrapper .btn-remove {
            position: absolute;
            top: 5px;
            right: 5px;
            display: none;
        }
        .image-preview-wrapper:hover .btn-remove {
            display: block;
        }
    </style>
    </style>
    @stack('styles')
    
    <script>
        // Prevent FOUC
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-900 antialiased dark:bg-slate-900 dark:text-gray-100 transition-colors duration-200 tracking-tight">

    <!-- Top Navbar -->
    <nav class="fixed top-0 right-0 z-30 w-full sm:w-[calc(100%-272px)] bg-transparent sm:bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start">
                    <!-- Mobile sidebar toggle -->
                    <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-slate-500 rounded-lg sm:hidden hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-200 dark:text-slate-400 dark:hover:bg-slate-700 dark:focus:ring-slate-600">
                        <span class="sr-only">Open sidebar</span>
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <!-- Page Title / Breadcrumbs -->
                    <div class="hidden sm:block ms-3">
                        @php
                            $routeName = request()->route() ? request()->route()->getName() : '';
                            $breadcrumbGroup = 'Tổng quan';
                            
                            if (Str::startsWith($routeName, ['admin.products', 'admin.categories', 'admin.attributes', 'admin.colors', 'admin.sizes', 'admin.materials'])) {
                                $breadcrumbGroup = 'Product Manager';
                            } elseif (Str::startsWith($routeName, ['admin.imports', 'admin.inventory'])) {
                                $breadcrumbGroup = 'Warehouse & Inventory';
                            } elseif (Str::startsWith($routeName, ['admin.orders'])) {
                                $breadcrumbGroup = 'Orders & Fulfillment';
                            } elseif (Str::startsWith($routeName, ['admin.finance', 'admin.transactions'])) {
                                $breadcrumbGroup = 'Finance & Cashflow';
                            } elseif (Str::startsWith($routeName, ['admin.banners', 'admin.coupons', 'admin.flash_sales', 'admin.posts'])) {
                                $breadcrumbGroup = 'Marketing & Promotion';
                            } elseif (Str::startsWith($routeName, ['admin.users', 'admin.settings'])) {
                                $breadcrumbGroup = 'System Settings';
                            }
                        @endphp
                        <nav class="flex" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                                <li class="inline-flex items-center">
                                    <span class="inline-flex items-center text-sm font-medium text-slate-500 dark:text-slate-400">
                                        {{ $breadcrumbGroup }}
                                    </span>
                                </li>
                                @if(View::hasSection('title') && View::getSection('title') !== 'Admin Dashboard' && View::getSection('title') !== 'Dashboard')
                                <li aria-current="page">
                                    <div class="flex items-center">
                                        <i class="bi bi-chevron-right text-slate-400 mx-1 text-[10px]"></i>
                                        <span class="ml-1 text-base font-semibold text-slate-800 md:ml-2 dark:text-white tracking-tight">@yield('title')</span>
                                    </div>
                                </li>
                                @endif
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="flex items-center">
                    <!-- Notifications Dropdown -->
                    @php
                        $unreadNotificationsCount = auth()->user()->unreadNotifications->count();
                        $recentNotifications = auth()->user()->notifications()->take(5)->get();
                    @endphp
                    <button type="button" data-dropdown-toggle="notification-dropdown" class="p-2 mr-1 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg focus:outline-none focus:ring-4 focus:ring-slate-200 dark:focus:ring-slate-700 relative transition-colors">
                        <i class="bi bi-bell text-lg"></i>
                        <!-- Notification Badge -->
                        @if($unreadNotificationsCount > 0)
                        <div class="absolute inline-flex items-center justify-center w-4 h-4 text-[9px] font-bold text-white bg-rose-500 border-2 border-white dark:border-slate-800 rounded-full -top-1 -right-1">{{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}</div>
                        @endif
                    </button>
                    
                    <!-- Notification Dropdown menu -->
                    <div id="notification-dropdown" class="z-50 hidden my-4 w-80 max-w-sm text-base list-none bg-white divide-y divide-slate-100 rounded-xl shadow-xl dark:bg-slate-800 dark:divide-slate-700 border border-slate-100 dark:border-slate-700">
                        <div class="flex items-center justify-between px-4 py-3 font-medium text-slate-700 bg-slate-50 dark:bg-slate-800 dark:text-white rounded-t-xl border-b border-slate-100 dark:border-slate-700">
                            <span>Thông báo</span>
                            @if($unreadNotificationsCount > 0)
                            <form action="{{ route('admin.notifications.mark_all_read') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">Đánh dấu đã đọc</button>
                            </form>
                            @endif
                        </div>
                        <div class="divide-y divide-slate-100 dark:divide-slate-700 max-h-80 overflow-y-auto custom-scrollbar">
                            @forelse($recentNotifications as $notification)
                                @php
                                    $isUnread = is_null($notification->read_at);
                                    $data = $notification->data;
                                    $type = $data['type'] ?? 'default';
                                    
                                    $iconClass = 'bi-bell';
                                    $bgClass = 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400';
                                    
                                    if ($type === 'new_order') {
                                        $iconClass = 'bi-cart-check';
                                        $bgClass = 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400';
                                    } elseif ($type === 'new_message') {
                                        $iconClass = 'bi-chat-dots';
                                        $bgClass = 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400';
                                    } elseif ($type === 'low_stock') {
                                        $iconClass = 'bi-exclamation-triangle';
                                        $bgClass = 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400';
                                    }
                                @endphp
                                <a href="{{ $data['url'] ?? '#' }}" onclick="markNotificationAsRead('{{ $notification->id }}', this)" class="flex px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors relative {{ $isUnread ? 'bg-primary-50/30 dark:bg-primary-900/10' : '' }}">
                                    @if($isUnread)
                                        <div class="absolute w-2 h-2 rounded-full bg-primary-600 top-4 right-4 notification-dot"></div>
                                    @endif
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="w-9 h-9 rounded-full {{ $bgClass }} flex items-center justify-center">
                                            <i class="bi {{ $iconClass }}"></i>
                                        </div>
                                    </div>
                                    <div class="w-full pl-3 pr-4">
                                        <div class="text-sm mb-1 text-slate-800 dark:text-slate-200 {{ $isUnread ? 'font-semibold' : '' }} line-clamp-2">
                                            {{ $data['message'] ?? 'Bạn có thông báo mới' }}
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400 flex flex-col items-center">
                                    <i class="bi bi-bell-slash text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                    <span>Không có thông báo nào</span>
                                </div>
                            @endforelse
                        </div>
                        <a href="{{ route('admin.notifications.index') }}" class="block py-2.5 text-sm font-medium text-center text-slate-900 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-white rounded-b-xl transition-colors border-t border-slate-100 dark:border-slate-700">
                            <div class="inline-flex items-center">
                                Xem tất cả thông báo
                            </div>
                        </a>
                    </div>

                    <!-- Theme Switcher (1-Click Toggle Light/Dark) -->
                    <button id="theme-toggle" type="button" class="text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-200 dark:focus:ring-slate-700 rounded-lg p-2 mr-2 transition-colors cursor-pointer" title="Chuyển chế độ Sáng / Tối">
                        <i id="theme-toggle-dark-icon" class="bi bi-moon-stars-fill text-lg hidden"></i>
                        <i id="theme-toggle-light-icon" class="bi bi-sun-fill text-lg text-amber-500 hidden"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Floating Panel Sidebar -->
    <aside id="logo-sidebar" class="fixed top-2 left-2 bottom-2 z-40 w-64 h-[calc(100vh-1rem)] flex flex-col transition-transform -translate-x-full sm:translate-x-0 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm transition-colors duration-200" aria-label="Sidebar">
        
        <!-- Sticky Header (Logo & Profile) -->
        <div class="px-3 pt-4 pb-2 shrink-0">
            <!-- Logo -->
            <div class="flex items-center justify-between px-2 mb-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/nenlogoaureliawwhite.png') }}" alt="Aurelia Logo" class="h-10 w-auto object-contain">
                    <span class="text-2xl font-bold whitespace-nowrap text-slate-800 dark:text-white tracking-tight leading-none mt-1">Aurelia</span>
                </a>
                <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                    <i class="bi bi-layout-sidebar"></i>
                </button>
            </div>

            <!-- User Profile Block -->
            @auth
            <div class="px-2 mb-2">
                <div class="flex items-center gap-3 p-2 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors cursor-pointer" data-dropdown-toggle="sidebar-user-dropdown" data-dropdown-placement="bottom-start">
                    @if(auth()->user()->avatar)
                        <img class="w-10 h-10 rounded-full object-cover border border-slate-200 dark:border-slate-600" src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="User avatar">
                    @else
                        <img class="w-10 h-10 rounded-full object-cover border border-slate-200 dark:border-slate-600" src="https://ui-avatars.com/api/?name=Admin&background=0D8ABC&color=fff" alt="User avatar">
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
                        <div class="flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400">
                            <span class="inline-block w-3 h-3 bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-400 rounded-sm flex items-center justify-center text-[8px] font-bold">PRO</span>
                            <span class="truncate">{{ auth()->user()->role == 'admin' ? 'Administration' : 'User' }}</span>
                        </div>
                    </div>
                    <i class="bi bi-chevron-down text-slate-400 text-xs"></i>
                </div>
                
                <!-- Sidebar User Dropdown -->
                <div id="sidebar-user-dropdown" class="z-50 hidden w-56 text-base list-none bg-white divide-y divide-slate-100 rounded-lg shadow-lg dark:bg-slate-700 dark:divide-slate-600 border border-slate-100 dark:border-slate-600">
                    <div class="px-4 py-3" role="none">
                        <p class="text-sm font-medium text-slate-500 truncate dark:text-slate-400" role="none">{{ auth()->user()->email }}</p>
                    </div>
                    <ul class="py-1" role="none">
                        <li>
                            <a href="{{ route('admin.users.edit', auth()->id()) }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-600"><i class="bi bi-person mr-2"></i>Hồ sơ</a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-slate-100 dark:text-rose-500 dark:hover:bg-slate-600"><i class="bi bi-box-arrow-right mr-2"></i>Đăng xuất</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
            @endauth
        </div>

        <!-- Scrollable Menu Area -->
        <div class="flex-1 px-3 pb-4 overflow-y-auto sidebar-scrollbar flex flex-col justify-between">
            <div>
                <!-- Search Bar -->
                <div class="px-2 mb-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="bi bi-search text-slate-400"></i>
                        </div>
                        <input type="text" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-9 p-2 transition-colors" placeholder="Search">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                            <kbd class="px-1.5 py-0.5 text-[10px] font-semibold text-slate-500 bg-slate-100 border border-slate-200 rounded-md dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700">⌘F</kbd>
                        </div>
                    </div>
                </div>

                <!-- Main Menu -->
                <ul class="space-y-1 font-medium mt-2">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition-all duration-200 group {{ request()->routeIs('admin.dashboard') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-slate-200 font-normal' }}">
                            <div class="flex items-center gap-3 text-sm">
                                <i class="bi bi-grid text-base {{ request()->routeIs('admin.dashboard') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                                <span>Dashboard</span>
                            </div>
                        </a>
                    </li>
                    
                    <li>
                        <a href="{{ route('admin.chat.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition-all duration-200 group {{ request()->routeIs('admin.chat.*') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-slate-200 font-normal' }}">
                            <div class="flex items-center gap-3 text-sm">
                                <i class="bi bi-chat-square-text text-base {{ request()->routeIs('admin.chat.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-500 dark:group-hover:text-slate-400' }}"></i>
                                <span>Messages</span>
                            </div>
                            @php
                                $unreadTotal = \App\Models\Conversation::where('status','open')->count();
                            @endphp
                            @if($unreadTotal > 0)
                            <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-primary-500 rounded-full">{{ $unreadTotal > 9 ? '9+' : $unreadTotal }}</span>
                            @endif
                        </a>
                    </li>



                    <li>
                        <a href="{{ route('admin.analytics.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition-all duration-200 group {{ request()->routeIs('admin.analytics.*') ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-slate-200 font-normal' }}">
                            <div class="flex items-center gap-3 text-sm">
                                <i class="bi bi-bar-chart text-base {{ request()->routeIs('admin.analytics.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-500 dark:group-hover:text-slate-400' }}"></i>
                                <span>Analytics</span>
                            </div>
                        </a>
                    </li>
                </ul>

                <!-- Product Management Section -->
                <div class="mt-6">
                    <button type="button" class="flex items-center justify-between w-full px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 transition-colors group cursor-pointer" aria-controls="dropdown-products" data-collapse-toggle="dropdown-products">
                        <span>Product Management</span>
                        <i class="bi bi-chevron-down text-[10px] text-slate-400 transition-transform duration-200 {{ request()->routeIs('admin.products.*') || request()->routeIs('admin.categories.*') || request()->routeIs('admin.attributes.*') || request()->routeIs('admin.colors.*') || request()->routeIs('admin.sizes.*') || request()->routeIs('admin.materials.*') ? 'rotate-180' : 'group-data-[collapse-open]:rotate-180' }}"></i>
                    </button>
                    <ul id="dropdown-products" class="{{ request()->routeIs('admin.products.*') || request()->routeIs('admin.categories.*') || request()->routeIs('admin.attributes.*') || request()->routeIs('admin.colors.*') || request()->routeIs('admin.sizes.*') || request()->routeIs('admin.materials.*') ? '' : 'hidden' }} space-y-1 py-1 mt-1">
                        <li>
                            <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-3 py-2 pl-9 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.products.*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-50/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                                <i class="bi bi-box-seam text-base {{ request()->routeIs('admin.products.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}"></i>
                                <span>Products</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3 py-2 pl-9 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.categories.*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-50/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                                <i class="bi bi-folder2 text-base {{ request()->routeIs('admin.categories.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}"></i>
                                <span>Categories</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.attributes.index') }}" class="flex items-center gap-3 px-3 py-2 pl-9 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.attributes.*') || request()->routeIs('admin.colors.*') || request()->routeIs('admin.sizes.*') || request()->routeIs('admin.materials.*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-50/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                                <i class="bi bi-tags text-base {{ request()->routeIs('admin.attributes.*') || request()->routeIs('admin.colors.*') || request()->routeIs('admin.sizes.*') || request()->routeIs('admin.materials.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}"></i>
                                <span>Attributes</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Warehouse Section -->
                <div class="mt-4">
                    <button type="button" class="flex items-center justify-between w-full px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 transition-colors group cursor-pointer" aria-controls="dropdown-warehouse" data-collapse-toggle="dropdown-warehouse">
                        <span>Warehouse & Inventory</span>
                        <i class="bi bi-chevron-down text-[10px] text-slate-400 transition-transform duration-200 {{ request()->routeIs('admin.imports.*') || request()->routeIs('admin.inventory.*') || request()->routeIs('admin.inventory_history.*') ? 'rotate-180' : 'group-data-[collapse-open]:rotate-180' }}"></i>
                    </button>
                    <ul id="dropdown-warehouse" class="{{ request()->routeIs('admin.imports.*') || request()->routeIs('admin.inventory.*') || request()->routeIs('admin.inventory_history.*') ? '' : 'hidden' }} space-y-1 py-1 mt-1">
                        <li>
                            <a href="{{ route('admin.imports.index') }}" class="flex items-center gap-3 px-3 py-2 pl-9 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.imports.*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-50/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                                <i class="bi bi-box-arrow-in-down text-base {{ request()->routeIs('admin.imports.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}"></i>
                                <span>Nhập kho</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.inventory.index') }}" class="flex items-center gap-3 px-3 py-2 pl-9 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.inventory.*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-50/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                                <i class="bi bi-boxes text-base {{ request()->routeIs('admin.inventory.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}"></i>
                                <span>Tồn kho (Stock)</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.inventory_history.index') }}" class="flex items-center gap-3 px-3 py-2 pl-9 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.inventory_history.*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-50/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                                <i class="bi bi-clock-history text-base {{ request()->routeIs('admin.inventory_history.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}"></i>
                                <span>Thẻ kho</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Orders Section -->
                <div class="mt-4">
                    <button type="button" class="flex items-center justify-between w-full px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 transition-colors group cursor-pointer" aria-controls="dropdown-orders" data-collapse-toggle="dropdown-orders">
                        <span>Orders & Fulfillment</span>
                        <i class="bi bi-chevron-down text-[10px] text-slate-400 transition-transform duration-200 {{ request()->routeIs('admin.orders.*') ? 'rotate-180' : '' }}"></i>
                    </button>
                    <ul id="dropdown-orders" class="{{ request()->routeIs('admin.orders.*') ? '' : 'hidden' }} space-y-1 py-1 mt-1">
                        <li>
                            <a href="{{ route('admin.orders.index') }}" class="flex items-center justify-between px-3 py-2 pl-9 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.orders.*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-50/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                                <div class="flex items-center gap-3">
                                    <i class="bi bi-cart3 text-base {{ request()->routeIs('admin.orders.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}"></i>
                                    <span>Danh sách Đơn hàng</span>
                                </div>
                            </a>
                        </li>

                    </ul>
                </div>

                <!-- Finance Section -->
                <div class="mt-4">
                    <button type="button" class="flex items-center justify-between w-full px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 transition-colors group cursor-pointer" aria-controls="dropdown-finance" data-collapse-toggle="dropdown-finance">
                        <span>Tài chính & Dòng tiền</span>
                        <i class="bi bi-chevron-down text-[10px] text-slate-400 transition-transform duration-200 {{ request()->routeIs('admin.finance.*') || request()->routeIs('admin.transactions.*') ? 'rotate-180' : 'group-data-[collapse-open]:rotate-180' }}"></i>
                    </button>
                    <ul id="dropdown-finance" class="{{ request()->routeIs('admin.finance.*') || request()->routeIs('admin.transactions.*') ? '' : 'hidden' }} space-y-1 py-1 mt-1">
                        <li>
                            <a href="{{ route('admin.finance.index') }}" class="flex items-center gap-3 px-3 py-2 pl-9 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.finance.*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-50/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                                <i class="bi bi-bar-chart-line text-base {{ request()->routeIs('admin.finance.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}"></i>
                                <span>Thống kê tài chính</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.transactions.index') }}" class="flex items-center gap-3 px-3 py-2 pl-9 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.transactions.*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-50/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                                <i class="bi bi-credit-card text-base {{ request()->routeIs('admin.transactions.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}"></i>
                                <span>Danh sách giao dịch</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- Marketing Section -->
                <div class="mt-4">
                    <button type="button" class="flex items-center justify-between w-full px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 transition-colors group cursor-pointer" aria-controls="dropdown-marketing" data-collapse-toggle="dropdown-marketing">
                        <span>Marketing & Khuyến mãi</span>
                        <i class="bi bi-chevron-down text-[10px] text-slate-400 transition-transform duration-200 {{ request()->routeIs('admin.coupons.*') || request()->routeIs('admin.flash_sales.*') || request()->routeIs('admin.banners.*') || request()->routeIs('admin.posts.*') ? 'rotate-180' : 'group-data-[collapse-open]:rotate-180' }}"></i>
                    </button>
                    <ul id="dropdown-marketing" class="{{ request()->routeIs('admin.coupons.*') || request()->routeIs('admin.flash_sales.*') || request()->routeIs('admin.banners.*') || request()->routeIs('admin.posts.*') ? '' : 'hidden' }} space-y-1 py-1 mt-1">
                        <li>
                            <a href="{{ route('admin.banners.index') }}" class="flex items-center gap-3 px-3 py-2 pl-9 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.banners.*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-50/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                                <i class="bi bi-image text-base {{ request()->routeIs('admin.banners.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}"></i>
                                <span>Banner Trang Chủ</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.coupons.index') }}" class="flex items-center gap-3 px-3 py-2 pl-9 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.coupons.*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-50/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                                <i class="bi bi-ticket-perforated text-base {{ request()->routeIs('admin.coupons.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}"></i>
                                <span>Mã Giảm Giá</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.flash_sales.index') }}" class="flex items-center gap-3 px-3 py-2 pl-9 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.flash_sales.*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-50/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                                <i class="bi bi-lightning-charge text-base {{ request()->routeIs('admin.flash_sales.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}"></i>
                                <span>Flash Sale</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.posts.index') }}" class="flex items-center gap-3 px-3 py-2 pl-9 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.posts.*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-50/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                                <i class="bi bi-journal-text text-base {{ request()->routeIs('admin.posts.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}"></i>
                                <span>Tạp chí / Lookbook</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- System Section -->
                <div class="mt-4 mb-4">
                    <button type="button" class="flex items-center justify-between w-full px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 transition-colors group cursor-pointer" aria-controls="dropdown-system" data-collapse-toggle="dropdown-system">
                        <span>Hệ thống</span>
                        <i class="bi bi-chevron-down text-[10px] text-slate-400 transition-transform duration-200 {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.settings.*') ? 'rotate-180' : 'group-data-[collapse-open]:rotate-180' }}"></i>
                    </button>
                    <ul id="dropdown-system" class="{{ request()->routeIs('admin.users.*') || request()->routeIs('admin.settings.*') ? '' : 'hidden' }} space-y-1 py-1 mt-1">
                        <li>
                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 pl-9 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.users.*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-50/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                                <i class="bi bi-people text-base {{ request()->routeIs('admin.users.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}"></i>
                                <span>Tài khoản</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3 py-2 pl-9 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.settings.*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-50/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                                <i class="bi bi-gear text-base {{ request()->routeIs('admin.settings.*') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}"></i>
                                <span>Cài đặt chung</span>
                            </a>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="@yield('content_class', 'p-4 sm:ml-[272px] mt-14')">


        @yield('content')
    </div>

    <!-- No Bootstrap JS -->
    <!-- Axios for AJAX -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Setup CSRF token for Axios
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        let token = document.head.querySelector('meta[name="csrf-token"]');
        if (token) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        }
    </script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Global Notifications (Toast) -->
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: '{{ session('success') }}'
            });
        @endif

        @if(session('error'))
            Toast.fire({
                icon: 'error',
                title: '{{ session('error') }}'
            });
        @endif

        @if(session('warning'))
            Toast.fire({
                icon: 'warning',
                title: '{{ session('warning') }}'
            });
        @endif

        @if(session('info'))
            Toast.fire({
                icon: 'info',
                title: '{{ session('info') }}'
            });
        @endif

        @if($errors->any())
            Toast.fire({
                icon: 'error',
                title: '{{ $errors->first() }}'
            });
        @endif
    </script>

    <!-- Global SweetAlert2 Handlers (Alert override & Form confirmations) -->
    <script>
        // Override native window.alert with modern SweetAlert2
        window.alert = function(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Thông báo',
                    text: String(message),
                    icon: 'info',
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'Đóng'
                });
            } else {
                console.log('Alert:', message);
            }
        };

        // Delegated submit handler for forms with .form-delete or .form-confirm
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form || !form.classList) return;

            const isDelete = form.classList.contains('form-delete');
            const isConfirm = form.classList.contains('form-confirm');

            if (isDelete || isConfirm) {
                e.preventDefault();
                const title = form.dataset.confirmTitle || (isDelete ? 'Xóa dữ liệu?' : 'Xác nhận hành động?');
                const text = form.dataset.confirmText || (isDelete ? 'Hành động này không thể hoàn tác!' : 'Bạn có chắc chắn muốn thực hiện hành động này?');
                const icon = form.dataset.confirmIcon || (isDelete ? 'warning' : 'question');
                const confirmBtn = form.dataset.confirmBtn || (isDelete ? '<i class="bi bi-trash mr-1"></i> Đồng ý xóa' : '<i class="bi bi-check-lg mr-1"></i> Đồng ý');
                const confirmColor = form.dataset.confirmColor || (isDelete ? '#ef4444' : '#4f46e5');

                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonColor: confirmColor,
                    cancelButtonColor: '#64748b',
                    confirmButtonText: confirmBtn,
                    cancelButtonText: 'Hủy',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    </script>
    
    <!-- Theme Switcher Logic (Instant 1-Click Toggle Light/Dark) -->
    <script>
        (function() {
            function updateThemeUI(isDark) {
                const darkIcon = document.getElementById('theme-toggle-dark-icon');
                const lightIcon = document.getElementById('theme-toggle-light-icon');
                const btn = document.getElementById('theme-toggle');

                if (darkIcon && lightIcon) {
                    if (isDark) {
                        darkIcon.classList.add('hidden');
                        lightIcon.classList.remove('hidden');
                        if (btn) btn.title = 'Chuyển sang chế độ Sáng';
                    } else {
                        lightIcon.classList.add('hidden');
                        darkIcon.classList.remove('hidden');
                        if (btn) btn.title = 'Chuyển sang chế độ Tối';
                    }
                }
            }

            window.toggleTheme = function() {
                const isCurrentlyDark = document.documentElement.classList.contains('dark');
                const newTheme = isCurrentlyDark ? 'light' : 'dark';
                localStorage.setItem('color-theme', newTheme);
                document.documentElement.classList.toggle('dark', newTheme === 'dark');
                updateThemeUI(newTheme === 'dark');
            };

            document.addEventListener('DOMContentLoaded', function() {
                const currentTheme = localStorage.getItem('color-theme');
                const isDark = currentTheme === 'dark' || (!currentTheme && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', isDark);
                updateThemeUI(isDark);

                const btn = document.getElementById('theme-toggle');
                if (btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        window.toggleTheme();
                    });
                }
            });

            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (!localStorage.getItem('color-theme')) {
                    document.documentElement.classList.toggle('dark', e.matches);
                    updateThemeUI(e.matches);
                }
            });
        })();
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Sidebar dropdown accordion toggles
            document.querySelectorAll('[data-collapse-toggle]').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('data-collapse-toggle') || this.getAttribute('aria-controls');
                    const targetEl = document.getElementById(targetId);
                    if (targetEl) {
                        targetEl.classList.toggle('hidden');
                        const icon = this.querySelector('.bi-chevron-down');
                        if (icon) {
                            icon.classList.toggle('rotate-180');
                        }
                    }
                });
            });
        });

        function markNotificationAsRead(id, element) {
            axios.post(`/admin/notifications/${id}/read`)
                .then(response => {
                    if(response.data.success) {
                        element.classList.remove('bg-primary-50/30', 'dark:bg-primary-900/10');
                        let dot = element.querySelector('.notification-dot');
                        if (dot) dot.remove();
                        let title = element.querySelector('.line-clamp-2');
                        if (title) title.classList.remove('font-semibold');
                    }
                })
                .catch(error => console.error('Error marking notification as read:', error));
        }
    </script>
    
    @stack('scripts')
    @yield('scripts')
</body>
</html>

