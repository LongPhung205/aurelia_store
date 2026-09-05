@extends('admin.layouts.admin')

@section('title', 'Sửa bài viết: ' . $post->title)

@section('content')
<div class="px-0">
    <div class="w-full">
        <!-- Header & Breadcrumb -->
        <div class="flex justify-between items-center mb-6 shrink-0">
            <div>
                <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Sửa bài viết / Lookbook</h4>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.posts.index') }}" class="text-blue-600 dark:text-blue-500 hover:underline font-medium text-sm">
                    <i class="bi bi-arrow-left mr-1"></i> Quay lại danh sách
                </a>
                <button type="submit" form="post-form" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 transition-colors shadow-sm">
                    <i class="bi bi-save mr-2"></i>Cập nhật bài viết
                </button>
            </div>
        </div>

        <form id="post-form" action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Cột chính -->
                <div class="lg:col-span-2 space-y-6">
                    <x-admin.card class="mb-4">
                        <x-admin.input name="title" label="Tiêu đề bài viết" value="{{ old('title', $post->title) }}" required="true" />
                        
                        <x-admin.textarea name="excerpt" label="Trích dẫn ngắn" rows="3">
                            {{ old('excerpt', $post->excerpt) }}
                        </x-admin.textarea>

                        <x-admin.textarea name="content" label="Nội dung chi tiết" rows="12" required="true">
                            {{ old('content', $post->content) }}
                        </x-admin.textarea>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Bạn có thể dán mã HTML vào để định dạng phong phú hơn.</p>
                    </x-admin.card>
                </div>

                <!-- Cột phụ -->
                <div class="lg:col-span-1 space-y-6">
                    <x-admin.card class="mb-4 bg-gray-50 dark:bg-gray-800">
                        <x-admin.select name="is_active" label="Trạng thái hiển thị" required="true">
                            <option value="1" {{ old('is_active', $post->is_active) ? 'selected' : '' }}>Công khai (Đăng ngay)</option>
                            <option value="0" {{ old('is_active', $post->is_active) == false ? 'selected' : '' }}>Bản nháp (Ẩn)</option>
                        </x-admin.select>
                    </x-admin.card>

                    <x-admin.card class="mb-4" title="Ảnh bìa (Lookbook)">
                        <div class="text-center">
                            <div class="w-full aspect-[3/4] bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden flex items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-600 relative group cursor-pointer" onclick="document.getElementById('image_upload').click()">
                                @if($post->image)
                                    <img id="image_preview" src="{{ Storage::url($post->image) }}" class="w-full h-full object-cover" />
                                    <div id="image_placeholder" class="hidden text-gray-400 dark:text-gray-500 group-hover:text-blue-500 transition-colors flex-col items-center p-4">
                                        <i class="bi bi-image text-4xl mb-2"></i>
                                        <span class="text-sm font-medium">Click để tải ảnh lên</span>
                                    </div>
                                @else
                                    <img id="image_preview" src="" class="w-full h-full object-cover hidden" />
                                    <div id="image_placeholder" class="text-gray-400 dark:text-gray-500 group-hover:text-blue-500 transition-colors flex flex-col items-center p-4">
                                        <i class="bi bi-image text-4xl mb-2"></i>
                                        <span class="text-sm font-medium">Click để tải ảnh lên</span>
                                        <span class="text-xs mt-1 text-gray-400">Khuyên dùng tỷ lệ 3:4 cho Lookbook</span>
                                    </div>
                                @endif
                                <!-- Lớp phủ đổi ảnh -->
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                    <span class="text-white font-medium"><i class="bi bi-camera"></i> Đổi ảnh</span>
                                </div>
                            </div>
                            <input type="file" name="image" id="image_upload" class="hidden" accept="image/*" onchange="previewImage(event)" />
                        </div>
                        @error('image') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </x-admin.card>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('image_preview');
            var placeholder = document.getElementById('image_placeholder');
            output.src = reader.result;
            output.classList.remove('hidden');
            if(placeholder) placeholder.classList.add('hidden');
        };
        if(event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>
@endpush
