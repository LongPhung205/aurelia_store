@extends('admin.layouts.admin')

@section('title', 'Thêm Bộ Sưu Tập Mới')

@section('content')
<div class="w-full h-[calc(100vh-90px)] flex flex-col">
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6">
        <div class="max-w-5xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Thêm Bộ Sưu Tập Mới</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tạo nhóm các sản phẩm chung chủ đề để nổi bật trên trang chủ</p>
                </div>
                <x-admin.button href="{{ route('admin.collections.index') }}" variant="secondary" icon="bi bi-arrow-left">
                    Quay lại
                </x-admin.button>
            </div>

            @if ($errors->any())
                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                    <span class="font-medium">Có lỗi xảy ra:</span>
                    <ul class="mt-1.5 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.collections.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Main Content Col -->
                    <div class="lg:col-span-2 space-y-6">
                        <x-admin.card>
                            <x-admin.input name="name" label="Tên bộ sưu tập" placeholder="VD: Váy cưới thiết kế, Bộ sưu tập Hè 2026..." required="true" value="{{ old('name') }}" />
                            
                            <div class="mt-4">
                                <x-admin.textarea name="description" label="Mô tả ngắn" placeholder="Nhập mô tả vài dòng...">{{ old('description') }}</x-admin.textarea>
                            </div>
                        </x-admin.card>

                        <x-admin.card title="Sản phẩm trong Bộ Sưu Tập" icon="bi bi-box-seam">
                            <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Gắn sản phẩm</label>
                            <select class="select2-products w-full" name="products[]" multiple="multiple" data-placeholder="Tìm và chọn sản phẩm...">
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ in_array($product->id, old('products', [])) ? 'selected' : '' }}>
                                        {{ $product->name }} ({{ number_format($product->price, 0, ',', '.') }}đ)
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-xs text-gray-500">Bạn có thể chọn nhiều sản phẩm cùng lúc bằng cách gõ tên.</p>
                        </x-admin.card>
                    </div>

                    <!-- Sidebar Col -->
                    <div class="lg:col-span-1 space-y-6">
                        <x-admin.card title="Cấu hình hiển thị" icon="bi bi-gear">
                            
                            <div class="mb-4">
                                <x-admin.input name="position" type="number" label="Vị trí hiển thị (Position)" value="{{ old('position', 0) }}" />
                                <p class="text-xs text-gray-500 mt-1">Số càng nhỏ ưu tiên hiển thị lên trước.</p>
                            </div>

                            <div class="mb-4">
                                <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Trạng thái (Is Active)</label>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">Hiển thị ra trang chủ</span>
                                </label>
                            </div>

                        </x-admin.card>

                        <x-admin.card title="Ảnh đại diện" icon="bi bi-image">
                            <div class="flex items-center justify-center w-full mb-3" id="upload-wrapper">
                                <label for="image" class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <i class="bi bi-cloud-arrow-up text-3xl text-gray-400 mb-3"></i>
                                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click tải lên</span> hoặc kéo thả</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, WEBP (Max 5MB)</p>
                                    </div>
                                    <input id="image" name="image" type="file" class="hidden" accept="image/*" onchange="previewImage(this)" />
                                </label>
                            </div>
                            
                            <div class="mt-3 text-center hidden relative group" id="imagePreviewContainer">
                                <img id="imagePreview" src="#" alt="Preview" class="rounded-lg border border-gray-200 shadow-sm mx-auto" style="max-height: 200px;">
                                <button type="button" onclick="removePreview()" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity" title="Xóa ảnh">
                                    <i class="bi bi-x-circle text-lg"></i>
                                </button>
                            </div>
                        </x-admin.card>

                        <div class="pt-4 flex gap-3">
                            <x-admin.button type="submit" variant="primary" icon="bi bi-save" class="w-full">
                                Lưu Bộ Sưu Tập
                            </x-admin.button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--multiple {
        min-height: 42px;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
    }
    .dark .select2-container .select2-selection--multiple {
        background-color: #374151;
        border-color: #4b5563;
    }
    .dark .select2-container .select2-selection--multiple .select2-selection__choice {
        background-color: #1f2937;
        border-color: #4b5563;
        color: #f3f4f6;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #ef4444;
    }
    .dark .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #4b5563;
    }
    .dark .select2-dropdown {
        background-color: #374151;
        border-color: #4b5563;
        color: #f3f4f6;
    }
</style>
@endpush

@push('scripts')
<!-- Select2 JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select2-products').select2({
            placeholder: "Gõ tên sản phẩm để tìm kiếm...",
            allowClear: true,
            width: '100%'
        });
    });

    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
                document.getElementById('imagePreviewContainer').classList.remove('hidden');
                document.getElementById('upload-wrapper').classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removePreview() {
        document.getElementById('image').value = '';
        document.getElementById('imagePreviewContainer').classList.add('hidden');
        document.getElementById('upload-wrapper').classList.remove('hidden');
    }
</script>
@endpush
