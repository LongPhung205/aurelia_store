@extends('client.profile.layout')

@section('profile_content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
    <h2 class="text-xl font-bold text-gray-900 mb-6 pb-4 border-b border-gray-100">Hồ Sơ Của Tôi</h2>
    <p class="text-sm text-gray-500 mb-8">Quản lý thông tin hồ sơ để bảo mật tài khoản</p>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="flex flex-col-reverse lg:flex-row gap-8 lg:gap-16">
            
            <!-- Form Fields -->
            <div class="flex-grow space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Họ và Tên</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand focus:ring-brand @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand focus:ring-brand @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand focus:ring-brand @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4">
                    <button type="submit" class="bg-brand text-white px-8 py-2.5 rounded-lg font-medium hover:bg-[#C2185B] transition-colors shadow-sm">
                        Lưu Thay Đổi
                    </button>
                </div>
            </div>

            <!-- Avatar Upload -->
            <div class="flex-shrink-0 flex flex-col items-center justify-start border-l-0 lg:border-l border-gray-100 pl-0 lg:pl-16">
                <div class="w-32 h-32 rounded-full overflow-hidden bg-gray-100 border-4 border-white shadow-lg mb-4 relative group cursor-pointer" onclick="document.getElementById('avatar').click()">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" id="avatar-preview" alt="{{ $user->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400 text-4xl font-bold bg-gray-50" id="avatar-fallback">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <img src="" id="avatar-preview" alt="Preview" class="w-full h-full object-cover hidden">
                    @endif
                    
                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <i class="bi bi-camera-fill text-white text-2xl"></i>
                    </div>
                </div>
                
                <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/jpg" class="hidden" onchange="previewImage(this)">
                
                <button type="button" onclick="document.getElementById('avatar').click()" class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors mb-2">
                    Chọn Ảnh
                </button>
                <p class="text-xs text-gray-400 text-center max-w-[150px]">
                    Dung lượng file tối đa 2MB<br>Định dạng: .JPEG, .PNG
                </p>
                
                @error('avatar')
                    <p class="mt-2 text-sm text-red-600 text-center">{{ $message }}</p>
                @enderror
            </div>

        </div>
    </form>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById('avatar-preview');
                var fallback = document.getElementById('avatar-fallback');
                
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if(fallback) fallback.classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
