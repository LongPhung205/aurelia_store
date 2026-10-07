@extends('admin.layouts.admin')

@section('title', 'Tạo Phiếu Nhập Kho')



@section('content')
<div class="px-0 w-full h-[calc(100vh-90px)] flex flex-col min-h-0">

    <form action="{{ route('admin.imports.store') }}" method="POST" id="importForm" class="flex-1 flex flex-col min-h-0">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 flex-1 min-h-0">
            <!-- Cột trái: Form nhập liệu (scrollable) -->
            <div class="lg:col-span-2 flex flex-col min-h-0">
                <x-admin.card class="flex-1 flex flex-col min-h-0 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden" bodyClass="flex-1 flex flex-col min-h-0" noPadding="true">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700 shrink-0 bg-slate-50 dark:bg-slate-800/80 relative z-20">
                        <h5 class="mb-0 font-medium text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="bi bi-box-seam text-primary-600 dark:text-primary-400"></i> Chi tiết hàng nhập
                        </h5>
                        <div class="mt-4">
                            <label class="block mb-2 text-xs font-medium text-slate-700 dark:text-slate-300 uppercase tracking-wider">Thêm sản phẩm (Tìm theo SKU hoặc tên)</label>
                            <div class="flex gap-2">
                                <div class="flex-1">
                                    <select id="productSelect" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-slate-500 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                        <option value="">-- Chọn sản phẩm --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">
                                                {{ $product->sku }} - {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" id="openBulkSelectBtn" class="h-[42px] px-4 text-primary-700 bg-primary-100 hover:bg-primary-200 dark:text-primary-300 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 rounded-lg font-medium flex items-center gap-2 whitespace-nowrap transition-colors">
                                    <i class="bi bi-list-check text-lg"></i> <span class="hidden sm:inline">Chọn nhiều</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center px-4 py-2.5 bg-slate-50/50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700 shrink-0">
                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300 flex items-center gap-1.5 uppercase tracking-wider">
                            <i class="bi bi-lightning-charge text-amber-500"></i> Điền nhanh
                        </span>
                        <div class="flex gap-2 items-center">
                            <input type="number" id="quickGlobalQty" placeholder="Số lượng chung" class="text-xs w-32 bg-white border border-slate-300 rounded-md px-2.5 py-1.5 focus:ring-primary-500 focus:border-primary-500 dark:bg-slate-900 dark:border-slate-600 dark:text-white" min="1">
                            <input type="number" id="quickGlobalPrice" placeholder="Giá nhập chung" class="text-xs w-32 bg-white border border-slate-300 rounded-md px-2.5 py-1.5 focus:ring-primary-500 focus:border-primary-500 dark:bg-slate-900 dark:border-slate-600 dark:text-white" min="0">
                            <button type="button" id="applyGlobalBtn" class="text-xs font-medium bg-slate-200 hover:bg-slate-300 text-slate-800 px-3 py-1.5 rounded-md transition-colors dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200">Áp dụng</button>
                        </div>
                    </div>
                    
                    <div class="overflow-auto flex-1 relative bg-white dark:bg-slate-900">
                        <table class="w-full text-sm text-left border-collapse text-xs" id="importTable">
                            <thead class="bg-slate-50 dark:bg-slate-800/80 sticky top-0 z-10 border-b border-slate-200 dark:border-slate-700 shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300 w-32">SKU</th>
                                    <th scope="col" class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300">Sản phẩm</th>
                                    <th scope="col" class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300 w-32 text-center">Số lượng</th>
                                    <th scope="col" class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300 w-40 text-right">Giá vốn (đ)</th>
                                    <th scope="col" class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300 w-40 text-right">Thành tiền (đ)</th>
                                    <th scope="col" class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300 w-16 text-center">Xóa</th>
                                </tr>
                            </thead>
                            <tbody id="importTableBody" class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                <!-- Dòng sẽ được thêm bằng JS -->
                                <tr id="emptyRow">
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                        <i class="bi bi-inbox text-3xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
                                        Vui lòng chọn sản phẩm để nhập kho.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </x-admin.card>
            </div>
            
            <!-- Cột phải: Thông tin phiếu & Submit -->
            <div class="lg:col-span-1 flex flex-col gap-4 overflow-y-auto">
                <x-admin.card class="border border-slate-200 dark:border-slate-700 shadow-sm" noPadding="true">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80">
                        <h5 class="font-medium text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="bi bi-file-earmark-text text-primary-600 dark:text-primary-400"></i> Thông tin chứng từ
                        </h5>
                    </div>
                    
                    <div class="p-4 space-y-4">
                        <div>
                            <label class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 uppercase tracking-wider">Nhà cung cấp <span class="text-rose-500">*</span></label>
                            <select name="supplier_id" id="supplier_id" class="w-full text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white p-2.5 focus:ring-primary-500" required>
                                <option value="">-- Chọn nhà cung cấp --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 uppercase tracking-wider">Ghi chú phiếu nhập</label>
                            <textarea name="note" rows="3" class="w-full text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white p-2.5 focus:ring-primary-500" placeholder="Lý do nhập, thông tin xe hàng..."></textarea>
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card class="border border-slate-200 dark:border-slate-700 shadow-sm" noPadding="true">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80">
                        <h5 class="font-medium text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="bi bi-cash-stack text-primary-600 dark:text-primary-400"></i> Thanh toán
                        </h5>
                    </div>
                    
                    <div class="p-4">
                        <div class="mb-5 p-3.5 bg-slate-50 dark:bg-slate-900/60 rounded-xl space-y-2.5 border border-slate-100 dark:border-slate-800">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tổng số lượng:</span>
                                <span class="text-base font-medium text-slate-800 dark:text-white" id="summaryQty">0</span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-slate-200 dark:border-slate-700">
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tổng tiền:</span>
                                <span class="text-lg font-medium text-primary-600 dark:text-primary-400" id="summaryTotal">0đ</span>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-colors dark:bg-primary-600 dark:hover:bg-primary-700 flex items-center justify-center gap-2 shadow-sm">
                                <i class="bi bi-save"></i> Lưu Bản Nháp (Pending)
                            </button>
                            <p class="mt-2.5 text-[11px] text-slate-500 text-center dark:text-slate-400">Lưu nháp chưa cộng số lượng vào tồn kho.</p>
                        </div>
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="matrixModalTitle">
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

<!-- Bulk Select Modal -->
<div id="bulkSelectModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-[100] justify-center items-center w-full md:inset-0 h-full max-h-full bg-slate-900/60 backdrop-blur-sm">
    <div class="relative p-4 w-full max-w-5xl max-h-full mx-auto mt-10 md:mt-16">
        <div class="relative bg-white rounded-2xl shadow-2xl dark:bg-slate-800 flex flex-col max-h-[85vh] overflow-hidden border border-slate-100 dark:border-slate-700">
            <div class="flex justify-between items-center p-5 border-b border-slate-100 dark:border-slate-700 shrink-0 bg-white dark:bg-slate-800">
                <h3 class="text-base font-medium text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="bi bi-list-check text-primary-600 dark:text-primary-400 text-xl"></i> Chọn nhiều sản phẩm
                </h3>
                <button type="button" class="text-slate-400 hover:bg-slate-100 hover:text-slate-600 rounded-full w-8 h-8 flex justify-center items-center transition-colors dark:hover:bg-slate-700 dark:hover:text-white" id="closeBulkSelectBtn">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 shrink-0 bg-slate-50 dark:bg-slate-800/80 relative">
                <i class="bi bi-search absolute left-7 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="bulkSearchInput" placeholder="Tìm tên sản phẩm, SKU hoặc phân loại..." class="bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white transition-all shadow-sm">
            </div>

            <div class="overflow-y-auto flex-1 p-0 bg-white dark:bg-slate-900">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80 sticky top-0 z-10 border-b border-slate-200 dark:border-slate-700 shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
                        <tr>
                            <th scope="col" class="px-5 py-3 w-14 text-center">
                                <input type="checkbox" id="bulkCheckAll" class="w-4 h-4 text-primary-600 bg-white border-slate-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-slate-800 dark:bg-slate-700 dark:border-slate-600 cursor-pointer">
                            </th>
                            <th scope="col" class="px-5 py-3 font-medium text-slate-700 dark:text-slate-300">Sản phẩm / Phân loại</th>
                            <th scope="col" class="px-5 py-3 font-medium text-slate-700 dark:text-slate-300 w-40">SKU</th>
                            <th scope="col" class="px-5 py-3 font-medium text-slate-700 dark:text-slate-300 w-32 text-right">Giá gốc</th>
                        </tr>
                    </thead>
                    <tbody id="bulkSelectTbody" class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        <!-- Rendered by JS -->
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 dark:border-slate-700 shrink-0 flex justify-between items-center bg-white dark:bg-slate-800">
                <div class="text-sm font-medium text-slate-600 dark:text-slate-300">
                    Đã chọn: <span id="bulkSelectedCount" class="text-primary-600 font-medium text-lg px-2 bg-primary-50 dark:bg-primary-900/30 rounded-md">0</span> sản phẩm
                </div>
                <button type="button" id="confirmBulkSelectBtn" class="text-white bg-primary-600 hover:bg-primary-700 font-medium rounded-lg text-sm px-6 py-2 shadow-sm transition-colors flex items-center gap-1.5">
                    <i class="bi bi-plus-lg"></i> Thêm vào phiếu
                </button>
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
        let allVariants = []; // Store all variants flattened

        // Flatten variants for bulk select
        productsData.forEach(p => {
            if (p.variants) {
                p.variants.forEach(v => {
                    allVariants.push({
                        ...v,
                        product_name: p.name,
                        product_id: p.id
                    });
                });
            }
        });

        // Quick Global Apply
        const quickGlobalQty = document.getElementById('quickGlobalQty');
        const quickGlobalPrice = document.getElementById('quickGlobalPrice');
        const applyGlobalBtn = document.getElementById('applyGlobalBtn');
        
        if (applyGlobalBtn) {
            applyGlobalBtn.addEventListener('click', () => {
                const q = quickGlobalQty.value;
                const p = quickGlobalPrice.value;
                
                let changed = false;
                document.querySelectorAll('#importTableBody tr').forEach(tr => {
                    if (tr.id === 'emptyRow') return;
                    
                    if (q !== '') {
                        tr.querySelector('.qty-input').value = q;
                        changed = true;
                    }
                    if (p !== '') {
                        tr.querySelector('.price-input').value = p;
                        changed = true;
                    }
                    
                    if (changed) {
                        const currentQ = parseFloat(tr.querySelector('.qty-input').value) || 0;
                        const currentP = parseFloat(tr.querySelector('.price-input').value) || 0;
                        tr.querySelector('.subtotal-text').innerText = formatCurrency(currentQ * currentP);
                    }
                });
                
                if (changed) updateSummary();
                if (q !== '') quickGlobalQty.value = '';
                if (p !== '') quickGlobalPrice.value = '';
                
                // Show a small success toast if SweetAlert is available
                if (changed && typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Đã áp dụng hàng loạt',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        }

        // Bulk Select Logic
        const bulkSelectModal = document.getElementById('bulkSelectModal');
        const openBulkSelectBtn = document.getElementById('openBulkSelectBtn');
        const closeBulkSelectBtn = document.getElementById('closeBulkSelectBtn');
        const bulkSearchInput = document.getElementById('bulkSearchInput');
        const bulkSelectTbody = document.getElementById('bulkSelectTbody');
        const bulkCheckAll = document.getElementById('bulkCheckAll');
        const bulkSelectedCount = document.getElementById('bulkSelectedCount');
        const confirmBulkSelectBtn = document.getElementById('confirmBulkSelectBtn');

        function renderBulkVariants(query = '') {
            query = query.toLowerCase();
            
            const filteredProducts = productsData.filter(p => {
                if (!p.variants || p.variants.length === 0) return false;
                
                const pName = (p.name || '').toLowerCase();
                const pSku = (p.sku || '').toLowerCase();
                if (pName.includes(query) || pSku.includes(query)) return true;
                
                return p.variants.some(v => {
                    const vSku = (v.sku || '').toLowerCase();
                    const color = (v.color ? v.color.name : '').toLowerCase();
                    const size = (v.size ? v.size.name : '').toLowerCase();
                    return vSku.includes(query) || color.includes(query) || size.includes(query);
                });
            });

            bulkSelectTbody.innerHTML = '';
            
            filteredProducts.forEach(p => {
                // Product Row
                const pTr = document.createElement('tr');
                pTr.className = 'bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700';
                
                let totalVariants = p.variants.length;
                let alreadyInTableVariants = p.variants.filter(v => document.getElementById(`row-${v.id}`) !== null).length;
                
                let productImg = 'https://ui-avatars.com/api/?name='+encodeURIComponent(p.sku)+'&background=F3F4F6&color=6B7280';
                if (p.images && p.images.length > 0) {
                    productImg = p.images[0].image_url.startsWith('http') ? p.images[0].image_url : '/storage/' + p.images[0].image_url;
                }

                pTr.innerHTML = `
                    <td class="px-5 py-3 text-center align-middle">
                        <input type="checkbox" class="product-bulk-cb w-4 h-4 text-primary-600 bg-white border-slate-300 rounded focus:ring-primary-500 cursor-pointer dark:bg-slate-700 dark:border-slate-600" data-product-id="${p.id}" ${alreadyInTableVariants === totalVariants ? 'disabled checked' : ''}>
                    </td>
                    <td class="px-5 py-3 align-middle cursor-pointer" onclick="toggleProductVariants(${p.id})">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-chevron-right text-slate-400 text-xs transition-transform" id="icon-product-${p.id}"></i>
                            <img src="${productImg}" class="w-8 h-8 object-cover rounded border border-slate-200 dark:border-slate-700 shrink-0">
                            <div class="font-medium text-slate-800 dark:text-white">${p.name} <span class="text-xs font-normal text-slate-500 ml-1">(${p.variants.length} phân loại)</span></div>
                        </div>
                    </td>
                    <td class="px-5 py-3 font-mono font-medium text-slate-700 dark:text-slate-300 align-middle">${p.sku}</td>
                    <td class="px-5 py-3 text-right font-medium text-slate-500 dark:text-slate-400 align-middle">-</td>
                `;
                bulkSelectTbody.appendChild(pTr);

                // Variants Rows
                p.variants.forEach(v => {
                    const isAlreadyInTable = document.getElementById(`row-${v.id}`) !== null;
                    const variantName = (v.color ? v.color.name : '') + (v.size ? (v.color ? ' - ' : '') + v.size.name : '');
                    const displayName = variantName ? variantName : 'Mặc định';
                    
                    const vTr = document.createElement('tr');
                    vTr.className = `variant-row variant-of-${p.id} hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-700/60 hidden ${isAlreadyInTable ? 'opacity-50' : ''}`;
                    vTr.innerHTML = `
                        <td class="px-5 py-2.5 text-center align-middle">
                            <input type="checkbox" value="${v.id}" data-parent-id="${p.id}" class="bulk-variant-cb w-4 h-4 text-primary-600 bg-white border-slate-300 rounded focus:ring-primary-500 cursor-pointer dark:bg-slate-700 dark:border-slate-600" ${isAlreadyInTable ? 'disabled checked' : ''}>
                        </td>
                        <td class="px-5 py-2.5 align-middle pl-14">
                            <div class="flex items-center gap-3">
                                <div class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></div>
                                <div class="font-medium text-slate-700 dark:text-slate-300">${displayName}</div>
                            </div>
                        </td>
                        <td class="px-5 py-2.5 font-mono text-xs font-medium text-slate-600 dark:text-slate-400 align-middle">${v.sku}</td>
                        <td class="px-5 py-2.5 text-right font-medium text-slate-800 dark:text-white align-middle">${formatCurrency(v.cost_price || 0)}</td>
                    `;
                    
                    if (!isAlreadyInTable) {
                        vTr.addEventListener('click', function(e) {
                            if(e.target.type !== 'checkbox') {
                                const cb = vTr.querySelector('.bulk-variant-cb');
                                cb.checked = !cb.checked;
                                updateBulkCount();
                                updateProductCheckbox(p.id);
                            }
                        });
                    }
                    bulkSelectTbody.appendChild(vTr);
                });

                // Expand if searching
                if (query !== '') {
                    toggleProductVariants(p.id, true);
                }
                
                // Handle product checkbox click
                const productCb = pTr.querySelector('.product-bulk-cb');
                if (productCb && !productCb.disabled) {
                    productCb.addEventListener('change', function(e) {
                        const checked = e.target.checked;
                        bulkSelectTbody.querySelectorAll(`.variant-of-${p.id} .bulk-variant-cb:not([disabled])`).forEach(cb => {
                            cb.checked = checked;
                        });
                        updateBulkCount();
                    });
                }
            });
            updateBulkCount();
        }

        window.toggleProductVariants = function(productId, forceShow = false) {
            const rows = document.querySelectorAll(`.variant-of-${productId}`);
            const icon = document.getElementById(`icon-product-${productId}`);
            
            let isHidden = true;
            if (rows.length > 0) {
                isHidden = rows[0].classList.contains('hidden');
            }
            
            if (forceShow && !isHidden) return; // already shown
            
            rows.forEach(row => {
                if (isHidden || forceShow) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
            
            if (icon) {
                if (isHidden || forceShow) {
                    icon.classList.remove('bi-chevron-right');
                    icon.classList.add('bi-chevron-down');
                } else {
                    icon.classList.remove('bi-chevron-down');
                    icon.classList.add('bi-chevron-right');
                }
            }
        };

        function updateProductCheckbox(productId) {
            const productCb = document.querySelector(`.product-bulk-cb[data-product-id="${productId}"]`);
            if (!productCb) return;
            
            const allVariants = bulkSelectTbody.querySelectorAll(`.variant-of-${productId} .bulk-variant-cb:not([disabled])`);
            if (allVariants.length === 0) return;
            
            const checkedVariants = bulkSelectTbody.querySelectorAll(`.variant-of-${productId} .bulk-variant-cb:checked:not([disabled])`);
            
            if (checkedVariants.length === 0) {
                productCb.checked = false;
                productCb.indeterminate = false;
            } else if (checkedVariants.length === allVariants.length) {
                productCb.checked = true;
                productCb.indeterminate = false;
            } else {
                productCb.checked = false;
                productCb.indeterminate = true;
            }
        }

        function updateBulkCount() {
            const checkboxes = bulkSelectTbody.querySelectorAll('.bulk-variant-cb:not([disabled])');
            const checked = bulkSelectTbody.querySelectorAll('.bulk-variant-cb:checked:not([disabled])').length;
            bulkSelectedCount.textContent = checked;
            
            if (checkboxes.length > 0) {
                if (checked === 0) {
                    bulkCheckAll.checked = false;
                    bulkCheckAll.indeterminate = false;
                } else if (checked === checkboxes.length) {
                    bulkCheckAll.checked = true;
                    bulkCheckAll.indeterminate = false;
                } else {
                    bulkCheckAll.checked = false;
                    bulkCheckAll.indeterminate = true;
                }
            } else {
                bulkCheckAll.checked = false;
                bulkCheckAll.indeterminate = false;
            }
        }

        openBulkSelectBtn.addEventListener('click', () => {
            renderBulkVariants();
            bulkSelectModal.classList.remove('hidden');
            bulkSelectModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            setTimeout(() => bulkSearchInput.focus(), 100);
        });

        closeBulkSelectBtn.addEventListener('click', () => {
            hideModalBulk();
        });

        bulkSearchInput.addEventListener('input', (e) => {
            renderBulkVariants(e.target.value);
        });

        bulkCheckAll.addEventListener('change', (e) => {
            const checked = e.target.checked;
            
            // Toggle all variants
            bulkSelectTbody.querySelectorAll('.bulk-variant-cb:not([disabled])').forEach(cb => {
                cb.checked = checked;
            });
            
            // Toggle all products
            bulkSelectTbody.querySelectorAll('.product-bulk-cb:not([disabled])').forEach(cb => {
                cb.checked = checked;
                cb.indeterminate = false;
            });
            
            updateBulkCount();
        });

        bulkSelectTbody.addEventListener('change', (e) => {
            if (e.target.classList.contains('bulk-variant-cb')) {
                const parentId = e.target.getAttribute('data-parent-id');
                if (parentId) updateProductCheckbox(parentId);
                updateBulkCount();
            }
        });

        confirmBulkSelectBtn.addEventListener('click', () => {
            const selectedIds = Array.from(bulkSelectTbody.querySelectorAll('.bulk-variant-cb:checked:not([disabled])')).map(cb => parseInt(cb.value));
            
            if (selectedIds.length === 0) {
                hideModalBulk();
                return;
            }
            
            let added = false;
            selectedIds.forEach(id => {
                const variant = allVariants.find(v => v.id === id);
                if (variant) {
                    addVariantToTable(variant, 1, variant.cost_price || ''); // Default qty = 1
                    added = true;
                }
            });
            
            if (added) {
                if (emptyRow) emptyRow.style.display = 'none';
                updateSummary();
            }
            
            hideModalBulk();
        });

        function hideModalBulk() {
            bulkSelectModal.classList.add('hidden');
            bulkSelectModal.classList.remove('flex');
            document.body.style.overflow = '';
        }

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
            tr.className = 'hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors';
            
            tr.innerHTML = `
                <td class="px-4 py-3 font-mono font-medium text-slate-800 dark:text-slate-200 align-middle">
                    <input type="hidden" name="variants[]" value="${variant.id}">
                    ${sku}
                </td>
                <td class="px-4 py-3 align-middle">
                    <div class="flex items-center gap-3">
                        <img src="${imageUrl}" alt="${name}" class="w-10 h-10 object-cover rounded bg-slate-100 dark:bg-slate-800 shrink-0 border border-slate-200 dark:border-slate-700">
                        <span class="font-medium text-slate-800 dark:text-white">${name}</span>
                    </div>
                </td>
                <td class="px-4 py-3 align-middle">
                    <input type="number" name="quantities[]" class="qty-input w-full px-2.5 py-1.5 text-center text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-primary-500" value="${qty}" min="1" required>
                </td>
                <td class="px-4 py-3 align-middle">
                    <input type="number" name="prices[]" class="price-input w-full px-2.5 py-1.5 text-right text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-primary-500" value="${cost}" min="0" required>
                </td>
                <td class="px-4 py-3 text-right font-medium text-primary-600 dark:text-primary-400 subtotal-text align-middle">
                    ${formatCurrency(subtotal)}
                </td>
                <td class="px-4 py-3 text-center align-middle">
                    <button type="button" class="p-1.5 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded transition-colors btn-remove-row" title="Xóa">
                        <i class="bi bi-trash text-base"></i>
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
