<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="bg-[#FDFDFC] text-[#1b1b18] flex flex-col min-h-screen">
        <x-header />

        @yield('full_width_top')

        <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @yield('content')
        </main>
        <x-footer />
        
        <!-- Zalo/Chat Floating Icon -->
        <a href="#" class="fixed bottom-6 right-6 z-50 animate-bounce hover:animate-none group">
            <div class="bg-blue-500 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg group-hover:shadow-2xl transition-all">
                <!-- Fallback icon or text if no Zalo logo available -->
                <i class="bi bi-chat-dots text-3xl"></i>
            </div>
        </a>

        <!-- Global Toast Notification -->
        <div x-data="{ show: false, message: '', type: 'success' }" 
             @notify.window="message = $event.detail.message; type = $event.detail.type || 'success'; show = true; setTimeout(() => show = false, 3000)"
             class="fixed top-24 right-4 z-[9999] transition-all duration-300 transform"
             :class="show ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'"
             style="display: none;" x-show="show">
             <div class="px-6 py-4 rounded-lg shadow-xl text-white font-medium flex items-center gap-3"
                  :class="type === 'success' ? 'bg-brand' : (type === 'error' ? 'bg-red-500' : 'bg-gray-800')">
                  <i class="bi text-xl" :class="type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'"></i>
                  <span x-text="message"></span>
             </div>
        </div>
        
        @stack('scripts')
    </body>
</html>
