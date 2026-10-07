@extends('admin.layouts.admin')

@section('title', 'Thêm Banner Mới')
@section('page_title', 'Thêm Banner Mới')

@section('content')
<div class="max-w-4xl bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6 mb-6">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.banners.index') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-700 transition-colors">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="text-xl font-bold text-slate-800 dark:text-white">Thêm Banner</h2>
    </div>

    @if ($errors->any())
        <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-slate-800 dark:text-red-400 border border-red-200 dark:border-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Left Column: Info -->
            <div class="space-y-6">
                <div>
                    <label for="title" class="block mb-2 text-sm font-medium text-slate-900 dark:text-white">Tiêu đề (Tùy chọn)</label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-slate-700 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Nhập tên chiến dịch/sự kiện...">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="type" class="block mb-2 text-sm font-medium text-slate-900 dark:text-white">Loại Banner</label>
                        <select id="type" name="type" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-slate-700 dark:border-slate-600 dark:text-white" onchange="toggleCategorySelect()">
                            <option value="home_slider" {{ old('type') == 'home_slider' ? 'selected' : '' }}>Home Slider</option>
                            <option value="category_header" {{ old('type') == 'category_header' ? 'selected' : '' }}>Banner Danh Mục</option>
                        </select>
                    </div>

                    <div id="category_select_wrapper" style="display: {{ old('type') == 'category_header' ? 'block' : 'none' }};">
                        <label for="category_id" class="block mb-2 text-sm font-medium text-slate-900 dark:text-white">Danh Mục Áp Dụng</label>
                        <select id="category_id" name="category_id" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                            <option value="">Tất cả danh mục (Banner chung)</option>
                            @foreach($categoryTree as $cat)
                                <option value="{{ $cat['id'] }}" {{ old('category_id') == $cat['id'] ? 'selected' : '' }}>{{ $cat['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="link" class="block mb-2 text-sm font-medium text-slate-900 dark:text-white">Đường dẫn khi click (Tùy chọn)</label>
                    <input type="text" id="link" name="link" value="{{ old('link') }}" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-slate-700 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="https://...">
                </div>

                <div class="flex gap-4">
                    <div class="flex-1">
                        <label for="position" class="block mb-2 text-sm font-medium text-slate-900 dark:text-white">Thứ tự hiển thị</label>
                        <input type="number" id="position" name="position" value="{{ old('position', 0) }}" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-slate-700 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                    </div>
                    
                    <div class="flex-1 flex flex-col justify-end pb-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', true) ? 'checked' : '' }}>
                            <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-primary-600"></div>
                            <span class="ms-3 text-sm font-medium text-slate-900 dark:text-slate-300">Trạng thái (Bật/Tắt)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Right Column: Image Upload -->
            <div>
                <label class="block mb-2 text-sm font-medium text-slate-900 dark:text-white">Hình ảnh Banner <span class="text-red-500">*</span></label>
                
                <div class="flex items-center justify-center w-full" id="upload-container">
                    <label for="image" class="flex flex-col items-center justify-center w-full h-64 border-2 border-slate-300 border-dashed rounded-lg cursor-pointer bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:hover:border-slate-500 dark:hover:bg-slate-600 transition-colors">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6 text-slate-500 dark:text-slate-400 text-center px-4" id="upload-text">
                            <i class="bi bi-cloud-arrow-up text-4xl mb-3"></i>
                            <p class="mb-2 text-sm font-semibold">Click để upload ảnh</p>
                            <p class="text-xs">Chấp nhận JPG, PNG, WEBP (Max: 5MB)</p>
                            <p class="text-xs mt-1 text-slate-400">Tỷ lệ khuyên dùng: 16:9 hoặc ảnh ngang (Ví dụ: 1920x600)</p>
                        </div>
                        <input id="image" name="image" type="file" class="hidden" accept="image/*" required />
                    </label>
                </div>
                <!-- Preview Image -->
                <div id="preview-container" class="hidden relative mt-2 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 shadow-sm h-48 group">
                    <img id="image-preview" src="#" alt="Preview" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <button type="button" id="remove-image" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center">
                            <i class="bi bi-trash mr-2"></i> Xóa ảnh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t border-slate-200 dark:border-slate-700 pt-6">
            <a href="{{ route('admin.banners.index') }}" class="text-slate-700 bg-white border border-slate-300 focus:ring-4 focus:outline-none focus:ring-slate-100 font-medium rounded-lg text-sm px-5 py-2.5 text-center hover:bg-slate-50 dark:bg-slate-800 dark:text-white dark:border-slate-600 dark:hover:bg-slate-700 dark:hover:border-slate-500 dark:focus:ring-slate-700 transition-colors">
                Hủy bỏ
            </a>
            <button type="submit" class="text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 transition-colors shadow-sm">
                Lưu Banner
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const imageInput = document.getElementById('image');
    const uploadContainer = document.getElementById('upload-container');
    const previewContainer = document.getElementById('preview-container');
    const imagePreview = document.getElementById('image-preview');
    const removeBtn = document.getElementById('remove-image');

    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                uploadContainer.classList.add('hidden');
                previewContainer.classList.remove('hidden');
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    removeBtn.addEventListener('click', function() {
        imageInput.value = '';
        uploadContainer.classList.remove('hidden');
        previewContainer.classList.add('hidden');
        imagePreview.src = '#';
    });

    function toggleCategorySelect() {
        const typeStr = document.getElementById('type').value;
        const wrapper = document.getElementById('category_select_wrapper');
        if (typeStr === 'category_header') {
            wrapper.style.display = 'block';
        } else {
            wrapper.style.display = 'none';
            document.getElementById('category_id').value = '';
        }
    }
    
    // Run on load to set correct initial state
    document.addEventListener('DOMContentLoaded', function() {
        toggleCategorySelect();
    });
</script>
@endpush
