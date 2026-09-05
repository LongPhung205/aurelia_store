@extends('layouts.client')

@section('content')
<div class="max-w-7xl mx-auto py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-8 text-sm" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="/" class="text-gray-500 hover:text-brand transition-colors flex items-center">
                    <i class="bi bi-house-door mr-2"></i>
                    Trang chủ
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="bi bi-chevron-right text-gray-400 mx-2 text-xs"></i>
                    <span class="text-gray-900 font-medium">Hồ sơ cá nhân</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Menu -->
        <aside class="w-full lg:w-1/4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- User Summary -->
                <div class="p-6 border-b border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0">
                        @if(auth()->user()->avatar)
                            <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400 text-xl font-bold bg-gray-50">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm text-gray-500">Tài khoản của</p>
                        <p class="font-bold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex flex-col py-2">
                    <a href="{{ route('profile.index') }}" class="flex items-center gap-3 px-6 py-3 text-sm font-medium transition-colors {{ request()->routeIs('profile.index') ? 'text-brand bg-brand/5 border-l-2 border-brand' : 'text-gray-600 hover:bg-gray-50 hover:text-brand border-l-2 border-transparent' }}">
                        <i class="bi bi-person text-lg w-5"></i>
                        Thông tin tài khoản
                    </a>
                    
                    <a href="{{ route('profile.password') }}" class="flex items-center gap-3 px-6 py-3 text-sm font-medium transition-colors {{ request()->routeIs('profile.password') ? 'text-brand bg-brand/5 border-l-2 border-brand' : 'text-gray-600 hover:bg-gray-50 hover:text-brand border-l-2 border-transparent' }}">
                        <i class="bi bi-shield-lock text-lg w-5"></i>
                        Đổi mật khẩu
                    </a>

                    <a href="{{ route('profile.addresses') }}" class="flex items-center gap-3 px-6 py-3 text-sm font-medium transition-colors {{ request()->routeIs('profile.addresses') ? 'text-brand bg-brand/5 border-l-2 border-brand' : 'text-gray-600 hover:bg-gray-50 hover:text-brand border-l-2 border-transparent' }}">
                        <i class="bi bi-geo-alt text-lg w-5"></i>
                        Sổ địa chỉ
                    </a>

                    <a href="{{ route('profile.orders') }}" class="flex items-center gap-3 px-6 py-3 text-sm font-medium transition-colors {{ request()->routeIs('profile.orders') ? 'text-brand bg-brand/5 border-l-2 border-brand' : 'text-gray-600 hover:bg-gray-50 hover:text-brand border-l-2 border-transparent' }}">
                        <i class="bi bi-box-seam text-lg w-5"></i>
                        Quản lý đơn hàng
                    </a>
                    
                    <form method="POST" action="{{ route('logout') }}" class="mt-2 border-t border-gray-100">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-6 py-3 text-sm font-medium text-gray-600 hover:bg-red-50 hover:text-red-600 transition-colors border-l-2 border-transparent text-left">
                            <i class="bi bi-box-arrow-right text-lg w-5"></i>
                            Đăng xuất
                        </button>
                    </form>
                </nav>
            </div>
        </aside>

        <!-- Main Content Slot -->
        <main class="w-full lg:w-3/4">
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="bg-green-50 text-green-700 border border-green-200 rounded-lg p-4 mb-6 flex items-center gap-3">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 text-red-700 border border-red-200 rounded-lg p-4 mb-6 flex items-center gap-3">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    {{ session('error') }}
                </div>
            @endif

            @yield('profile_content')
        </main>
    </div>
</div>
@endsection
