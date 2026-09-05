@extends('admin.layouts.admin')

@section('title', 'Cài đặt Chung')
@section('page_title', 'Cài đặt Chung')

@section('content')
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6 mb-6">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-800 dark:text-white">Cấu hình Giao diện</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">Quản lý các hình ảnh và thông số giao diện chung của website.</p>
    </div>

    @if(session('success'))
        <div class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-slate-800 dark:text-green-400 border border-green-200 dark:border-green-800">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-6">
            <label class="block mb-2 text-sm font-medium text-slate-900 dark:text-white" for="product_frame">Khung Ảnh Sản Phẩm</label>
            <p class="text-xs text-slate-500 mb-3">Tải lên một khung viền trang trí (định dạng PNG trong suốt ở giữa) để bọc các sản phẩm trên trang chủ và trang danh mục.</p>
            
            <div class="flex items-start gap-6">
                <!-- Preview -->
                <div class="shrink-0 w-48 h-auto aspect-[3/4] border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg flex items-center justify-center overflow-hidden bg-slate-50 dark:bg-slate-700/50">
                    <img id="frame_preview" src="{{ str_starts_with($productFrame, 'storage/') ? asset($productFrame) : asset($productFrame) }}" alt="Preview" class="w-full h-full object-contain">
                </div>

                <!-- Input -->
                <div class="flex-1">
                    <input class="block w-full text-sm text-slate-900 border border-slate-300 rounded-lg cursor-pointer bg-slate-50 dark:text-slate-400 focus:outline-none dark:bg-slate-700 dark:border-slate-600 dark:placeholder-slate-400" 
                           id="product_frame" name="product_frame" type="file" accept="image/*" onchange="previewImage(this)">
                    @error('product_frame')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-primary-500 dark:hover:bg-primary-600 dark:focus:ring-primary-800 transition-colors">
                Lưu Cài Đặt
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('frame_preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
@endsection
