@extends('admin.layouts.admin')

@section('content')
<div class="w-full h-[calc(100vh-90px)] flex flex-col p-4 sm:p-8 bg-white shadow sm:rounded-lg">
    <div class="shrink-0 flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Quản lý Sản Phẩm tham gia: {{ $flash_sale->name }}</h2>
        <a href="{{ route('admin.flash_sales.index') }}" class="text-blue-600 hover:underline">
            &larr; Quay lại danh sách
        </a>
    </div>

    <div class="shrink-0">

        @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="shrink-0 mb-4">
        <button type="button" data-modal-target="addProductsModal" data-modal-toggle="addProductsModal" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg flex items-center gap-2">
            <i class="bi bi-plus-lg"></i> Thêm Sản Phẩm
        </button>
    </div>

    <!-- Danh sách sản phẩm đã tham gia -->
    <div class="flex-1 overflow-auto relative border rounded-lg custom-scrollbar">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.1)]">
                <tr>
                    <th class="px-6 py-3">Sản phẩm</th>
                    <th class="px-6 py-3 text-red-600">Giá Flash Sale</th>
                    <th class="px-6 py-3">SL Mở bán FS</th>
                    <th class="px-6 py-3">Đã bán</th>
                    <th class="px-6 py-3 text-right">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4 flex items-center gap-4">
                        @if($item->product && $item->product->primary_image_url)
                            <img src="{{ Storage::url($item->product->primary_image_url) }}" class="w-12 h-12 object-cover rounded shadow-sm">
                        @else
                            <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center text-gray-400"><i class="bi bi-image"></i></div>
                        @endif
                        <span class="font-medium text-gray-900">{{ $item->product->name ?? 'N/A' }}</span>
                    </td>
                    <td class="px-6 py-4 font-bold text-red-600">{{ number_format($item->flash_sale_price) }} đ</td>
                    <td class="px-6 py-4">{{ $item->quantity }}</td>
                    <td class="px-6 py-4">{{ $item->sold_quantity }}</td>
                    <td class="px-6 py-4 text-right">
                        <form action="{{ route('admin.flash_sales.items.destroy', [$flash_sale, $item]) }}" method="POST" class="inline-block form-delete" data-confirm-title="Bạn muốn gỡ sản phẩm này khỏi Flash Sale?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-medium text-red-600 hover:underline">Gỡ</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Chưa có sản phẩm nào tham gia</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Chọn Sản Phẩm -->
<div id="addProductsModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-6xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 flex flex-col h-[calc(100vh-4rem)]">
            <!-- Modal header -->
            <div class="shrink-0 flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Chọn sản phẩm tham gia Flash Sale
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="addProductsModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-0 flex-1 overflow-hidden flex flex-col">
                <form action="{{ route('admin.flash_sales.items.store', $flash_sale) }}" method="POST" id="bulkAddForm" class="flex flex-col h-full">
                    @csrf
                    
                    <div class="flex-1 overflow-y-auto relative custom-scrollbar">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.1)]">
                                <tr>
                                    <th scope="col" class="p-4 w-4">
                                        <div class="flex items-center">
                                            <input id="checkbox-all" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                            <label for="checkbox-all" class="sr-only">checkbox</label>
                                        </div>
                                    </th>
                                    <th class="px-6 py-3">Sản phẩm</th>
                                    <th class="px-6 py-3">Giá gốc (tham khảo)</th>
                                    <th class="px-6 py-3">Giá Flash Sale (đ)</th>
                                    <th class="px-6 py-3">Số lượng bán</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                @php
                                    $isAdded = $items->contains('product_id', $product->id);
                                @endphp
                                <tr class="bg-white border-b hover:bg-gray-50 {{ $isAdded ? 'opacity-50' : '' }}">
                                    <td class="p-4">
                                        <div class="flex items-center">
                                            <input type="checkbox" name="selected_products[]" value="{{ $product->id }}" class="product-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" {{ $isAdded ? 'disabled' : '' }}>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 flex items-center gap-3">
                                        @if($product->primary_image_url)
                                            <img src="{{ Storage::url($product->primary_image_url) }}" class="w-10 h-10 object-cover rounded shadow-sm border">
                                        @else
                                            <div class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center text-gray-400 border"><i class="bi bi-image"></i></div>
                                        @endif
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                            @if($isAdded)
                                                <span class="text-xs text-red-500 font-semibold">(Đã tham gia)</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ number_format($product->variants->min('price')) }} đ
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" name="products[{{ $product->id }}][flash_sale_price]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2" min="0" placeholder="Nhập giá sale" {{ $isAdded ? 'disabled' : '' }}>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" name="products[{{ $product->id }}][quantity]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2" min="1" value="10" {{ $isAdded ? 'disabled' : '' }}>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Modal footer -->
                    <div class="shrink-0 flex items-center justify-end p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600 gap-3 bg-gray-50">
                        <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10" data-modal-hide="addProductsModal">Đóng</button>
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Xác nhận thêm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkAllBtn = document.getElementById('checkbox-all');
    const productCheckboxes = document.querySelectorAll('.product-checkbox:not([disabled])');
    
    checkAllBtn.addEventListener('change', function() {
        const isChecked = this.checked;
        productCheckboxes.forEach(cb => {
            cb.checked = isChecked;
        });
    });

    // Make inputs required only if their row is checked
    const form = document.getElementById('bulkAddForm');
    form.addEventListener('submit', function(e) {
        let hasChecked = false;
        let isValid = true;
        
        productCheckboxes.forEach(cb => {
            if (cb.checked) {
                hasChecked = true;
                const tr = cb.closest('tr');
                const priceInput = tr.querySelector('input[name*="[flash_sale_price]"]');
                if (!priceInput.value || priceInput.value < 0) {
                    isValid = false;
                    priceInput.classList.add('border-red-500');
                } else {
                    priceInput.classList.remove('border-red-500');
                }
            }
        });

        if (!hasChecked) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Opps...',
                text: 'Bạn chưa chọn sản phẩm nào!'
            });
            return;
        }

        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Vui lòng nhập giá Flash Sale hợp lệ cho các sản phẩm đã chọn.'
            });
            return;
        }
    });
});
</script>
@endpush
