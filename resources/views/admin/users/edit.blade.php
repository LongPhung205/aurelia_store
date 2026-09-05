@extends('admin.layouts.admin')

@section('content')
<div class="w-full h-[calc(100vh-90px)] flex flex-col max-w-5xl mx-auto bg-gray-50/50 sm:rounded-2xl overflow-hidden">
    <!-- Fixed Header -->
    <div class="flex justify-between items-center p-5 sm:px-8 shrink-0 bg-white border-b border-gray-100 dark:bg-gray-900 dark:border-gray-800 rounded-t-2xl">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Chỉnh sửa Tài Khoản</h2>
        <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1 transition-colors">
            &larr; Quay lại
        </a>
    </div>

    <!-- Scrollable Body -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-5 sm:p-8">
        @if ($errors->any())
            <div class="mb-5 bg-red-50 border-l-4 border-red-500 text-red-700 p-3.5 rounded-r-lg shadow-sm">
                <div class="flex items-center mb-1.5">
                    <i class="bi bi-exclamation-triangle-fill mr-2"></i>
                    <span class="font-semibold text-sm">Vui lòng kiểm tra lại thông tin:</span>
                </div>
                <ul class="list-disc pl-6 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data" id="editUserForm">
            @csrf
            @method('PUT')
            
            <div class="flex flex-col gap-6">
                <!-- Main Info Card -->
                <div class="bg-white border border-gray-200 rounded-xl p-5 sm:p-6 shadow-sm dark:bg-gray-900 dark:border-gray-800 flex flex-col lg:flex-row gap-6 lg:gap-8 items-start">
                    
                    <!-- Left Column: Avatar -->
                    <div class="w-full lg:w-1/3 flex flex-col items-center">
                        <div class="w-full bg-blue-50/50 border border-gray-100 rounded-xl p-6 flex flex-col items-center justify-center mb-4 dark:bg-gray-800/50 dark:border-gray-700">
                            <div class="relative w-32 h-32 mb-4 group">
                                @if($user->avatar)
                                    <img id="avatar-image" src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="w-full h-full rounded-2xl object-cover shadow-sm border-4 border-white dark:border-gray-700">
                                @else
                                    <div id="avatar-placeholder" class="w-full h-full rounded-2xl bg-gray-200 dark:bg-gray-700 flex items-center justify-center shadow-sm border-4 border-white dark:border-gray-800">
                                        <i class="bi bi-person text-5xl text-gray-400 dark:text-gray-500"></i>
                                    </div>
                                    <img id="avatar-image" src="" alt="Avatar" class="hidden w-full h-full rounded-2xl object-cover shadow-sm border-4 border-white dark:border-gray-700">
                                @endif
                            </div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Ảnh đại diện (Avatar)</span>
                        </div>
                        
                        <div class="w-full relative">
                            <input type="file" name="avatar" id="avatar" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*" onchange="previewAvatar(event)">
                            <button type="button" class="w-full px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 pointer-events-none flex items-center justify-center">
                                Chọn ảnh
                            </button>
                        </div>
                        <p class="mt-3 text-xs text-center text-gray-500 dark:text-gray-400 px-4">Chỉ chấp nhận file ảnh (PNG, JPG, JPEG, GIF). Tối đa 2MB.</p>
                    </div>

                    <!-- Right Column: User Info -->
                    <div class="w-full lg:w-2/3 flex flex-col">
                        <!-- User header (Name and badges) -->
                        <div class="mb-6 border-b border-gray-100 pb-4 dark:border-gray-800">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $user->name }}</h3>
                            <div class="flex flex-wrap gap-2">
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-1 rounded-md dark:bg-blue-900/30 dark:text-blue-300">Hoạt động</span>
                                <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-1 rounded-md dark:bg-gray-800 dark:text-gray-300">{{ $user->role == 'admin' ? 'Admin (Quản trị viên)' : 'User (Khách hàng/Người dùng)' }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4">
                            <div>
                                <label for="name" class="block mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300">Họ tên <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white transition-shadow hover:shadow-md" required>
                            </div>

                            <div>
                                <label for="email" class="block mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300">Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white transition-shadow hover:shadow-md" required>
                            </div>

                            <div>
                                <label for="role" class="block mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300">Vai trò <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select name="role" id="role" class="appearance-none bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 pr-10 shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white transition-shadow hover:shadow-md cursor-pointer" required>
                                        <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User (Khách hàng/Người dùng)</option>
                                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin (Quản trị viên)</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                        <i class="bi bi-chevron-down"></i>
                                    </div>
                                </div>
                                @if(auth()->id() === $user->id)
                                    <p class="mt-2 text-xs font-medium text-amber-600 bg-amber-50 inline-block px-2 py-1 rounded dark:bg-amber-900/30 dark:text-amber-500"><i class="bi bi-exclamation-triangle mr-1"></i> Đây là tài khoản của bạn. Bạn không thể tự hạ quyền của mình.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Password Card (Accordion style look) -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-gray-900 dark:border-gray-800" x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="w-full px-5 py-4 flex justify-between items-center bg-white hover:bg-gray-50 transition-colors dark:bg-gray-900 dark:hover:bg-gray-800 focus:outline-none">
                        <div class="flex items-center">
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Đổi mật khẩu (Tùy chọn)</h3>
                        </div>
                        <i class="bi bi-chevron-up text-gray-500 transition-transform duration-300" :class="{'rotate-180': !open}"></i>
                    </button>
                    
                    <div x-show="open" x-collapse>
                        <div class="p-5 sm:p-6 border-t border-gray-100 dark:border-gray-800">
                            <p class="text-sm text-gray-500 mb-4 dark:text-gray-400">Để trống 2 trường dưới đây nếu bạn không muốn thay đổi mật khẩu hiện tại.</p>
                            <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 sm:p-5 dark:bg-gray-800/50 dark:border-gray-700">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="password" class="block mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300">Mật khẩu mới</label>
                                        <input type="password" name="password" id="password" class="bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white transition-shadow hover:shadow-md" minlength="8">
                                    </div>
                                    <div>
                                        <label for="password_confirmation" class="block mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300">Nhập lại Mật khẩu mới</label>
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white transition-shadow hover:shadow-md" minlength="8">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Fixed Footer -->
    <div class="p-5 sm:px-8 shrink-0 bg-white border-t border-gray-200 dark:bg-gray-900 dark:border-gray-800 flex justify-end gap-3 rounded-b-2xl shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-10 relative">
        <a href="{{ route('admin.users.index') }}" class="text-gray-700 bg-white hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-100 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 transition-colors shadow-sm inline-flex items-center justify-center">Hủy</a>
        <button type="submit" form="editUserForm" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-6 py-2.5 text-center shadow-sm transition-all transform hover:-translate-y-0.5 inline-flex items-center justify-center">Lưu thay đổi</button>
    </div>
</div>

<!-- Alpine.js is assumed to be loaded. If not, add this script manually. -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>

<script>
    function previewAvatar(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const placeholder = document.getElementById('avatar-placeholder');
                const image = document.getElementById('avatar-image');
                
                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
                
                image.src = e.target.result;
                image.classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
