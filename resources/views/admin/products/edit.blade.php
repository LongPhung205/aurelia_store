@extends('admin.layouts.admin')

@section('title', 'Sửa Sản Phẩm: ' . $product->name)

@section('content')
<div class="px-0">


    @if ($errors->any())
        <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <i class="bi bi-exclamation-triangle-fill text-xl mr-3"></i>
            <div>
                <span class="font-medium">Vui lòng kiểm tra lại thông tin:</span>
                <ul class="mt-1.5 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Main Card -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700 overflow-hidden mb-8">
        <!-- Tabs Nav -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="productTabs" data-tabs-toggle="#productTabsContent" role="tablist">
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg aria-selected:border-blue-600 aria-selected:text-blue-600 dark:aria-selected:text-blue-500 dark:aria-selected:border-blue-500 hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="info-tab" data-tabs-target="#info" type="button" role="tab" aria-selected="true">
                        <i class="bi bi-info-circle mr-2"></i>Thông tin chung
                    </button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="variants-tab" data-tabs-target="#variants" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-layers mr-2"></i>Biến thể ({{ $product->variants->count() }})
                    </button>
                </li>
            </ul>
        </div>

        <!-- Tabs Content -->
        <div>
            <div id="productTabsContent">
                
                <!-- TAB 1: THÔNG TIN CHUNG -->
                <div class="hidden p-6" id="info" role="tabpanel" aria-labelledby="info-tab">
                    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="lg:col-span-2 space-y-6">
                                <x-admin.input name="name" label="Tên sản phẩm" value="{{ old('name', $product->name) }}" required="true" />

                                <x-admin.textarea name="short_description" label="Mô tả ngắn" rows="3">{{ old('short_description', $product->short_description) }}</x-admin.textarea>

                                <x-admin.textarea name="description" label="Bài viết chi tiết" rows="6">{{ old('description', $product->description) }}</x-admin.textarea>

                                <div class="flex justify-end mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <a href="{{ route('admin.products.index') }}" class="mr-2 inline-flex">
                                        <x-admin.button type="button" variant="secondary">Hủy</x-admin.button>
                                    </a>
                                    <x-admin.button type="submit" variant="primary" icon="bi bi-save">Lưu thay đổi</x-admin.button>
                                </div>
                            </div>
                            
                            <div class="lg:col-span-1">
                                <x-admin.card class="mb-4 bg-gray-50 dark:bg-gray-800" title="Phân loại">
                                    <div class="mb-4">
                                        <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Danh mục <span class="text-red-500">*</span></label>
                                        <div class="p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg max-h-60 overflow-y-auto">
                                            @include('admin.products.partials.category_checkbox', [
                                                'categories' => $categories,
                                                'parentId' => null,
                                                'level' => 0,
                                                'selected' => old('category_ids', $product->categories->pluck('id')->toArray())
                                            ])
                                        </div>
                                        @error('category_ids')
                                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <x-admin.input name="material" label="Chất liệu vải" value="{{ old('material', $product->material) }}" />


                                    <x-admin.select name="status" label="Trạng thái" required="true">
                                        <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Công khai</option>
                                        <option value="draft" {{ old('status', $product->status) == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                                        <option value="hidden" {{ old('status', $product->status) == 'hidden' ? 'selected' : '' }}>Đã ẩn</option>
                                    </x-admin.select>
                                </x-admin.card>
                            </div>
                        </div>

                    </form>
                </div>

                <!-- TAB 2: BIẾN THỂ VÀ HÌNH ẢNH THEO MÀU -->
                <div class="hidden p-6" id="variants" role="tabpanel" aria-labelledby="variants-tab">
                    
                    <!-- Phần Cập nhật hình ảnh theo màu sắc -->
                    <x-admin.card class="mb-8 bg-gray-50 dark:bg-gray-800">
                        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="name" value="{{ $product->name }}">
                            <input type="hidden" name="status" value="{{ $product->status }}">
                            
                            <!-- Hidden inputs to pass validation for UpdateProductRequest -->
                            @foreach($product->categories as $category)
                                <input type="hidden" name="category_ids[]" value="{{ $category->id }}">
                            @endforeach
                            
                            <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                                <h6 class="text-lg font-bold text-gray-900 dark:text-white mb-0"><i class="bi bi-images mr-2"></i>Cập nhật hình ảnh theo màu sắc</h6>
                                <x-admin.button type="submit" variant="primary" size="sm" icon="bi bi-save">
                                    Lưu hình ảnh chung
                                </x-admin.button>
                            </div>

                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                    @php
                                        // Lấy tất cả màu sắc đã được chọn trong các biến thể của sản phẩm này
                                        $colorVariants = $product->variants->unique('color_id');
                                    @endphp
                                    
                                    @forelse($colorVariants as $variant)
                                        @if($variant->color)
                                        <div class="text-center">
                                            <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $variant->color->name }}</label>
                                            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-2 dark:bg-gray-800 dark:border-gray-700">
                                                @if($variant->thumbnail_url)
                                                    <img src="{{ asset('storage/' . $variant->thumbnail_url) }}" class="w-full h-32 object-cover color-image-preview" id="preview-color-{{ $variant->color_id }}">
                                                @else
                                                    <div class="flex items-center justify-center w-full h-32 bg-gray-100 dark:bg-gray-700 color-image-preview-placeholder" id="preview-placeholder-{{ $variant->color_id }}">
                                                        <i class="bi bi-image text-gray-400 text-3xl"></i>
                                                    </div>
                                                    <img src="" class="hidden w-full h-32 object-cover color-image-preview" id="preview-color-{{ $variant->color_id }}">
                                                @endif
                                            </div>
                                            <input type="file" name="color_images[{{ $variant->color_id }}]" class="block w-full text-xs text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none color-image-input" data-color-id="{{ $variant->color_id }}" accept="image/*">
                                        </div>
                                        @endif
                                    @empty
                                        <div class="col-span-full text-center text-gray-500 py-6">
                                            <i class="bi bi-images text-4xl mb-2 text-gray-400"></i>
                                            <p class="mb-0 text-sm">Chưa có biến thể nào có màu sắc.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </form>
                    </x-admin.card>

                    <!-- Phần Quản lý kho & Biến thể -->
                    <div class="flex justify-between items-center mb-6">
                        <h6 class="text-lg font-bold text-gray-900 dark:text-white mb-0">Quản lý kho & Biến thể</h6>
                        <x-admin.button type="button" variant="success" size="sm" icon="bi bi-plus-lg" data-modal-target="createVariantModal" data-modal-toggle="createVariantModal">
                            Thêm biến thể mới
                        </x-admin.button>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm dark:border-gray-700">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Hình ảnh</th>
                                    <th scope="col" class="px-6 py-3">Thuộc tính</th>
                                    <th scope="col" class="px-6 py-3">Mã SKU</th>
                                    <th scope="col" class="px-6 py-3">Giá bán</th>
                                    <th scope="col" class="px-6 py-3 text-center">Tồn kho</th>
                                    <th scope="col" class="px-6 py-3 text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($product->variants as $variant)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="px-6 py-4">
                                            @if($variant->thumbnail_url)
                                                <img src="{{ asset('storage/' . $variant->thumbnail_url) }}" class="w-12 h-12 rounded object-cover shadow-sm border border-gray-200 dark:border-gray-600">
                                            @else
                                                <div class="w-12 h-12 bg-gray-100 rounded border border-gray-200 flex items-center justify-center text-gray-400 shadow-sm dark:bg-gray-700 dark:border-gray-600">
                                                    <i class="bi bi-image text-xl"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900 dark:text-white">{{ $variant->color ? $variant->color->name : 'N/A' }}</div>
                                            <div class="text-xs text-gray-500">Size: {{ $variant->size ? $variant->size->name : 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $variant->sku }}</td>
                                        <td class="px-6 py-4 font-semibold text-red-600">{{ number_format($variant->price) }} ₫</td>
                                        <td class="px-6 py-4 text-center">
                                            <x-admin.badge variant="{{ $variant->stock_quantity > 0 ? 'success' : 'danger' }}" rounded="true">
                                                {{ $variant->stock_quantity }}
                                            </x-admin.badge>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <a href="javascript:void(0)" class="text-blue-600 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 hover:bg-gray-100 dark:bg-gray-800 dark:text-blue-500 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700" data-modal-target="editVariantModal{{ $variant->id }}" data-modal-toggle="editVariantModal{{ $variant->id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('admin.products.variants.destroy', [$product->id, $variant->id]) }}" method="POST" class="inline-block form-delete" data-confirm-title="Xóa biến thể?" data-confirm-text="Bạn có chắc chắn muốn xóa biến thể này không?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 hover:bg-gray-100 dark:bg-gray-800 dark:text-red-500 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700 btn-delete-item">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            <div class="mb-2"><i class="bi bi-inbox text-4xl text-gray-400"></i></div>
                                            <p class="mb-0">Chưa có biến thể nào được tạo.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Edit Variant Modals (Rendered for each variant) -->
                    @foreach($product->variants as $variant)
                        <x-admin.modal id="editVariantModal{{ $variant->id }}" title="Sửa Biến Thể">
                            <form action="{{ route('admin.products.variants.update', [$product->id, $variant->id]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <x-admin.select name="color_id" label="Màu sắc" required="true">
                                        <option value="">-- Chọn màu --</option>
                                        @foreach($colors as $color) 
                                            <option value="{{ $color->id }}" {{ $variant->color_id == $color->id ? 'selected' : '' }}>{{ $color->name }}</option> 
                                        @endforeach
                                    </x-admin.select>
                                    
                                    <x-admin.select name="size_id" label="Kích cỡ" required="true">
                                        <option value="">-- Chọn kích cỡ --</option>
                                        @foreach($sizes as $size) 
                                            <option value="{{ $size->id }}" {{ $variant->size_id == $size->id ? 'selected' : '' }}>{{ $size->name }}</option> 
                                        @endforeach
                                    </x-admin.select>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <x-admin.input type="number" name="price" label="Giá bán (VNĐ)" value="{{ $variant->price }}" required="true" min="0" />
                                    <x-admin.input type="number" name="stock_quantity" label="Tồn kho" value="{{ $variant->stock_quantity }}" required="true" min="0" />
                                </div>

                                <div class="mb-4">
                                    <x-admin.input name="sku" label="Mã SKU" value="{{ $variant->sku }}" />
                                    <p class="mt-1 text-sm text-yellow-600 dark:text-yellow-500"><i class="bi bi-exclamation-triangle mr-1"></i> Sửa mã SKU cẩn thận để không trùng lặp. Xóa trắng để tự động tạo lại.</p>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Ảnh đại diện cho Biến thể này (Tùy chọn)</label>
                                    @if($variant->thumbnail_url)
                                        <div class="mb-3">
                                            <img src="{{ asset('storage/' . $variant->thumbnail_url) }}" alt="Thumbnail" class="h-16 w-16 object-cover rounded shadow-sm border border-gray-200 dark:border-gray-600">
                                        </div>
                                    @endif
                                    <input type="file" name="thumbnail" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" accept="image/*">
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400"><i class="bi bi-info-circle mr-1"></i>Tải lên ảnh mới nếu bạn muốn thay đổi ảnh hiện tại.</p>
                                </div>
                            </form>
                        </x-admin.modal>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</div>


<!-- Modal Create Variant -->
<x-admin.modal id="createVariantModal" title="Thêm Biến Thể Mới">
    <form action="{{ route('admin.products.variants.store', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <x-admin.select name="color_id" label="Màu sắc" required="true">
                <option value="">-- Chọn màu --</option>
                @foreach($colors as $color) <option value="{{ $color->id }}">{{ $color->name }}</option> @endforeach
            </x-admin.select>
            
            <x-admin.select name="size_id" label="Kích cỡ" required="true">
                <option value="">-- Chọn kích cỡ --</option>
                @foreach($sizes as $size) <option value="{{ $size->id }}">{{ $size->name }}</option> @endforeach
            </x-admin.select>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <x-admin.input type="number" name="price" label="Giá bán (VNĐ)" required="true" min="0" placeholder="VD: 550000" />
            <x-admin.input type="number" name="stock_quantity" label="Tồn kho" value="0" required="true" min="0" />
        </div>

        <div class="mb-4">
            <x-admin.input name="sku" label="Mã SKU (Tùy chọn)" placeholder="Để trống để hệ thống tự tạo mã..." />
        </div>
        
        <div class="mb-4">
            <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Ảnh đại diện cho Biến thể này</label>
            <input type="file" name="thumbnail" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" accept="image/*">
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400"><i class="bi bi-info-circle mr-1"></i>Chỉ nên dùng nếu biến thể này có màu sắc hoặc hình dáng khác rõ rệt.</p>
        </div>
    </form>
</x-admin.modal>

<style>
/* Custom styles for Edit Page */
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image Preview for Color Images
    const colorImageInputs = document.querySelectorAll('.color-image-input');
    colorImageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const colorId = this.dataset.colorId;
                const previewImg = document.getElementById('preview-color-' + colorId);
                const placeholder = document.getElementById('preview-placeholder-' + colorId);
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (previewImg) {
                        previewImg.src = e.target.result;
                        previewImg.classList.remove('hidden');
                    }
                    if (placeholder) {
                        placeholder.classList.add('hidden');
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    });
});
</script>
@endsection

