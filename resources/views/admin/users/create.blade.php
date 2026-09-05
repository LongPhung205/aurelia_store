@extends('admin.layouts.admin')

@section('content')
<div class="w-full h-[calc(100vh-90px)] flex flex-col max-w-5xl mx-auto bg-white shadow-sm border border-gray-100 sm:rounded-2xl dark:bg-gray-900 dark:border-gray-800 overflow-hidden">
    <!-- Fixed Header -->
    <div class="flex justify-between items-center p-5 sm:px-8 shrink-0 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Thêm Tài Khoản Mới</h2>
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

        <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" id="createUserForm">
            @csrf
            
            <div class="flex flex-col lg:flex-row gap-6 items-stretch">
                <!-- Left Column: Avatar Dropzone -->
                <div class="w-full lg:w-1/3 flex flex-col">
                    <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Ảnh đại diện (Avatar)</label>
                    
                    <div class="relative group flex-1 flex flex-col min-h-[280px]">
                        <input type="file" name="avatar" id="avatar" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*" onchange="previewAvatar(event)">
                        <div id="avatar-preview-container" class="flex-1 flex flex-col items-center justify-center w-full border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-blue-400 transition-all duration-300 dark:bg-gray-800 dark:border-gray-600 dark:hover:bg-gray-700">
                            <i class="bi bi-cloud-arrow-up text-5xl text-blue-500 mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                            <p class="text-sm font-medium text-gray-600 text-center px-3 mb-3 dark:text-gray-400">Kéo thả file vào đây<br>hoặc click để chọn</p>
                            <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 pointer-events-none flex items-center">
                                <i class="bi bi-image mr-2"></i> Duyệt ảnh
                            </button>
                        </div>
                        <div id="avatar-image-container" class="hidden flex-1 w-full relative rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                            <img id="avatar-image" src="" alt="Avatar Preview" class="w-full h-full object-cover absolute inset-0">
                            <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="text-white font-medium bg-black bg-opacity-60 px-3 py-1.5 rounded-lg text-sm flex items-center"><i class="bi bi-pencil mr-2"></i> Đổi ảnh</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Form Fields Box -->
                <div class="w-full lg:w-2/3">
                    <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Thông tin tài khoản</label>
                    <div class="flex flex-col gap-4 bg-gray-50 border border-gray-200 p-5 sm:p-6 rounded-xl dark:bg-gray-800/60 dark:border-gray-700 h-full justify-center">
                        <div>
                            <label for="name" class="block mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300">Họ tên <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white transition-shadow hover:shadow-md" required placeholder="Nhập họ và tên...">
                        </div>

                        <div>
                            <label for="email" class="block mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" class="bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white transition-shadow hover:shadow-md" required placeholder="example@email.com">
                        </div>

                        <div>
                            <label for="role" class="block mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300">Vai trò <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select name="role" id="role" class="appearance-none bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 pr-10 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white transition-shadow hover:shadow-md cursor-pointer" required>
                                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User (Khách hàng/Người dùng)</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin (Quản trị viên)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <i class="bi bi-chevron-down"></i>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300">Mật khẩu <span class="text-red-500">*</span></label>
                                <input type="password" name="password" id="password" class="bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white transition-shadow hover:shadow-md" required minlength="8" placeholder="••••••••">
                            </div>
                            <div>
                                <label for="password_confirmation" class="block mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300">Nhập lại Mật khẩu <span class="text-red-500">*</span></label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white transition-shadow hover:shadow-md" required minlength="8" placeholder="••••••••">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Fixed Footer -->
    <div class="p-5 sm:px-8 shrink-0 bg-gray-50 border-t border-gray-100 dark:bg-gray-800/50 dark:border-gray-800 flex justify-end gap-3 rounded-b-2xl">
        <a href="{{ route('admin.users.index') }}" class="text-gray-700 bg-white hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-100 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 transition-colors shadow-sm inline-flex items-center justify-center">Hủy</a>
        <button type="submit" form="createUserForm" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-6 py-2.5 text-center shadow-sm transition-all transform hover:-translate-y-0.5 inline-flex items-center justify-center">Tạo tài khoản</button>
    </div>
</div>

<script>
    function previewAvatar(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatar-preview-container').classList.add('hidden');
                document.getElementById('avatar-image-container').classList.remove('hidden');
                document.getElementById('avatar-image').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
