@php
    $level = $level ?? 0;
    $padding = $level * 1.5;
    $hasChildren = $category->children->count() > 0;
    $imageUrl = $category->image 
        ? (\Illuminate\Support\Str::startsWith($category->image, ['http://', 'https://']) ? $category->image : Storage::url($category->image))
        : null;
@endphp
<tr class="{{ $level > 0 ? 'hidden children-of-' . $category->parent_id : '' }} bg-white dark:bg-[#0a0a0a] hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors category-row" data-id="{{ $category->id }}" data-parent="{{ $category->parent_id }}">
    <td class="px-6 py-4 font-mono text-xs text-gray-500">#{{ $category->id }}</td>
    <td class="px-6 py-4 text-center">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $category->name }}" class="w-10 h-10 object-cover rounded-xl mx-auto ring-1 ring-black/5 dark:ring-white/10 shadow-sm">
        @else
            <div class="w-10 h-10 bg-gray-50 dark:bg-white/5 rounded-xl flex items-center justify-center mx-auto text-gray-400 ring-1 ring-black/5 dark:ring-white/10">
                <i class="bi bi-image text-lg"></i>
            </div>
        @endif
    </td>
    <td class="px-6 py-4 whitespace-nowrap" style="padding-left: {{ 1.5 + $padding }}rem !important;">
        <div class="flex items-center gap-2">
            @if($hasChildren)
                <button type="button" class="inline-flex items-center justify-center p-1 rounded hover:bg-gray-100 dark:hover:bg-white/10 text-gray-500 hover:text-blue-600 transition-colors btn-toggle-children focus:outline-none" data-id="{{ $category->id }}">
                    <i class="bi bi-folder2 text-blue-500 mr-1 text-lg"></i>
                    <i class="bi bi-chevron-down toggle-icon-{{ $category->id }} text-[10px] font-bold" style="transition: transform 0.2s;"></i>
                </button>
            @else
                <i class="bi bi-dash text-gray-300 dark:text-gray-600 mr-2 ml-4"></i>
            @endif
            <span class="font-medium text-sm text-gray-800 dark:text-gray-100">{{ $category->name }}</span>
        </div>
    </td>
    <td class="px-6 py-4">
        <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-medium font-mono bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300">
            {{ $category->slug }}
        </span>
    </td>
    <td class="px-6 py-4 text-center">
        @if($category->is_active)
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-500/20">
                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Hiển thị
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400 border border-gray-200 dark:border-white/10">
                <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50"></span> Đã ẩn
            </span>
        @endif
    </td>
    <td class="px-6 py-4 text-right">
        <div class="flex justify-end gap-2">
            <button type="button" 
                class="btn-edit-category w-8 h-8 rounded-xl inline-flex items-center justify-center bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-blue-600 hover:border-blue-200 focus:ring-4 focus:outline-none focus:ring-blue-50 dark:bg-[#111] dark:border-white/10 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-blue-400 transition-all shadow-sm"
                data-modal-target="editModal" 
                data-modal-toggle="editModal" 
                data-id="{{ $category->id }}"
                data-name="{{ $category->name }}"
                data-parent-id="{{ $category->parent_id ?? '' }}"
                data-is-active="{{ $category->is_active ? 1 : 0 }}"
                data-image="{{ $imageUrl ?? '' }}"
                data-descendants="{{ json_encode($category->getAllDescendantIdsAndSelf()) }}"
                title="Chỉnh sửa">
                <i class="bi bi-pencil-square text-sm"></i>
            </button>
            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="inline-block form-delete" data-confirm-title="Xóa danh mục này?" data-confirm-text="Các danh mục con (nếu có) sẽ trở thành danh mục gốc!">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-8 h-8 rounded-xl inline-flex items-center justify-center bg-white border border-gray-200 text-gray-600 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 focus:ring-4 focus:outline-none focus:ring-rose-50 dark:bg-[#111] dark:border-white/10 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-rose-400 transition-all shadow-sm" title="Xóa">
                    <i class="bi bi-trash text-sm"></i>
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
