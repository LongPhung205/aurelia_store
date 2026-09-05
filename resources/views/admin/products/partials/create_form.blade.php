            <!-- Hiển thị lỗi chung (nếu có) -->
            @if ($errors->any())
                <div class="alert alert-danger shadow-sm border-0 mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Tabs Nav -->
            <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="createProductTabs" data-tabs-toggle="#createProductTabsContent" role="tablist">
                    <li class="me-2" role="presentation">
                        <button class="inline-block p-4 border-b-2 rounded-t-lg aria-selected:border-blue-600 aria-selected:text-blue-600 dark:aria-selected:text-blue-500 dark:aria-selected:border-blue-500 hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="basic-info-tab" data-tabs-target="#basic-info-pane" type="button" role="tab" aria-controls="basic-info-pane" aria-selected="true">
                            <i class="bi bi-info-circle mr-2"></i>Thông tin cơ bản
                        </button>
                    </li>
                    <li class="me-2" role="presentation">
                        <button class="inline-block p-4 border-b-2 rounded-t-lg border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="images-variants-tab" data-tabs-target="#images-variants-pane" type="button" role="tab" aria-controls="images-variants-pane" aria-selected="false">
                            <i class="bi bi-images mr-2"></i>Hình ảnh & Biến thể
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tabs Content -->
            <div id="createProductTabsContent" class="overflow-y-auto max-h-[calc(100vh-320px)] pr-2 pb-4">
                
                <!-- TAB 1: THÔNG TIN CƠ BẢN -->
                <div class="hidden" id="basic-info-pane" role="tabpanel" aria-labelledby="basic-info-tab">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <!-- Cột Trái -->
                        <div class="lg:col-span-2">
                            <x-admin.card class="mb-4">
                                <x-admin.input name="name" label="Tên váy / Sản phẩm" placeholder="VD: Váy liền cổ chữ V đính ngọc" required="true" />
                                
                                <x-admin.textarea name="description" label="Bài viết chi tiết" placeholder="Viết nội dung giới thiệu chi tiết về form dáng, cách phối đồ, dịp mặc phù hợp...">
                                </x-admin.textarea>

                                <div class="mb-4">
                                    <x-admin.select id="material-select" label="Chất liệu vải">
                                        <option value="">-- Chọn chất liệu --</option>
                                        @foreach($materials as $mat)
                                            <option value="{{ $mat->name }}" {{ old('material') == $mat->name ? 'selected' : '' }}>{{ $mat->name }}</option>
                                        @endforeach
                                        <option value="_other_">Khác (Tự nhập...)</option>
                                    </x-admin.select>
                                    
                                    <input type="text" class="hidden bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mt-2" id="material-input" placeholder="Nhập tên chất liệu mới...">
                                    
                                    <input type="hidden" name="material" id="material-hidden" value="{{ old('material') }}">
                                </div>
                                
                                <div class="p-4 mt-2 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
                                    <i class="bi bi-lightbulb mr-2"></i> Nhập xong thông tin cơ bản, hãy chuyển sang Tab <b>Hình ảnh & Biến thể</b> để tiếp tục.
                                </div>
                            </x-admin.card>
                        </div>

                        <!-- Cột Phải -->
                        <div class="lg:col-span-1">
                            <x-admin.card class="mb-4 bg-gray-50 dark:bg-gray-800">
                                <div class="mb-4">
                                    <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Danh mục <span class="text-red-500">*</span></label>
                                    <div class="p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg max-h-60 overflow-y-auto">
                                        @include('admin.products.partials.category_checkbox', [
                                            'categories' => $categories,
                                            'level' => 0,
                                            'selected' => old('category_ids', [])
                                        ])
                                    </div>
                                    @error('category_ids')
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <x-admin.select name="status" label="Trạng thái" required="true">
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Công khai (Active)</option>
                                    <option value="hidden" {{ old('status') == 'hidden' ? 'selected' : '' }}>Ẩn (Hidden)</option>
                                </x-admin.select>
                            </x-admin.card>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: HÌNH ẢNH & BIẾN THỂ -->
                <div class="hidden" id="images-variants-pane" role="tabpanel" aria-labelledby="images-variants-tab">
                    
                    <x-admin.card class="mb-4" title="Biến thể sản phẩm (Tùy chọn)" icon="bi bi-layers">
                        <x-slot name="headerActions">
                            <x-admin.button variant="outline-primary" size="sm" type="button" id="btn-generate-variants" icon="bi bi-magic">
                                Tạo biến thể
                            </x-admin.button>
                        </x-slot>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-sm font-semibold text-gray-900 dark:text-white">Chọn Màu sắc</label>
                                    <button type="button" class="text-blue-600 hover:underline text-sm" id="btn-select-all-colors">Chọn tất cả</button>
                                </div>
                                <div class="flex flex-wrap gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600" id="color-selection">
                                    @foreach($colors as $color)
                                        <div class="flex items-center">
                                            <input class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 color-checkbox" type="checkbox" value="{{ $color->id }}" id="color_{{ $color->id }}" data-name="{{ $color->name }}">
                                            <label class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center" for="color_{{ $color->id }}">
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
                                    <button type="button" class="text-blue-600 hover:underline text-sm" id="btn-select-all-sizes">Chọn tất cả</button>
                                </div>
                                <div class="flex flex-wrap gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600" id="size-selection">
                                    @foreach($sizes as $size)
                                        <div class="flex items-center">
                                            <input class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 size-checkbox" type="checkbox" value="{{ $size->id }}" id="size_{{ $size->id }}" data-name="{{ $size->name }}">
                                            <label class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300" for="size_{{ $size->id }}">{{ $size->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                                    

                        <!-- Khu vực tải ảnh theo màu -->
                        <div id="color-images-section" class="mb-6 hidden">
                            <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Hình ảnh theo màu sắc (Tùy chọn)</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4" id="color-images-container">
                                <!-- JS sẽ render các ô upload ảnh tại đây -->
                            </div>
                        </div>
                        
                        <!-- Khu vực Thiết lập chung (Bulk Edit) -->
                        <div id="bulk-setup-section" class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 mb-6 hidden">
                            <label class="block mb-3 text-sm font-semibold text-blue-700 dark:text-blue-400">Thiết lập giá chung cho tất cả phân loại</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                                <div class="md:col-span-2">
                                    <input type="number" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" id="bulk-price" min="0" placeholder="Ví dụ: 500000 VNĐ">
                                </div>
                                <div class="md:col-span-1">
                                    <x-admin.button type="button" id="btn-apply-bulk" variant="primary" class="w-full">Áp dụng cho tất cả</x-admin.button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 hidden" id="variants-table">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Màu sắc</th>
                                        <th scope="col" class="px-6 py-3">Kích cỡ</th>
                                        <th scope="col" class="px-6 py-3">Giá bán <span class="text-red-500">*</span></th>
                                        <th scope="col" class="px-6 py-3">Mã SKU (Tự động)</th>
                                        <th scope="col" class="px-6 py-3 text-center">
                                            <button type="button" class="text-red-600 hover:text-red-800" id="btn-clear-all-variants" title="Xóa tất cả biến thể">
                                                <i class="bi bi-trash text-lg"></i>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="variants-tbody">
                                    <!-- Dữ liệu JS sinh ra ở đây -->
                                </tbody>
                            </table>
                        </div>
                        <div id="no-variant-msg" class="text-gray-500 italic text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            Vui lòng chọn ít nhất một màu sắc và một kích cỡ, sau đó bấm "Tạo biến thể". Nếu không tạo biến thể, sản phẩm sẽ được lưu dưới dạng sản phẩm đơn.
                        </div>
                    </x-admin.card>
                </div>

            </div>

