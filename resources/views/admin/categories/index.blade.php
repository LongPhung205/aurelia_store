@extends('admin.layouts.admin')

@section('title', 'Quản lý Danh mục')

@section('content')
<div class="px-0 w-full h-[calc(100vh-90px)] flex flex-col">
    <!-- Header & Breadcrumb -->
    <div class="flex justify-between items-center mb-6 shrink-0">
        <div>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Quản lý Danh Mục</h4>
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="#" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                            Bảng điều khiển
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="bi bi-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Danh mục</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <x-admin.card class="flex-1 flex flex-col min-h-0 border-0 shadow-sm" bodyClass="flex-1 flex flex-col min-h-0" noPadding="true">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 shrink-0 flex justify-between items-center bg-white dark:bg-gray-800 rounded-t-lg">
        <h5 class="mb-0 font-semibold text-blue-600 dark:text-blue-400"><i class="bi bi-folder2 mr-2"></i>Tất cả danh mục</h5>
        <x-admin.button type="button" variant="primary" size="sm" icon="bi bi-plus-circle" data-modal-target="createModal" data-modal-toggle="createModal">
            Thêm Mới
        </x-admin.button>
    </div>
    
    <div class="overflow-auto flex-1 relative">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.1)]">
                <tr>
                    <th scope="col" class="px-6 py-3 font-semibold">ID</th>
                    <th scope="col" class="px-6 py-3 font-semibold text-center">Ảnh</th>
                    <th scope="col" class="px-6 py-3 font-semibold">Tên danh mục</th>
                    <th scope="col" class="px-6 py-3 font-semibold">Đường dẫn (Slug)</th>
                    <th scope="col" class="px-6 py-3 font-semibold text-center">Trạng thái</th>
                    <th scope="col" class="px-6 py-3 font-semibold text-right">Thao tác</th>
                </tr>
            </thead>
                <tbody>
                    @forelse($categories as $category)
                        @include('admin.categories.partials.category_row', ['category' => $category, 'level' => 0])
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Chưa có dữ liệu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
    </div>
    @if($categories->hasPages())
        <x-slot name="footer">
            <div class="mt-2 flex justify-end w-full">
                {{ $categories->links('pagination::tailwind') }}
            </div>
        </x-slot>
    @endif
    </x-admin.card>
</div>

<!-- Modal Thêm Danh Mục -->
<x-admin.modal id="createModal" title="Thêm Danh Mục Mới">
    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="mb-4">
            <x-admin.input name="name" label="Tên danh mục" required="true" />
        </div>
        
        <div class="mb-4">
            <x-admin.select name="parent_id" label="Danh mục cha">
                <option value="">-- Không có (Thư mục gốc) --</option>
                @foreach(\App\Models\Category::all() as $parent)
                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                @endforeach
            </x-admin.select>
        </div>
        
        <div class="mb-4">
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Ảnh danh mục (Tùy chọn)</label>
            <input type="file" name="image" accept="image/*" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
        </div>
        
        <div class="mb-4">
            <x-admin.select name="is_active" label="Trạng thái">
                <option value="1">Hiển thị</option>
                <option value="0">Ẩn</option>
            </x-admin.select>
        </div>
        
        <div class="flex justify-end gap-2 mt-6">
            <x-admin.button type="button" variant="secondary" data-modal-hide="createModal">Hủy</x-admin.button>
            <x-admin.button type="submit" variant="primary" icon="bi bi-save">Lưu danh mục</x-admin.button>
        </div>
    </form>
</x-admin.modal>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-toggle-children').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const parentId = this.dataset.id;
                const children = document.querySelectorAll('.children-of-' + parentId);
                const icon = document.querySelector('.toggle-icon-' + parentId);
                
                let isExpanded = !children[0].classList.contains('hidden');
                
                if (isExpanded) {
                    // Cần ẩn đi (collapse)
                    children.forEach(child => child.classList.add('hidden'));
                    icon.style.transform = 'rotate(0deg)';
                    
                    // Ẩn luôn tất cả con cháu bên trong nếu chúng đang mở
                    const hideDescendants = (id) => {
                        const desc = document.querySelectorAll('.children-of-' + id);
                        desc.forEach(d => {
                            d.classList.add('hidden');
                            const childIcon = document.querySelector('.toggle-icon-' + d.dataset.id);
                            if (childIcon) childIcon.style.transform = 'rotate(0deg)';
                            hideDescendants(d.dataset.id);
                        });
                    };
                    hideDescendants(parentId);
                } else {
                    // Cần hiện ra (bỏ collapse)
                    children.forEach(child => child.classList.remove('hidden'));
                    icon.style.transform = 'rotate(-180deg)';
                }
            });
        });
    });
</script>
@endpush
