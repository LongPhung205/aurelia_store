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

                                <div class="flex justify-end gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <button type="button" data-modal-hide="editVariantModal{{ $variant->id }}" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">Hủy</button>
                                    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Lưu Thay Đổi</button>
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
<x-admin.modal id="createVariantModal" title="Thêm Biến Thể Mới" size="xl">
    <form action="{{ route('admin.products.variants.store', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4 flex justify-end">
            <x-admin.button variant="outline-primary" size="sm" type="button" id="btn-generate-variants-modal" icon="bi bi-magic">
                Tạo tổ hợp biến thể
            </x-admin.button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white">Chọn Màu sắc</label>
                    <button type="button" class="text-blue-600 hover:underline text-sm" id="btn-select-all-colors-modal">Chọn tất cả</button>
                </div>
                <div class="flex flex-wrap gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600" id="color-selection-modal">
                    @foreach($colors as $color)
                        <div class="flex items-center">
                            <input class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 color-checkbox-modal" type="checkbox" value="{{ $color->id }}" id="modal_color_{{ $color->id }}" data-name="{{ $color->name }}">
                            <label class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center" for="modal_color_{{ $color->id }}">
                                @if($color->hex_code)
                                <span class="inline-block w-4 h-4 rounded-full mr-2 border border-gray-300" style="background-color:{{ $color->hex_code }};"></span>
                                @endif
                                {{ $color->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-semibold text-gray-900 dark:text-white">Chọn Kích cỡ (Size)</label>
                    <button type="button" class="text-blue-600 hover:underline text-sm" id="btn-select-all-sizes-modal">Chọn tất cả</button>
                </div>
                <div class="flex flex-wrap gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600" id="size-selection-modal">
                    @foreach($sizes as $size)
                        <div class="flex items-center">
                            <input class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 size-checkbox-modal" type="checkbox" value="{{ $size->id }}" id="modal_size_{{ $size->id }}" data-name="{{ $size->name }}">
                            <label class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300" for="modal_size_{{ $size->id }}">{{ $size->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Khu vực tải ảnh theo màu -->
        <div id="modal-color-images-section" class="mb-6 hidden">
            <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Hình ảnh theo màu sắc (Tùy chọn)</label>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4" id="modal-color-images-container">
                <!-- JS sẽ render các ô upload ảnh tại đây -->
            </div>
        </div>
        
        <div id="modal-bulk-setup-section" class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 mb-6 hidden">
            <label class="block mb-3 text-sm font-semibold text-blue-700 dark:text-blue-400">Thiết lập giá chung</label>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="md:col-span-2">
                    <input type="number" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" id="modal-bulk-price" min="0" placeholder="Giá (VD: 500000)">
                </div>
                <div class="md:col-span-1">
                    <x-admin.button type="button" id="btn-apply-bulk-modal" variant="primary" class="w-full">Áp dụng cho tất cả</x-admin.button>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto max-h-96">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 hidden" id="modal-variants-table">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600">
                    <tr>
                        <th scope="col" class="px-6 py-3">Màu sắc</th>
                        <th scope="col" class="px-6 py-3">Kích cỡ</th>
                        <th scope="col" class="px-6 py-3">Giá bán <span class="text-red-500">*</span></th>
                        <th scope="col" class="px-6 py-3">Mã SKU</th>
                        <th scope="col" class="px-6 py-3 text-center">
                            <button type="button" class="text-red-600 hover:text-red-800" id="btn-clear-all-variants-modal" title="Xóa tất cả biến thể">
                                <i class="bi bi-trash text-lg"></i>
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody id="modal-variants-tbody">
                    <!-- Dữ liệu JS sinh ra ở đây -->
                </tbody>
            </table>
        </div>
        <div id="modal-no-variant-msg" class="text-gray-500 italic text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 mb-4">
            Vui lòng chọn ít nhất một màu sắc hoặc một kích cỡ, sau đó bấm "Tạo tổ hợp biến thể".
        </div>

        <div class="flex justify-end gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" data-modal-hide="createVariantModal" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">Hủy</button>
            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Lưu Các Biến Thể</button>
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

    // JS for Modal Create Variants
    document.getElementById('btn-select-all-colors-modal')?.addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.color-checkbox-modal');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
    });

    document.getElementById('btn-select-all-sizes-modal')?.addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.size-checkbox-modal');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
    });

    document.getElementById('btn-generate-variants-modal')?.addEventListener('click', function() {
        const selectedColors = Array.from(document.querySelectorAll('.color-checkbox-modal:checked')).map(cb => ({ id: cb.value, name: cb.dataset.name }));
        const selectedSizes = Array.from(document.querySelectorAll('.size-checkbox-modal:checked')).map(cb => ({ id: cb.value, name: cb.dataset.name }));
        
        const tbody = document.getElementById('modal-variants-tbody');
        const table = document.getElementById('modal-variants-table');
        const msg = document.getElementById('modal-no-variant-msg');
        const colorImagesSection = document.getElementById('modal-color-images-section');
        const colorImagesContainer = document.getElementById('modal-color-images-container');
        const bulkSetupSection = document.getElementById('modal-bulk-setup-section');
        
        if (selectedColors.length === 0 && selectedSizes.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Thiếu thông tin',
                text: 'Vui lòng chọn ít nhất 1 màu sắc hoặc 1 kích cỡ.',
                confirmButtonText: 'Đã hiểu'
            });
            return;
        }

        tbody.innerHTML = '';
        colorImagesContainer.innerHTML = '';
        
        table.classList.remove('hidden');
        msg.classList.add('hidden');
        bulkSetupSection.classList.remove('hidden');
        
        if (selectedColors.length > 0) {
            colorImagesSection.classList.remove('hidden');
            selectedColors.forEach(color => {
                const col = document.createElement('div');
                col.className = 'md:col-span-1 flex flex-col justify-center items-center text-center relative';
                
                col.innerHTML = `
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm w-full p-2">
                        <label class="block text-xs font-semibold text-gray-900 truncate w-full mb-1">${color.name}</label>
                        <input type="file" name="color_images[${color.id}]" class="block w-full text-xs text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none modal-color-image-input" accept="image/*" data-color-id="${color.id}">
                        <div class="mt-2 text-gray-500 text-xs img-preview-area flex items-center justify-center bg-gray-50 rounded" id="modal-preview-color-${color.id}" style="min-height: 80px;">
                            <span>Chưa có ảnh</span>
                        </div>
                    </div>
                `;
                colorImagesContainer.appendChild(col);
            });

            document.querySelectorAll('.modal-color-image-input').forEach(input => {
                input.addEventListener('change', function() {
                    const colorId = this.dataset.colorId;
                    const previewArea = document.getElementById(`modal-preview-color-${colorId}`);
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewArea.innerHTML = `<img src="${e.target.result}" style="max-width:100%; max-height:80px; object-fit:cover; border-radius:4px;">`;
                        }
                        reader.readAsDataURL(this.files[0]);
                    } else {
                        previewArea.innerHTML = `<span>Chưa có ảnh</span>`;
                    }
                });
            });
        } else {
            colorImagesSection.classList.add('hidden');
        }

        let variants = [];
        if (selectedColors.length > 0 && selectedSizes.length > 0) {
            selectedColors.forEach(c => {
                selectedSizes.forEach(s => {
                    variants.push({ color: c, size: s });
                });
            });
        } else if (selectedColors.length > 0) {
            selectedColors.forEach(c => variants.push({ color: c, size: null }));
        } else {
            selectedSizes.forEach(s => variants.push({ color: null, size: s }));
        }

        variants.forEach((v, index) => {
            const tr = document.createElement('tr');
            
            const colorName = v.color ? v.color.name : '-';
            const sizeName = v.size ? v.size.name : '-';
            const arrayKey = (v.color ? v.color.id : '0') + '_' + (v.size ? v.size.id : '0');

            tr.className = 'bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600';
            tr.innerHTML = `
                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    ${colorName}
                    ${v.color ? `<input type="hidden" name="variants[${arrayKey}][color_id]" value="${v.color.id}">` : ''}
                </td>
                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    <span class="bg-gray-800 text-white text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">${sizeName}</span>
                    ${v.size ? `<input type="hidden" name="variants[${arrayKey}][size_id]" value="${v.size.id}">` : ''}
                </td>
                <td class="px-6 py-4">
                    <input type="number" name="variants[${arrayKey}][price]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white modal-variant-price" required min="0" placeholder="0">
                </td>
                <td class="px-6 py-4">
                    <input type="text" name="variants[${arrayKey}][sku]" class="bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600" placeholder="Tự tạo" readonly>
                </td>
                <td class="px-6 py-4 text-center">
                    <button type="button" class="text-red-600 hover:text-red-800 hover:bg-red-100 rounded p-1 modal-btn-remove-variant"><i class="bi bi-x-lg"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.querySelectorAll('.modal-btn-remove-variant').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('tr').remove();
                if (tbody.children.length === 0) {
                    table.classList.add('hidden');
                    bulkSetupSection.classList.add('hidden');
                    msg.classList.remove('hidden');
                    colorImagesSection.classList.add('hidden');
                }
            });
        });
    });

    document.getElementById('btn-clear-all-variants-modal')?.addEventListener('click', function() {
        document.getElementById('modal-variants-tbody').innerHTML = '';
        document.getElementById('modal-variants-table').classList.add('hidden');
        document.getElementById('modal-bulk-setup-section').classList.add('hidden');
        document.getElementById('modal-color-images-section').classList.add('hidden');
        document.getElementById('modal-no-variant-msg').classList.remove('hidden');
    });

    document.getElementById('btn-apply-bulk-modal')?.addEventListener('click', function() {
        const bulkPrice = document.getElementById('modal-bulk-price').value;

        if (bulkPrice) {
            document.querySelectorAll('.modal-variant-price').forEach(input => input.value = bulkPrice);
        }
    });
});
</script>
@endsection

