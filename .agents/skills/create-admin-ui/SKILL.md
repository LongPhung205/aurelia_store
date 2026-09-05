---
name: create-admin-ui
description: Use when building or modifying Admin Panel views (create, edit, index pages) in the Aurelia Store project to ensure consistent styling using custom Blade components.
---

# Create Admin UI (Aurelia Store)

## Overview
When building or refactoring Admin Panel views (e.g. `create.blade.php`, `edit.blade.php`), you MUST use the project's native Blade components (`<x-admin.*>`) instead of writing raw HTML or using raw TailwindCSS/DaisyUI classes. This ensures a 100% consistent, premium look across the entire dashboard.

## When to Use
- When creating a new module's admin views (e.g., `admin/posts/create.blade.php`).
- When modifying existing admin forms to make them look better.
- When the user asks to "làm đẹp giao diện admin" or "sửa lại giao diện".

## Core Pattern: Admin Form Layout

Always structure your `create` and `edit` forms using the standard 2-column layout (Main content left, Sidebar right) with a standard Header.

### 1. Standard Header
Always include a header with a title, a "Back" link, and a Primary Submit Button.

```html
<div class="flex justify-between items-center mb-6 shrink-0">
    <div>
        <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Thêm mới XYZ</h4>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.models.index') }}" class="text-blue-600 dark:text-blue-500 hover:underline font-medium text-sm">
            <i class="bi bi-arrow-left mr-1"></i> Quay lại danh sách
        </a>
        <button type="submit" form="main-form" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 transition-colors shadow-sm">
            <i class="bi bi-save mr-2"></i>Lưu thay đổi
        </button>
    </div>
</div>
```

### 2. Available Components

You MUST use these instead of standard `<input>`, `<textarea>`, `<select>`, or `<div class="card">`.

**Card Container:**
```html
<x-admin.card class="mb-4" title="Tiêu đề card (Tùy chọn)">
    <!-- Nội dung form -->
</x-admin.card>
```

**Text Input:**
```html
<x-admin.input name="title" label="Tiêu đề" placeholder="Nhập tiêu đề..." required="true" value="{{ old('title', $model->title ?? '') }}" />
```

**Textarea:**
```html
<x-admin.textarea name="content" label="Nội dung" required="true" rows="5">
    {{ old('content', $model->content ?? '') }}
</x-admin.textarea>
```

**Select Dropdown:**
```html
<x-admin.select name="is_active" label="Trạng thái" required="true">
    <option value="1">Công khai</option>
    <option value="0">Ẩn</option>
</x-admin.select>
```

## Quick Reference: 2-Column Grid Structure

Wrap your `<x-admin.card>` components in this grid layout:

```html
<form id="main-form" action="..." method="POST">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Cột chính (2/3 chiều rộng) -->
        <div class="lg:col-span-2 space-y-6">
            <x-admin.card class="mb-4">
                <x-admin.input name="name" label="Tên" required="true" />
                <x-admin.textarea name="description" label="Mô tả" />
            </x-admin.card>
        </div>

        <!-- Cột phụ Sidebar (1/3 chiều rộng) -->
        <div class="lg:col-span-1 space-y-6">
            <x-admin.card class="mb-4 bg-gray-50 dark:bg-gray-800">
                <x-admin.select name="status" label="Trạng thái">
                   <!-- ... -->
                </x-admin.select>
            </x-admin.card>
        </div>
        
    </div>
</form>
```

## Quick Reference: Index Page Layout

For list pages (e.g., `index.blade.php`), use a full-height layout with a sticky header table.

```html
<div class="px-0 w-full h-[calc(100vh-90px)] flex flex-col">
    <!-- Removed Header & Breadcrumb for a cleaner look -->

    <x-admin.card class="flex-1 flex flex-col min-h-0 border-0 shadow-sm" bodyClass="flex-1 flex flex-col min-h-0" noPadding="true">
        <!-- Toolbar -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 shrink-0 flex justify-between items-center bg-white dark:bg-gray-800 rounded-t-lg">
            <h5 class="mb-0 font-semibold text-blue-600 dark:text-blue-400"><i class="bi bi-list mr-2"></i>Tất cả mục</h5>
            <a href="{{ route('admin.models.create') }}">
                <x-admin.button type="button" variant="primary" size="sm" icon="bi bi-plus-circle">Thêm mới</x-admin.button>
            </a>
        </div>
        
        <!-- Table -->
        <div class="overflow-auto flex-1 relative">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.1)]">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">Tên cột</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dữ liệu -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($items->hasPages())
        <x-slot name="footer">
            <div class="mt-2 flex justify-end w-full">
                {{ $items->links('pagination::tailwind') }}
            </div>
        </x-slot>
        @endif
    </x-admin.card>
</div>
```

## Common Mistakes
- **Mistake:** Using `<input class="form-control">` or DaisyUI `<input class="input input-bordered">`.
  **Fix:** Always use `<x-admin.input name="..." label="..." />`! It automatically handles labels, borders, dark mode, and error messages.
- **Mistake:** Forgetting to link the header Save button to the form.
  **Fix:** Give the `<form>` an `id` (e.g. `id="main-form"`) and add `form="main-form"` to the submit `<button>` in the header.
