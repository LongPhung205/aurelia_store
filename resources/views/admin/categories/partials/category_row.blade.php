@php
    $level = $level ?? 0;
    $padding = $level * 1.5;
    $hasChildren = $category->children->count() > 0;
@endphp
<tr class="{{ $level > 0 ? 'hidden children-of-' . $category->parent_id : '' }} bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 category-row" data-id="{{ $category->id }}" data-parent="{{ $category->parent_id }}">
    <td class="px-6 py-4 font-mono text-xs text-gray-500 dark:text-gray-400">#{{ $category->id }}</td>
    <td class="px-6 py-4 text-center">
        @if($category->image)
            <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="w-10 h-10 object-cover rounded mx-auto border border-gray-200">
        @else
            <div class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center mx-auto text-gray-400 border border-gray-200">
                <i class="bi bi-image"></i>
            </div>
        @endif
    </td>
    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white" style="padding-left: {{ 1.5 + $padding }}rem !important;">
        @if($hasChildren)
            <button class="inline-flex items-center justify-center p-0 mr-1 text-gray-900 bg-transparent hover:text-blue-600 dark:text-white dark:hover:text-blue-500 btn-toggle-children focus:outline-none" data-id="{{ $category->id }}">
                <i class="bi bi-folder2-open text-yellow-500 mr-1 text-lg"></i>
                <i class="bi bi-chevron-down ml-1 toggle-icon-{{ $category->id }} text-xs" style="transition: transform 0.2s;"></i>
            </button>
        @else
            <i class="bi bi-file-earmark text-gray-400 mr-2 ml-4 text-lg"></i>
        @endif
        {{ $category->name }}
    </td>
    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $category->slug }}</td>
    <td class="px-6 py-4 text-center">
        @if($category->is_active)
            <x-admin.badge variant="success" rounded="true">Hiển thị</x-admin.badge>
        @else
            <x-admin.badge variant="danger" rounded="true">Đã ẩn</x-admin.badge>
        @endif
    </td>
    <td class="px-6 py-4 text-right">
        <div class="flex justify-end gap-2">
            <button type="button" class="text-blue-600 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 hover:bg-gray-100 dark:bg-gray-800 dark:text-blue-500 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700" data-modal-target="editModal{{ $category->id }}" data-modal-toggle="editModal{{ $category->id }}" title="Chỉnh sửa">
                <i class="bi bi-pencil-square"></i>
            </button>
            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="inline-block">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 hover:bg-gray-100 dark:bg-gray-800 dark:text-red-500 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700" onclick="return confirm('Xóa danh mục này?')">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>

<!-- Modal Edit cho từng danh mục -->
<x-admin.modal id="editModal{{ $category->id }}" title="Chỉnh sửa Danh Mục">
    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="mb-4">
            <x-admin.input name="name" label="Tên danh mục" value="{{ $category->name }}" required="true" />
        </div>
        
        <div class="mb-4">
            <x-admin.select name="parent_id" label="Danh mục cha">
                <option value="">-- Không có --</option>
                @foreach(\App\Models\Category::whereNull('parent_id')->where('id', '!=', $category->id)->get() as $parent)
                    <option value="{{ $parent->id }}" {{ $category->parent_id == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                @endforeach
            </x-admin.select>
        </div>
        
        <div class="mb-4">
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Ảnh danh mục (Tùy chọn)</label>
            @if($category->image)
                <div class="mb-2">
                    <img src="{{ Storage::url($category->image) }}" alt="Current Image" class="h-20 w-20 object-cover rounded border border-gray-200">
                </div>
            @endif
            <input type="file" name="image" accept="image/*" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            <p class="mt-1 text-xs text-gray-500">Để trống nếu không muốn thay đổi ảnh.</p>
        </div>
        
        <div class="mb-4">
            <x-admin.select name="is_active" label="Trạng thái">
                <option value="1" {{ $category->is_active ? 'selected' : '' }}>Hiển thị</option>
                <option value="0" {{ !$category->is_active ? 'selected' : '' }}>Ẩn</option>
            </x-admin.select>
        </div>
        
        <div class="flex justify-end gap-2 mt-6">
            <x-admin.button type="button" variant="secondary" data-modal-hide="editModal{{ $category->id }}">Hủy</x-admin.button>
            <x-admin.button type="submit" variant="primary" icon="bi bi-save">Cập nhật</x-admin.button>
        </div>
    </form>
</x-admin.modal>

@if($hasChildren)
    @foreach($category->children as $child)
        @include('admin.categories.partials.category_row', ['category' => $child, 'level' => $level + 1])
    @endforeach
@endif
