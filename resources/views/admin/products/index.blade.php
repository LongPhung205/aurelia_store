@extends('admin.layouts.admin')

@section('title', 'Danh sách Sản phẩm')

@section('content')
<div class="w-full h-[calc(100vh-90px)] flex flex-col">

    <x-admin.card noPadding="true" title="Tất cả sản phẩm" icon="bi bi-list-ul" class="flex-1 flex flex-col min-h-0" bodyClass="flex-1 flex flex-col min-h-0">
        <x-slot name="headerActions">
            <x-admin.button variant="primary" size="sm" data-modal-target="createProductModal" data-modal-toggle="createProductModal" icon="bi bi-plus-circle">
                Thêm Mới
            </x-admin.button>
        </x-slot>
        <div class="p-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shrink-0">
            <form action="{{ route('admin.products.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-3">
                    <x-admin.input name="search" label="Tìm kiếm" placeholder="Tên sản phẩm..." icon="bi bi-search" :value="request('search')" onchange="this.form.submit()" />
                </div>
                <div class="md:col-span-2 mb-4">
                    <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Danh mục</label>
                    <select name="category_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" onchange="this.form.submit()">
                        <option value="">-- Tất cả --</option>
                        @foreach($categories as $parent)
                            <option value="{{ $parent->id }}" class="font-semibold" {{ request('category_id') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                            @if($parent->children)
                                @foreach($parent->children as $child)
                                    <option value="{{ $child->id }}" {{ request('category_id') == $child->id ? 'selected' : '' }}>&nbsp;&nbsp;&nbsp;-- {{ $child->name }}</option>
                                    @if($child->children)
                                        @foreach($child->children as $grandchild)
                                            <option value="{{ $grandchild->id }}" {{ request('category_id') == $grandchild->id ? 'selected' : '' }}>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;---- {{ $grandchild->name }}</option>
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 mb-4">
                    <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Màu sắc</label>
                    <select name="color_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" onchange="this.form.submit()">
                        <option value="">-- Tất cả --</option>
                        @foreach($colors as $color)
                            <option value="{{ $color->id }}" {{ request('color_id') == $color->id ? 'selected' : '' }}>{{ $color->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 mb-4">
                    <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Kích cỡ</label>
                    <select name="size_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" onchange="this.form.submit()">
                        <option value="">-- Tất cả --</option>
                        @foreach($sizes as $size)
                            <option value="{{ $size->id }}" {{ request('size_id') == $size->id ? 'selected' : '' }}>{{ $size->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3 mb-4">
                    <x-admin.button href="{{ route('admin.products.index') }}" variant="danger" icon="bi bi-x-circle" class="w-full py-2.5">
                        Xóa lọc
                    </x-admin.button>
                </div>
            </form>
        </div>
    <div class="overflow-auto flex-1 relative shadow-inner">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 relative">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.1)]">
                <tr>
                    <th scope="col" class="px-6 py-3">ID</th>
                    <th scope="col" class="px-6 py-3">Sản phẩm</th>
                    <th scope="col" class="px-6 py-3">Danh mục</th>
                    <th scope="col" class="px-6 py-3 text-center">Biến thể</th>
                    <th scope="col" class="px-6 py-3 text-center">Trạng thái</th>
                    <th scope="col" class="px-6 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">#{{ $product->id }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded bg-gray-100 flex items-center justify-center overflow-hidden shrink-0">
                                    @php
                                        $thumbnail = null;
                                        if ($product->images->isNotEmpty()) {
                                            $thumbnail = $product->images->first()->image_url;
                                        } else {
                                            $variantWithThumb = $product->variants->whereNotNull('thumbnail_url')->first();
                                            if ($variantWithThumb) {
                                                $thumbnail = $variantWithThumb->thumbnail_url;
                                            }
                                        }
                                    @endphp
                                    
                                    @if($thumbnail)
                                        <img src="{{ asset('storage/' . $thumbnail) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="bi bi-image text-gray-400 text-xl"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $product->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $product->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($product->categories->count())
                                <div class="flex flex-wrap gap-1">
                                    @foreach($product->categories as $cat)
                                        <x-admin.badge variant="info" size="xs">{{ $cat->name }}</x-admin.badge>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <x-admin.badge variant="dark" rounded="true">{{ $product->variants->count() }}</x-admin.badge>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($product->status == 'active')
                                <x-admin.badge variant="success" rounded="true">Hoạt động</x-admin.badge>
                            @elseif($product->status == 'draft')
                                <x-admin.badge variant="warning" rounded="true">Bản nháp</x-admin.badge>
                            @else
                                <x-admin.badge variant="danger" rounded="true">Đã ẩn</x-admin.badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="text-blue-600 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 hover:bg-gray-100 dark:bg-gray-800 dark:text-blue-500 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline-block form-delete" data-confirm-title="Xóa sản phẩm?" data-confirm-text="Sản phẩm và toàn bộ biến thể sẽ bị xóa, không thể hoàn tác!">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 hover:bg-gray-100 dark:bg-gray-800 dark:text-red-500 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700 btn-delete-product">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="mb-3"><i class="bi bi-inbox text-4xl text-gray-400"></i></div>
                                <h6 class="text-lg font-medium mb-2">Chưa có sản phẩm nào!</h6>
                                <x-admin.button variant="primary" size="sm" data-modal-target="createProductModal" data-modal-toggle="createProductModal" icon="bi bi-plus">
                                    Thêm sản phẩm
                                </x-admin.button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <x-slot name="footer">
            @if($products->hasPages())
                <div class="mt-2">
                    {{ $products->links('pagination::tailwind') }}
                </div>
            @endif
        </x-slot>
    </x-admin.card>
</div>

<!-- Modal Thêm Sản Phẩm -->
<x-admin.modal id="createProductModal" title="Thêm Sản Phẩm Mới" size="xl" static="true">
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="createProductForm">
        @csrf
        @include('admin.products.partials.create_form')
    </form>
    
    <x-slot name="footer">
        <div class="flex justify-end w-full">
            <x-admin.button type="button" variant="secondary" class="mr-2" data-modal-hide="createProductModal">Hủy</x-admin.button>
            <x-admin.button type="submit" variant="primary" icon="bi bi-save" form="createProductForm">Lưu Sản Phẩm</x-admin.button>
        </div>
    </x-slot>
</x-admin.modal>
@endsection

@push('scripts')
    @include('admin.products.partials.create_scripts')

@endpush
