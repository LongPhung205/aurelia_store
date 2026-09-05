<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu - AURELIA</title>
    
    <!-- Tailwind CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
</head>
<body class="font-sans bg-gray-50 flex flex-col h-screen overflow-hidden m-0 p-0">
    <x-header />

    <div class="flex-1 flex flex-col overflow-hidden">
        <div class="flex-grow flex h-full">
            <!-- Left Side (Hero Image) -->
            <div class="hidden lg:flex lg:w-1/2 h-full bg-white flex-col">
                
                <!-- Hình ảnh kéo dài phần còn lại -->
                <div class="flex-grow relative overflow-hidden">
                    <img src="{{ asset('images/anhdaidien.jpg') }}" class="absolute inset-0 w-full h-full object-cover object-center" alt="Aurelia Collection">
                </div>
            </div>

            <!-- Right Side (Form) -->
            <div class="w-full lg:w-1/2 h-full flex items-center justify-center bg-brand-light py-[30px] px-[20px] overflow-y-auto">
                <div class="bg-white max-w-[440px] w-full py-[1.75rem] px-[2rem] rounded-[16px] shadow-[0_8px_30px_rgba(0,0,0,0.06)] border border-black/5">
                    <!-- Logo Aurelia -->
                    <div class="text-center mb-2">
                        <a href="/">
                            <img src="{{ asset('images/nenlogoaureliawwhite.png') }}" alt="Aurelia Logo" class="max-h-[60px] w-auto object-contain transition-transform duration-300 hover:scale-105 mx-auto">
                        </a>
                    </div>

                    <h2 class="text-center font-bold text-[1.5rem] mb-[3px] text-gray-900">Quên Mật Khẩu?</h2>
                    <p class="text-[0.88rem] text-gray-500 text-center mb-[18px] leading-[1.4]">Nhập địa chỉ email để nhận liên kết đặt lại mật khẩu của bạn.</p>

                    <!-- Session Status -->
                    @if(session('status'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded relative mb-3 text-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <!-- Email Address -->
                        <div class="mb-3">
                            <label for="email" class="block font-medium text-[0.85rem] text-gray-700 mb-[0.3rem]">Email</label>
                            <div class="relative flex items-center group">
                                <span class="absolute left-3 text-gray-500 group-focus-within:text-brand transition-colors"><i class="bi bi-envelope"></i></span>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full pl-[2.2rem] pr-3 py-[0.45rem] bg-white border border-gray-300 rounded-md text-[0.9rem] placeholder-gray-400 focus:ring-[4px] focus:ring-brand/25 focus:border-brand focus:outline-none transition-shadow @error('email') border-red-500 @enderror" placeholder="Nhập địa chỉ email của bạn" required autofocus>
                            </div>
                            @error('email')
                                <div class="text-red-500 text-[0.82rem] mt-[0.2rem] block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="w-full bg-brand text-white border border-brand rounded-lg py-[10px] px-[16px] font-semibold text-[0.95rem] mt-[10px] mb-3 hover:bg-brand-hover hover:-translate-y-[1px] hover:shadow-[0_4px_12px_rgba(253,129,122,0.35)] transition-all duration-300">
                            Gửi Liên Kết Đặt Lại Mật Khẩu
                        </button>
                        
                        <div class="text-center">
                            <a class="text-brand font-medium text-[0.88rem] hover:text-brand-hover hover:underline transition-colors duration-200" href="{{ route('login') }}">
                                <i class="bi bi-arrow-left mr-1"></i> Quay lại Đăng nhập
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 shrink-0 py-[11px] relative z-10 w-full shadow-[0_-2px_10px_rgba(0,0,0,0.02)]">
        <div class="w-full px-4 md:px-5">
            <div class="flex flex-wrap items-center justify-between gap-y-2">
                <!-- Left: Brand Name -->
                <div class="w-full md:w-auto text-center md:text-left">
                    <a href="/" class="inline-flex items-center gap-2 text-brand font-playfair font-bold text-[1.15rem] tracking-[2px] no-underline hover:text-brand-hover hover:opacity-90 transition-all duration-200">
                        <span>AURELIA</span>
                        <span class="hidden xl:inline text-gray-500 text-[0.72rem] font-sans font-normal tracking-[0.5px] border-l border-gray-200 pl-2">BOUTIQUE</span>
                    </a>
                </div>

                <!-- Center: Navigation Links -->
                <div class="w-full md:w-auto text-center">
                    <div class="flex flex-wrap justify-center items-center gap-[1.5rem]">
                        <a href="#" class="text-gray-600 text-[0.82rem] font-medium hover:text-brand transition-colors duration-200">Bền Vững</a>
                        <a href="#" class="text-gray-600 text-[0.82rem] font-medium hover:text-brand transition-colors duration-200">Giao Hàng</a>
                        <a href="#" class="text-gray-600 text-[0.82rem] font-medium hover:text-brand transition-colors duration-200">Đổi Trả</a>
                        <a href="#" class="text-gray-600 text-[0.82rem] font-medium hover:text-brand transition-colors duration-200">Liên Hệ</a>
                        <a href="#" class="text-gray-600 text-[0.82rem] font-medium hover:text-brand transition-colors duration-200">Bảo Mật</a>
                    </div>
                </div>

                <!-- Right: Copyright -->
                <div class="w-full md:w-auto text-center md:text-right">
                    <span class="text-[0.74rem] font-medium tracking-[0.5px] text-gray-400 uppercase whitespace-nowrap">© 2024 AURELIA BOUTIQUE. ĐÃ ĐĂNG KÝ BẢN QUYỀN.</span>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
