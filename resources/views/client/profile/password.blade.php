@extends('client.profile.layout')

@section('profile_content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
    <h2 class="text-xl font-bold text-gray-900 mb-6 pb-4 border-b border-gray-100">Đổi Mật Khẩu</h2>
    <p class="text-sm text-gray-500 mb-8">Để bảo mật tài khoản, vui lòng không chia sẻ mật khẩu cho người khác</p>

    <form method="POST" action="{{ route('profile.password.update') }}" class="max-w-2xl">
        @csrf
        
        <div class="space-y-6">
            <!-- Current Password -->
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu hiện tại</label>
                <div class="relative">
                    <input type="password" name="current_password" id="current_password" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand focus:ring-brand pr-10 @error('current_password') border-red-500 @enderror">
                    <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600" onclick="togglePassword('current_password')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('current_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- New Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu mới</label>
                <div class="relative">
                    <input type="password" name="password" id="password" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand focus:ring-brand pr-10 @error('password') border-red-500 @enderror">
                    <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600" onclick="togglePassword('password')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Xác nhận mật khẩu</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand focus:ring-brand pr-10">
                    <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600" onclick="togglePassword('password_confirmation')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="bg-brand text-white px-8 py-2.5 rounded-lg font-medium hover:bg-[#C2185B] transition-colors shadow-sm">
                    Xác Nhận
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = input.nextElementSibling.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
</script>
@endsection
