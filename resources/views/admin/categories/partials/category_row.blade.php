@php
    $level = $level ?? 0;
    $padding = $level * 1.5;
    $hasChildren = $category->children->count() > 0;
    $imageUrl = $category->image 
        ? (\Illuminate\Support\Str::startsWith($category->image, ['http://', 'https://']) ? $category->image : Storage::url($category->image))
        : null;
@endphp
<tr class="{{ $level > 0 ? 'hidden children-of-' . $category->parent_id : '' }} bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 category-row" data-id="{{ $category->id }}" data-parent="{{ $category->parent_id }}">
    <td class="px-6 py-4 font-mono text-xs text-gray-500 dark:text-gray-400">#{{ $category->id }}</td>
    <td class="px-6 py-4 text-center">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $category->name }}" class="w-10 h-10 object-cover rounded mx-auto border border-gray-200">
        @else
            <div class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center mx-auto text-gray-400 border border-gray-200">
                <i class="bi bi-image"></i>
            </div>
        @endif
    </td>
    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white" style="padding-left: {{ 1.5 + $padding }}rem !important;">
        @if($hasChildren)
            <button type="button" class="inline-flex items-center justify-center p-0 mr-1 text-gray-900 bg-transparent hover:text-blue-600 dark:text-white dark:hover:text-blue-500 btn-toggle-children focus:outline-none" data-id="{{ $category->id }}">
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
            <button type="button" 
                class="btn-edit-category text-blue-600 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 hover:bg-gray-100 dark:bg-gray-800 dark:text-blue-500 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700" 
                data-modal-target="editModal" 
                data-modal-toggle="editModal" 
                data-id="{{ $category->id }}"
                data-name="{{ $category->name }}"
                data-parent-id="{{ $category->parent_id ?? '' }}"
                data-is-active="{{ $category->is_active ? 1 : 0 }}"
                data-image="{{ $imageUrl ?? '' }}"
                data-descendants="{{ json_encode($category->getAllDescendantIdsAndSelf()) }}"
                title="Chỉnh sửa">
                <i class="bi bi-pencil-square"></i>
            </button>
            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="inline-block form-delete" data-confirm-title="Xóa danh mục này?" data-confirm-text="Các danh mục con (nếu có) sẽ trở thành danh mục gốc!">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 hover:bg-gray-100 dark:bg-gray-800 dark:text-red-500 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>

@if($hasChildren)
    @foreach($category->children as $child)
        @include('admin.categories.partials.category_row', ['category' => $child, 'level' => $level + 1])
    @endforeach
@endif
