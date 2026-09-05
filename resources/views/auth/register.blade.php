<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Tài Khoản - AURELIA</title>
    
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
                <div class="bg-white max-w-[440px] w-full py-[1.5rem] px-[2rem] rounded-[16px] shadow-[0_8px_30px_rgba(0,0,0,0.06)] border border-black/5">
                    <!-- Logo Aurelia -->
                    <div class="text-center mb-2">
                        <a href="/">
                            <img src="{{ asset('images/nenlogoaureliawwhite.png') }}" alt="Aurelia Logo" class="max-h-[55px] w-auto object-contain transition-transform duration-300 hover:scale-105 mx-auto">
                        </a>
                    </div>

                    <h2 class="text-center font-bold text-[1.45rem] mb-[2px] text-gray-900">Đăng Ký Tài Khoản</h2>
                    <p class="text-[0.85rem] text-gray-500 text-center mb-[15px]">Vui lòng điền thông tin để đăng ký tài khoản mới.</p>

                    <form method="POST" action="{{ route('register') }}" x-data="otpForm()">
                        @csrf

                        <!-- Name -->
                        <div class="mb-2">
                            <label for="name" class="block font-medium text-[0.82rem] text-gray-700 mb-[0.2rem]">Họ và tên</label>
                            <div class="relative flex items-center group">
                                <span class="absolute left-3 text-gray-500 group-focus-within:text-brand transition-colors"><i class="bi bi-person"></i></span>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" class="w-full pl-[2.1rem] pr-3 py-[0.4rem] bg-white border border-gray-300 rounded-md text-[0.88rem] placeholder-gray-400 focus:ring-[4px] focus:ring-brand/25 focus:border-brand focus:outline-none transition-shadow @error('name') border-red-500 @enderror" placeholder="Nhập họ và tên của bạn" required autofocus autocomplete="name">
                            </div>
                            @error('name')
                                <div class="text-red-500 text-[0.8rem] mt-[0.15rem] block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email Address -->
                        <div class="mb-2">
                            <label for="email" class="block font-medium text-[0.82rem] text-gray-700 mb-[0.2rem]">Email</label>
                            <div class="relative flex items-center group gap-2">
                                <div class="relative flex-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 group-focus-within:text-brand transition-colors"><i class="bi bi-envelope"></i></span>
                                    <input type="email" id="email" name="email" x-model="email" class="w-full pl-[2.1rem] pr-3 py-[0.4rem] bg-white border border-gray-300 rounded-md text-[0.88rem] placeholder-gray-400 focus:ring-[4px] focus:ring-brand/25 focus:border-brand focus:outline-none transition-shadow @error('email') border-red-500 @enderror" placeholder="Nhập địa chỉ email" required autocomplete="username">
                                </div>
                                <button type="button" @click="sendOtp" :disabled="isSending || countdown > 0" class="shrink-0 bg-brand text-white border border-brand rounded-md py-[0.4rem] px-3 font-semibold text-[0.8rem] disabled:opacity-50 hover:bg-brand-hover transition-colors min-w-[90px]">
                                    <span x-show="!isSending && countdown === 0">Gửi mã</span>
                                    <span x-show="isSending"><i class="bi bi-arrow-repeat animate-spin inline-block"></i></span>
                                    <span x-show="countdown > 0" x-text="countdown + 's'"></span>
                                </button>
                            </div>
                            <div x-show="message" :class="isError ? 'text-red-500' : 'text-green-500'" class="text-[0.8rem] mt-[0.2rem]" x-text="message" style="display: none;"></div>
                            @error('email')
                                <div class="text-red-500 text-[0.8rem] mt-[0.15rem] block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Mã xác thực (OTP) -->
                        <div class="mb-2">
                            <label for="otp_code" class="block font-medium text-[0.82rem] text-gray-700 mb-[0.2rem]">Mã xác thực (OTP)</label>
                            <div class="relative flex items-center group">
                                <span class="absolute left-3 text-gray-500 group-focus-within:text-brand transition-colors"><i class="bi bi-shield-check"></i></span>
                                <input type="text" id="otp_code" name="otp_code" value="{{ old('otp_code') }}" class="w-full pl-[2.1rem] pr-3 py-[0.4rem] bg-white border border-gray-300 rounded-md text-[0.88rem] placeholder-gray-400 focus:ring-[4px] focus:ring-brand/25 focus:border-brand focus:outline-none transition-shadow @error('otp_code') border-red-500 @enderror" placeholder="Nhập mã 6 số từ email" required>
                            </div>
                            @error('otp_code')
                                <div class="text-red-500 text-[0.8rem] mt-[0.15rem] block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-2" x-data="{ show: false }">
                            <label for="password" class="block font-medium text-[0.82rem] text-gray-700 mb-[0.2rem]">Mật khẩu</label>
                            <div class="relative flex items-center group">
                                <span class="absolute left-3 text-gray-500 group-focus-within:text-brand transition-colors"><i class="bi bi-lock"></i></span>
                                <input :type="show ? 'text' : 'password'" id="password" name="password" class="w-full pl-[2.1rem] pr-10 py-[0.4rem] bg-white border border-gray-300 rounded-md text-[0.88rem] placeholder-gray-400 focus:ring-[4px] focus:ring-brand/25 focus:border-brand focus:outline-none transition-shadow @error('password') border-red-500 @enderror" placeholder="Nhập mật khẩu" required autocomplete="new-password">
                                <button type="button" @click="show = !show" class="absolute right-3 text-gray-500 hover:text-gray-700 focus:outline-none group-focus-within:text-brand transition-colors">
                                    <i class="bi" :class="show ? 'bi-eye-slash' : 'bi-eye'"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="text-red-500 text-[0.8rem] mt-[0.15rem] block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-2" x-data="{ show: false }">
                            <label for="password_confirmation" class="block font-medium text-[0.82rem] text-gray-700 mb-[0.2rem]">Xác nhận mật khẩu</label>
                            <div class="relative flex items-center group">
                                <span class="absolute left-3 text-gray-500 group-focus-within:text-brand transition-colors"><i class="bi bi-lock"></i></span>
                                <input :type="show ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" class="w-full pl-[2.1rem] pr-10 py-[0.4rem] bg-white border border-gray-300 rounded-md text-[0.88rem] placeholder-gray-400 focus:ring-[4px] focus:ring-brand/25 focus:border-brand focus:outline-none transition-shadow @error('password_confirmation') border-red-500 @enderror" placeholder="Nhập lại mật khẩu" required autocomplete="new-password">
                                <button type="button" @click="show = !show" class="absolute right-3 text-gray-500 hover:text-gray-700 focus:outline-none group-focus-within:text-brand transition-colors">
                                    <i class="bi" :class="show ? 'bi-eye-slash' : 'bi-eye'"></i>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <div class="text-red-500 text-[0.8rem] mt-[0.15rem] block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex justify-between items-center mt-3">
                            <a class="text-brand font-medium text-[0.85rem] hover:text-brand-hover hover:underline transition-colors duration-200" href="{{ route('login') }}">
                                Đã có tài khoản?
                            </a>

                            <button type="submit" class="bg-brand text-white border border-brand rounded-lg py-[9px] px-[16px] font-semibold text-[0.92rem] mt-0 hover:bg-brand-hover hover:-translate-y-[1px] hover:shadow-[0_4px_12px_rgba(253,129,122,0.35)] transition-all duration-300">
                                Đăng Ký Ngay
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script xử lý OTP bằng AlpineJS -->
    <script>
        function otpForm() {
            return {
                email: '{{ old('email') }}',
                isSending: false,
                countdown: 0,
                message: '',
                isError: false,
                
                async sendOtp() {
                    if (!this.email) {
                        this.isError = true;
                        this.message = 'Vui lòng nhập email trước khi gửi mã.';
                        return;
                    }
                    
                    this.isSending = true;
                    this.message = '';
                    this.isError = false;
                    
                    try {
                        const response = await axios.post('{{ route('register.send_otp') }}', {
                            email: this.email
                        });
                        
                        this.isError = false;
                        this.message = response.data.message;
                        
                        // Start countdown
                        this.countdown = 60;
                        const timer = setInterval(() => {
                            this.countdown--;
                            if (this.countdown <= 0) {
                                clearInterval(timer);
                            }
                        }, 1000);
                        
                    } catch (error) {
                        this.isError = true;
                        if (error.response && error.response.data.errors && error.response.data.errors.email) {
                            this.message = error.response.data.errors.email[0];
                        } else {
                            this.message = 'Có lỗi xảy ra khi gửi mã xác thực. Vui lòng thử lại.';
                        }
                    } finally {
                        this.isSending = false;
                    }
                }
            }
        }
    </script>

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
