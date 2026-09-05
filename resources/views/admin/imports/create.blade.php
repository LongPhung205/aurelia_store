@extends('admin.layouts.admin')

@section('title', 'Tạo Phiếu Nhập Kho')

@section('content')
<div class="px-0 w-full h-[calc(100vh-90px)] flex flex-col min-h-0">

    <form action="{{ route('admin.imports.store') }}" method="POST" id="importForm" class="flex-1 flex flex-col min-h-0">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 flex-1 min-h-0">
            <!-- Cột trái: Form nhập liệu (scrollable) -->
            <div class="lg:col-span-2 flex flex-col min-h-0">
                <x-admin.card class="flex-1 flex flex-col min-h-0 border-0 shadow-sm" bodyClass="flex-1 flex flex-col min-h-0" noPadding="true">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 shrink-0 bg-gray-50 dark:bg-gray-800 rounded-t-lg relative z-20">
                        <h5 class="mb-0 font-semibold text-gray-900 dark:text-white">Chi tiết hàng nhập</h5>
                        <div class="mt-4">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Thêm sản phẩm (Tìm theo SKU hoặc tên)</label>
                            <select id="productSelect" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="">-- Chọn sản phẩm --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->sku }} - {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="overflow-auto flex-1 relative bg-white dark:bg-gray-800">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400" id="importTable">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.1)]">
                                <tr>
                                    <th scope="col" class="px-4 py-3 min-w-[120px]">SKU</th>
                                    <th scope="col" class="px-4 py-3 min-w-[250px]">Sản phẩm</th>
                                    <th scope="col" class="px-4 py-3 w-32 text-center">Số lượng</th>
                                    <th scope="col" class="px-4 py-3 w-40 text-right">Giá vốn (đ)</th>
                                    <th scope="col" class="px-4 py-3 w-40 text-right">Thành tiền (đ)</th>
                                    <th scope="col" class="px-4 py-3 w-16 text-center">Xóa</th>
                                </tr>
                            </thead>
                            <tbody id="importTableBody">
                                <!-- Dòng sẽ được thêm bằng JS -->
                                <tr id="emptyRow">
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">Vui lòng chọn sản phẩm để nhập kho.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </x-admin.card>
            </div>
            
            <!-- Cột phải: Thông tin phiếu & Submit -->
            <div class="lg:col-span-1 flex flex-col gap-6 overflow-y-auto">
                <x-admin.card class="border-0 shadow-sm">
                    <h5 class="mb-4 font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">Thông tin chứng từ</h5>
                    
                    <div class="mb-4">
                        <x-admin.select name="supplier_id" id="supplier_id" label="Nhà cung cấp" required="true">
                            <option value="">-- Chọn nhà cung cấp --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </x-admin.select>
                    </div>

                    <div class="mb-2">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Ghi chú phiếu nhập</label>
                        <textarea name="note" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Lý do nhập, thông tin xe hàng..."></textarea>
                    </div>
                </x-admin.card>

                <x-admin.card class="border-0 shadow-sm">
                    <h5 class="mb-4 font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">Thanh toán</h5>
                    
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Tổng số lượng:</span>
                            <span class="text-lg font-bold text-gray-900 dark:text-white" id="summaryQty">0</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Tổng tiền:</span>
                            <span class="text-xl font-bold text-blue-600 dark:text-blue-400" id="summaryTotal">0</span>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-3 text-center dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">
                            <i class="bi bi-save mr-2"></i> Lưu Bản Nháp (Pending)
                        </button>
                        <p class="mt-2 text-xs text-gray-500 text-center dark:text-gray-400">Lưu nháp chưa cộng số lượng vào tồn kho thực tế.</p>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </form>
</div>

<!-- Matrix Modal -->
<div id="matrixModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-[100] justify-center items-center w-full md:inset-0 h-full max-h-full bg-gray-900 bg-opacity-50 dark:bg-opacity-80">
    <div class="relative p-4 w-full max-w-4xl max-h-full mx-auto mt-10 md:mt-20">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 flex flex-col max-h-[80vh]">
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 shrink-0">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="matrixModalTitle">
                    Nhập số lượng
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" id="closeMatrixModal">
                    <i class="bi bi-x-lg"></i>
                    <span class="sr-only">Đóng modal</span>
                </button>
            </div>
            <div class="p-4 md:p-5 border-b border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 shrink-0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Giá nhập chung (đ) <span class="text-red-500">*</span></label>
                        <input type="number" id="bulkCostPrice" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 placeholder-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-500 dark:text-white" placeholder="Bắt buộc nhập" min="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Số lượng chung</label>
                        <div class="flex">
                            <input type="number" id="bulkQty" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-l-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 placeholder-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-500 dark:text-white" placeholder="Điền nhanh cho tất cả" min="0">
                            <button type="button" id="applyBulkQtyBtn" class="px-4 text-sm font-medium text-white bg-gray-600 border border-gray-600 rounded-r-lg hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800 whitespace-nowrap">Áp dụng</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-4 md:p-5 overflow-auto flex-1" id="matrixModalBody">
                <!-- Matrix Table will be rendered here via JS -->
            </div>
            <div class="flex items-center justify-end p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600 shrink-0">
                <button id="cancelMatrixBtn" type="button" class="py-2.5 px-5 me-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Hủy</button>
                <button id="confirmMatrixBtn" type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Xác nhận thêm</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.2.2/css/tom-select.css" rel="stylesheet">
    <style>
        /* Tùy chỉnh Tom Select cho phù hợp với giao diện Tailwind/Dark mode */
        .ts-control { border-radius: 0.5rem; border-color: #d1d5db; padding: 0.625rem; font-size: 0.875rem; min-height: 42px; display: flex; align-items: center; }
        .dark .ts-control { background-color: #374151; border-color: #4b5563; color: white; }
        .dark .ts-dropdown { background-color: #374151; border-color: #4b5563; color: white; }
        .dark .ts-dropdown .option:hover, .dark .ts-dropdown .active { background-color: #4b5563; color: white; }
        .dark .ts-control input { color: white; }
        .ts-dropdown .create { padding: 8px; border-top: 1px solid #e5e7eb; background-color: #f9fafb; color: #2563eb; font-weight: 500; }
        .dark .ts-dropdown .create { border-top-color: #4b5563; background-color: #1f2937; color: #60a5fa; }
    </style>
@endpush

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.2.2/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        try {
            // Initialize Tom Select for Supplier
            new TomSelect("#supplier_id", {
                create: true,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: "-- Chọn hoặc nhập tên nhà cung cấp mới --",
                render: {
                    option_create: function(data, escape) {
                        return '<div class="create"><i class="bi bi-plus-circle mr-1"></i> Thêm mới nhà cung cấp: <strong>' + escape(data.input) + '</strong></div>';
                    },
                    no_results: function(data, escape) {
                        return '<div class="no-results p-2 text-gray-500">Nhấn Enter để thêm "' + escape(data.input) + '"</div>';
                    }
                }
            });
            
            // Initialize Tom Select for Products
            new TomSelect("#productSelect", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: "-- Tìm sản phẩm theo mã SKU hoặc tên --"
            });
        } catch (e) {
            console.error("TomSelect init error:", e);
        }

        const productsData = @json($products);
        const select = document.getElementById('productSelect');
        const tbody = document.getElementById('importTableBody');
        const emptyRow = document.getElementById('emptyRow');
        const summaryQty = document.getElementById('summaryQty');
        const summaryTotal = document.getElementById('summaryTotal');
        const matrixModal = document.getElementById('matrixModal');
        const matrixModalTitle = document.getElementById('matrixModalTitle');
        const matrixModalBody = document.getElementById('matrixModalBody');
        const closeMatrixModalBtn = document.getElementById('closeMatrixModal');
        const cancelMatrixBtn = document.getElementById('cancelMatrixBtn');
        const confirmMatrixBtn = document.getElementById('confirmMatrixBtn');
        const bulkCostPriceInput = document.getElementById('bulkCostPrice');
        const bulkQtyInput = document.getElementById('bulkQty');
        const applyBulkQtyBtn = document.getElementById('applyBulkQtyBtn');
        
        let currentVariants = [];

        applyBulkQtyBtn.addEventListener('click', function() {
            const qty = parseInt(bulkQtyInput.value);
            if (qty >= 0) {
                const inputs = matrixModalBody.querySelectorAll('.matrix-input');
                inputs.forEach(input => {
                    input.value = qty;
                });
            }
        });

        function updateSummary() {
            let totalQty = 0;
            let totalAmount = 0;
            
            document.querySelectorAll('.qty-input').forEach(input => {
                totalQty += parseInt(input.value) || 0;
            });
            
            document.querySelectorAll('.price-input').forEach(input => {
                const tr = input.closest('tr');
                const qty = parseInt(tr.querySelector('.qty-input').value) || 0;
                totalAmount += (qty * (parseFloat(input.value) || 0));
            });
            
            summaryQty.textContent = totalQty;
            summaryTotal.textContent = formatCurrency(totalAmount);
        }

        function showModal() {
            matrixModal.classList.remove('hidden');
            matrixModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function hideModal() {
            matrixModal.classList.add('hidden');
            matrixModal.classList.remove('flex');
            document.body.style.overflow = '';
            bulkCostPriceInput.value = '';
            bulkQtyInput.value = '';
            if (select.tomselect) {
                select.tomselect.clear(true); // true = silent, to avoid triggering 'change' again
            } else {
                select.value = '';
            }
        }

        [closeMatrixModalBtn, cancelMatrixBtn].forEach(btn => btn.addEventListener('click', hideModal));

        select.addEventListener('change', function() {
            if (!this.value) return;

            const productId = parseInt(this.value);
            const product = productsData.find(p => p.id === productId);
            
            if (!product) return;
            
            matrixModalTitle.textContent = "Nhập số lượng lô: " + product.name;
            currentVariants = product.variants || [];
            
            if (currentVariants.length === 0) {
                Swal.fire({icon: 'info', title: 'Thông báo', text: 'Sản phẩm này chưa có biến thể nào.'});
                hideModal();
                return;
            }

            // Xây dựng ma trận
            const colorsMap = new Map();
            const sizesMap = new Map();
            
            currentVariants.forEach(v => {
                const colorId = v.color ? v.color.id : 0;
                const colorName = v.color ? v.color.name : 'Mặc định';
                if (!colorsMap.has(colorId)) colorsMap.set(colorId, colorName);
                
                const sizeId = v.size ? v.size.id : 0;
                const sizeName = v.size ? v.size.name : 'Mặc định';
                if (!sizesMap.has(sizeId)) sizesMap.set(sizeId, sizeName);
            });

            const colors = Array.from(colorsMap.entries()); // [id, name]
            const sizes = Array.from(sizesMap.entries());

            let html = '<div class="overflow-x-auto relative rounded-lg border border-gray-200 dark:border-gray-600">';
            html += '<table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">';
            html += '<thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">';
            html += '<tr><th class="px-4 py-3 sticky left-0 bg-gray-50 dark:bg-gray-700 z-10 w-32 border-r dark:border-gray-600">Màu \\ Size</th>';
            sizes.forEach(s => {
                html += `<th class="px-4 py-3 text-center min-w-[80px] border-b border-gray-200 dark:border-gray-600">${s[1]}</th>`;
            });
            html += '</tr></thead><tbody>';

            colors.forEach((c, index) => {
                const isLast = index === colors.length - 1;
                html += `<tr class="bg-white dark:bg-gray-800 ${!isLast ? 'border-b dark:border-gray-700' : ''}">`;
                html += `<td class="px-4 py-2 font-medium text-gray-900 dark:text-white sticky left-0 bg-white dark:bg-gray-800 z-10 border-r dark:border-gray-600 shadow-[1px_0_0_0_#e5e7eb] dark:shadow-[1px_0_0_0_#4b5563]">${c[1]}</td>`;
                sizes.forEach(s => {
                    const variant = currentVariants.find(v => (v.color_id || 0) === c[0] && (v.size_id || 0) === s[0]);
                    if (variant) {
                        html += `<td class="p-2 border-l border-gray-100 dark:border-gray-700">
                            <input type="number" class="matrix-input bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 text-center placeholder-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-500 dark:text-white" 
                                data-id="${variant.id}" min="0" placeholder="0">
                        </td>`;
                    } else {
                        html += `<td class="p-2 border-l border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-center text-gray-300 dark:text-gray-600 select-none">
                            -
                        </td>`;
                    }
                });
                html += '</tr>';
            });
            html += '</tbody></table></div>';

            matrixModalBody.innerHTML = html;
            showModal();
            
            // Focus ô đầu tiên
            setTimeout(() => {
                const firstInput = matrixModalBody.querySelector('input');
                if (firstInput) firstInput.focus();
            }, 100);
        });

        confirmMatrixBtn.addEventListener('click', function() {
            const inputs = matrixModalBody.querySelectorAll('.matrix-input');
            const globalCost = bulkCostPriceInput.value;
            let added = false;
            let hasQuantity = false;
            
            inputs.forEach(input => {
                if (parseInt(input.value) > 0) hasQuantity = true;
            });
            
            if (hasQuantity && globalCost === '') {
                Swal.fire({
                    icon: 'warning', 
                    title: 'Bắt buộc nhập', 
                    text: 'Vui lòng điền Giá nhập chung cho lô hàng này.'
                });
                bulkCostPriceInput.focus();
                return;
            }
            
            inputs.forEach(input => {
                const qty = parseInt(input.value);
                if (qty > 0) {
                    const variantId = parseInt(input.dataset.id);
                    const variant = currentVariants.find(v => v.id === variantId);
                    if (variant) {
                        let finalCost = globalCost !== '' ? parseFloat(globalCost) : (variant.cost_price > 0 ? variant.cost_price : '');
                        addVariantToTable(variant, qty, finalCost);
                        added = true;
                    }
                }
            });
            
            if (added) {
                if (emptyRow) emptyRow.style.display = 'none';
                updateSummary();
            }
            
            hideModal();
        });

        function addVariantToTable(variant, qty, providedCost) {
            if (document.getElementById(`row-${variant.id}`)) {
                const row = document.getElementById(`row-${variant.id}`);
                const qtyInput = row.querySelector('.qty-input');
                qtyInput.value = parseInt(qtyInput.value) + qty;
                
                // Nếu có nhập giá nhập chung, cập nhật lại giá cho biến thể đã tồn tại trong bảng
                if (providedCost !== '') {
                    const priceInput = row.querySelector('.price-input');
                    priceInput.value = providedCost;
                }
                
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                row.querySelector('.subtotal-text').innerText = formatCurrency(parseInt(qtyInput.value) * price);
                return;
            }

            // Gắn tạm biến thể product để lấy tên do load relations
            const sku = variant.sku;
            const name = (variant.product ? variant.product.name : '') + (variant.color ? ' - ' + variant.color.name : '') + (variant.size ? ' - ' + variant.size.name : '');
            const cost = providedCost !== '' ? providedCost : (variant.cost_price > 0 ? variant.cost_price : '');
            const subtotal = cost !== '' ? (qty * cost) : 0;
            
            let rawUrl = variant.thumbnail_url || (variant.product && variant.product.images && variant.product.images.length > 0 ? variant.product.images[0].image_url : null);
            let imageUrl = '';
            if (rawUrl) {
                imageUrl = rawUrl.startsWith('http') ? rawUrl : `/storage/${rawUrl}`;
            } else {
                imageUrl = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(sku) + '&background=F3F4F6&color=6B7280';
            }

            const tr = document.createElement('tr');
            tr.id = `row-${variant.id}`;
            tr.className = 'bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600';
            
            tr.innerHTML = `
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white align-middle">
                    <input type="hidden" name="variants[]" value="${variant.id}">
                    ${sku}
                </td>
                <td class="px-4 py-3 align-middle">
                    <div class="flex items-center gap-3">
                        <img src="${imageUrl}" alt="${name}" class="w-12 h-12 object-cover rounded bg-gray-100 shrink-0">
                        <span class="font-medium">${name}</span>
                    </div>
                </td>
                <td class="px-4 py-3 align-middle">
                    <input type="number" name="quantities[]" class="qty-input bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 text-center dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" value="${qty}" min="1" required>
                </td>
                <td class="px-4 py-3 align-middle">
                    <input type="number" name="prices[]" class="price-input bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 text-right dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" value="${cost}" min="0" required>
                </td>
                <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white subtotal-text align-middle">
                    ${formatCurrency(subtotal)}
                </td>
                <td class="px-4 py-3 text-center align-middle">
                    <button type="button" class="text-red-600 hover:text-red-800 dark:text-red-500 dark:hover:text-red-400 btn-remove-row">
                        <i class="bi bi-trash text-lg"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
        }



        tbody.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-remove-row');
            if (btn) {
                btn.closest('tr').remove();
                if (tbody.querySelectorAll('tr').length === 1 && emptyRow) {
                    emptyRow.style.display = 'table-row';
                }
                updateSummary();
            }
        });

        tbody.addEventListener('input', function(e) {
            if (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input')) {
                const tr = e.target.closest('tr');
                const qty = parseFloat(tr.querySelector('.qty-input').value) || 0;
                const price = parseFloat(tr.querySelector('.price-input').value) || 0;
                const subtotal = qty * price;
                
                tr.querySelector('.subtotal-text').innerText = formatCurrency(subtotal);
                updateSummary();
            }
        });

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount).replace('₫', 'đ');
        }
        
        document.getElementById('importForm').addEventListener('submit', function(e) {
            let hasItems = false;
            tbody.querySelectorAll('tr').forEach(tr => {
                if (tr.id !== 'emptyRow') hasItems = true;
            });
            
            if (!hasItems) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: 'Vui lòng thêm ít nhất một sản phẩm để tạo phiếu nhập.',
                });
            }
        });
    });
</script>
@endpush
