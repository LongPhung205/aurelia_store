@props(['categories', 'level' => 0, 'selected' => []])

@if($categories && $categories->count() > 0)
    <ul class="{{ $level > 0 ? 'ml-4 border-l pl-3 border-gray-200 dark:border-gray-700 mt-2 space-y-2' : 'space-y-2' }}">
        @foreach($categories as $category)
            <li>
                <div class="flex items-center">
                    <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" 
                           id="cat_{{ $category->id }}"
                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                           {{ in_array($category->id, $selected) ? 'checked' : '' }}>
                    <label for="cat_{{ $category->id }}" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300 cursor-pointer">
                        {{ $category->name }}
                    </label>
                </div>
                
                <!-- Đệ quy cho các danh mục con -->
                @if($category->children && $category->children->count() > 0)
                    @include('admin.products.partials.category_checkbox', [
                        'categories' => $category->children,
                        'level' => $level + 1,
                        'selected' => $selected
                    ])
                @endif
            </li>
        @endforeach
    </ul>
@endif
