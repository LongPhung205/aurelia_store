@extends('admin.layouts.admin')

@section('title', 'Tồn kho tổng quan')

@section('content')
<div class="px-0 w-full h-[calc(100vh-90px)] flex flex-col">
    <div class="flex justify-between items-center mb-6 shrink-0">
        <div>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Quản Lý Tồn Kho</h4>
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                            Bảng điều khiển
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="bi bi-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Tồn kho</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <x-admin.card class="flex-1 flex flex-col min-h-0 border-0 shadow-sm" bodyClass="flex-1 flex flex-col min-h-0" noPadding="true">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 shrink-0 bg-white dark:bg-gray-800 rounded-t-lg">
            <form action="{{ route('admin.inventory.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
                
                <div class="flex items-center">
                    <label class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Danh mục:</label>
                    <select name="category_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option value="">-- Tất cả --</option>
                        @php
                            $printCategory = function($category, $prefix = '') use (&$printCategory) {
                                $selected = request('category_id') == $category->id ? 'selected' : '';
                                echo "<option value='{$category->id}' {$selected}>{$prefix} {$category->name}</option>";
                                if ($category->children) {
                                    foreach ($category->children as $child) {
                                        $printCategory($child, $prefix . '--');
                                    }
                                }
                            };
                        @endphp
                        @foreach($categories as $category)
                            @php $printCategory($category); @endphp
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center ml-4">
                    <input id="low_stock" type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="low_stock" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300 cursor-pointer">
                        <span class="text-red-600 dark:text-red-400 font-semibold">Chỉ hiển thị sắp hết hàng</span>
                    </label>
                </div>
                
                <x-admin.button type="submit" variant="primary" size="sm" class="ml-auto">
                    <i class="bi bi-filter mr-1"></i> Lọc
                </x-admin.button>
                @if(request()->anyFilled(['category_id', 'low_stock']))
                    <a href="{{ route('admin.inventory.index') }}" class="text-sm text-gray-500 hover:text-blue-600 ml-2 underline">Xóa lọc</a>
                @endif
            </form>
        </div>
        
        <div class="overflow-auto flex-1 relative">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.1)]">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">Sản phẩm</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Danh mục</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-center">Tổng tồn kho</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-center">Trạng thái</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $isLowStock = $product->low_stock_count > 0;
                        @endphp
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 {{ $isLowStock ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @php
                                        $thumbnail = null;
                                        if ($product->images && $product->images->count() > 0) {
                                            $thumbnail = $product->images->first()->image_url;
                                        } else {
                                            $variantWithThumb = $product->variants->whereNotNull('thumbnail_url')->first();
                                            if ($variantWithThumb) {
                                                $thumbnail = $variantWithThumb->thumbnail_url;
                                            }
                                        }
                                        if (!$thumbnail) {
                                            $thumbnail = 'https://ui-avatars.com/api/?name=' . urlencode($product->name ?? 'NA') . '&background=F3F4F6&color=6B7280';
                                        }
                                    @endphp
                                    @if($thumbnail)
                                        <img class="w-10 h-10 rounded-md object-cover mr-3" src="{{ filter_var($thumbnail, FILTER_VALIDATE_URL) ? $thumbnail : asset('storage/'.$thumbnail) }}" alt="{{ $product->name }}">
                                    @else
                                        <div class="w-10 h-10 rounded-md bg-gray-200 dark:bg-gray-700 flex items-center justify-center mr-3 text-gray-400"><i class="bi bi-image"></i></div>
                                    @endif
                                    <div>
                                        <span class="font-semibold text-gray-900 dark:text-white block">{{ $product->name }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $product->variants_count }} phân loại (biến thể)</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @forelse($product->categories as $category)
                                    <x-admin.badge variant="dark" class="mr-1 mb-1">{{ $category->name }}</x-admin.badge>
                                @empty
                                    <span class="text-gray-500 text-xs">Chưa có</span>
                                @endforelse
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-bold {{ $isLowStock ? 'text-red-600 dark:text-red-400 text-lg' : 'text-gray-900 dark:text-white' }}">
                                    {{ $product->variants_sum_stock_quantity ?? 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if(($product->variants_sum_stock_quantity ?? 0) <= 0)
                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Hết hàng</span>
                                @elseif($isLowStock)
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300">Có phân loại sắp hết</span>
                                @else
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Còn hàng</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button type="button" class="view-details-btn text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:bg-blue-500 dark:hover:bg-blue-600 focus:outline-none dark:focus:ring-blue-800" 
                                    data-id="{{ $product->id }}">
                                    Xem chi tiết
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Không tìm thấy sản phẩm nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($products->hasPages())
            <x-slot name="footer">
                <div class="mt-2 flex justify-end w-full">
                    {{ $products->links('pagination::tailwind') }}
                </div>
            </x-slot>
        @endif
    </x-admin.card>
</div>

<!-- Modal for details -->
<div id="detailsModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full flex items-center justify-center bg-gray-900/50 dark:bg-gray-900/80">
    <div class="relative w-full max-w-4xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 flex flex-col max-h-[90vh]">
            <!-- Modal header -->
            <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600 shrink-0">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Chi tiết tồn kho biến thể
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" onclick="document.getElementById('detailsModal').classList.add('hidden')">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Đóng</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-6 space-y-6 overflow-y-auto" id="detailsModalBody">
                <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-700"></div>
                </div>
            </div>
            <!-- Modal footer -->
            <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b dark:border-gray-600 shrink-0">
                <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600" onclick="document.getElementById('detailsModal').classList.add('hidden')">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('detailsModal');
        const modalBody = document.getElementById('detailsModalBody');

        document.querySelectorAll('.view-details-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');

                modal.classList.remove('hidden');
                modalBody.innerHTML = '<div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-700"></div></div>';

                let url = '{{ route("admin.inventory.details", ":id") }}'.replace(':id', id);

                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        modalBody.innerHTML = html;
                    })
                    .catch(error => {
                        modalBody.innerHTML = '<div class="text-red-500 text-center py-4">Có lỗi xảy ra khi tải dữ liệu.</div>';
                    });
            });
        });
    });
</script>
@endpush
