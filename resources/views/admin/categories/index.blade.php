@extends('admin.layouts.admin')

@section('title', 'Quản lý Danh mục')
@section('page_title', 'Danh mục Sản phẩm')

@section('content')
<div class="w-full h-[calc(100vh-90px)] flex flex-col">

    @if (isset($errors) && $errors->any())
        <div class="mb-4 p-4 text-sm text-red-800 rounded-xl bg-red-50 ring-1 ring-red-100 dark:bg-red-900/20 dark:text-red-400 dark:ring-red-900/30 shrink-0 shadow-sm" role="alert">
            <div class="font-bold mb-1 flex items-center gap-2"><i class="bi bi-exclamation-triangle-fill"></i> Đã có lỗi xảy ra:</div>
            <ul class="list-disc pl-8 font-medium">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-admin.card noPadding="true" title="Danh sách Danh mục" icon="bi bi-folder2-open" class="flex-1 flex flex-col min-h-0" bodyClass="flex-1 flex flex-col min-h-0">
        
        <!-- Header Actions -->
        <div class="p-4 border-b border-gray-100 dark:border-white/10 shrink-0 flex justify-between items-center bg-gray-50/50 dark:bg-[#0a0a0a]">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Quản lý cây danh mục đa cấp
            </div>
            <x-admin.button type="button" variant="primary" icon="bi bi-plus-lg" data-modal-target="createModal" data-modal-toggle="createModal" class="shadow-sm">
                Thêm Mới
            </x-admin.button>
        </div>
        
        <div class="overflow-auto flex-1 relative shadow-inner">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                <thead class="text-xs font-semibold text-gray-500 uppercase bg-gray-50 dark:bg-[#111] dark:text-gray-400 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.05)] border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th scope="col" class="px-6 py-4 w-20">ID</th>
                        <th scope="col" class="px-6 py-4 w-24 text-center">Ảnh</th>
                        <th scope="col" class="px-6 py-4">Tên danh mục</th>
                        <th scope="col" class="px-6 py-4 w-48">Đường dẫn (Slug)</th>
                        <th scope="col" class="px-6 py-4 w-32 text-center">Trạng thái</th>
                        <th scope="col" class="px-6 py-4 w-32 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($categories as $category)
                        @include('admin.categories.partials.category_row', ['category' => $category, 'level' => 0])
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Chưa có dữ liệu.</td>
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
        
        <x-admin.input name="name" label="Tên danh mục" required="true" placeholder="Nhập tên danh mục..." />
        
        <x-admin.select name="parent_id" label="Danh mục cha">
            <option value="">-- Không có (Danh mục gốc) --</option>
            @foreach($categoryTree as $parent)
                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
            @endforeach
        </x-admin.select>
        
        <div class="mb-4">
            <label class="block mb-2 text-sm font-semibold text-slate-700 dark:text-slate-300">Ảnh danh mục (Tùy chọn)</label>
            <input type="file" name="image" accept="image/*" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
        </div>
        
        <x-admin.select name="is_active" label="Trạng thái">
            <option value="1">Hiển thị</option>
            <option value="0">Ẩn</option>
        </x-admin.select>
        
        <div class="flex justify-end gap-2 mt-6">
            <x-admin.button type="button" variant="secondary" data-modal-hide="createModal">Hủy</x-admin.button>
            <x-admin.button type="submit" variant="primary" icon="bi bi-save">Lưu danh mục</x-admin.button>
        </div>
    </form>
</x-admin.modal>

<!-- Modal Chỉnh Sửa Danh Mục -->
<x-admin.modal id="editModal" title="Chỉnh sửa Danh Mục">
    <form id="editCategoryForm" action="" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <x-admin.input name="name" id="edit_name" label="Tên danh mục" required="true" placeholder="Nhập tên danh mục..." />
        
        <x-admin.select name="parent_id" id="edit_parent_id" label="Danh mục cha">
            <option value="">-- Không có (Danh mục gốc) --</option>
            @foreach($categoryTree as $parent)
                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
            @endforeach
        </x-admin.select>
        
        <div class="mb-4">
            <label class="block mb-2 text-sm font-semibold text-slate-700 dark:text-slate-300">Ảnh danh mục (Tùy chọn)</label>
            <div id="edit_image_preview_wrapper" class="mb-2 hidden">
                <img id="edit_image_preview" src="" alt="Current Image" class="h-20 w-20 object-cover rounded border border-gray-200">
            </div>
            <input type="file" name="image" accept="image/*" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            <p class="mt-1 text-xs text-gray-500">Để trống nếu không muốn thay đổi ảnh.</p>
        </div>
        
        <x-admin.select name="is_active" id="edit_is_active" label="Trạng thái">
            <option value="1">Hiển thị</option>
            <option value="0">Ẩn</option>
        </x-admin.select>
        
        <div class="flex justify-end gap-2 mt-6">
            <x-admin.button type="button" variant="secondary" data-modal-hide="editModal">Hủy</x-admin.button>
            <x-admin.button type="submit" variant="primary" icon="bi bi-save">Cập nhật</x-admin.button>
        </div>
    </form>
</x-admin.modal>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle category rows (children accordion)
        document.querySelectorAll('.btn-toggle-children').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const parentId = this.dataset.id;
                const children = document.querySelectorAll('.children-of-' + parentId);
                const icon = document.querySelector('.toggle-icon-' + parentId);
                
                if (children.length === 0) return;
                
                let isExpanded = !children[0].classList.contains('hidden');
                
                if (isExpanded) {
                    children.forEach(child => child.classList.add('hidden'));
                    if (icon) icon.style.transform = 'rotate(0deg)';
                    
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
                    children.forEach(child => child.classList.remove('hidden'));
                    if (icon) icon.style.transform = 'rotate(-180deg)';
                }
            });
        });

        // Edit Category Modal Binding
        const editForm = document.getElementById('editCategoryForm');
        const editName = document.getElementById('edit_name');
        const editParent = document.getElementById('edit_parent_id');
        const editIsActive = document.getElementById('edit_is_active');
        const editImgWrapper = document.getElementById('edit_image_preview_wrapper');
        const editImg = document.getElementById('edit_image_preview');

        document.querySelectorAll('.btn-edit-category').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const parentId = this.dataset.parentId;
                const isActive = this.dataset.isActive;
                const image = this.dataset.image;
                let descendants = [];
                try {
                    descendants = JSON.parse(this.dataset.descendants || '[]');
                } catch (e) {
                    descendants = [];
                }

                // Form action URL
                editForm.action = '{{ url("admin/categories") }}/' + id;

                // Inputs
                if (editName) editName.value = name || '';
                if (editIsActive) editIsActive.value = (isActive !== undefined && isActive !== '') ? isActive : '1';

                // Image preview
                if (image) {
                    editImg.src = image;
                    editImgWrapper.classList.remove('hidden');
                } else {
                    editImg.src = '';
                    editImgWrapper.classList.add('hidden');
                }

                // Filter options: hide/disable self and all descendants to prevent invalid cycles
                if (editParent) {
                    Array.from(editParent.options).forEach(opt => {
                        if (!opt.value) {
                            opt.disabled = false;
                            opt.hidden = false;
                            return;
                        }
                        const optId = parseInt(opt.value);
                        if (descendants.includes(optId)) {
                            opt.disabled = true;
                            opt.hidden = true;
                        } else {
                            opt.disabled = false;
                            opt.hidden = false;
                        }
                    });

                    // Set parent category
                    editParent.value = parentId || '';
                }
            });
        });
    });
</script>
@endpush
