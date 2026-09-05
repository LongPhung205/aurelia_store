---
name: admin-fixed-table-layout
description: Use when building or refactoring admin index pages, data tables, or tabs to ensure the page doesn't scroll globally, but rather only the table or list scrolls within its container.
---

# Admin Fixed Table Layout

## Overview
This pattern ensures that admin pages (especially those with data tables or tabs) do not cause the entire browser window to scroll. Instead, the layout fills the viewport, headers/filters stay fixed, and only the data table body scrolls.

## When to Use
- When creating new admin management pages (`index.blade.php`).
- When refactoring existing admin pages to prevent dual scrollbars or lost headers.
- When working with `<x-admin.card>` and data tables.

## Core Pattern

### 1. Main Page Wrapper
The outermost container of the view content must be fixed height and use flexbox to fill the available space (accounting for the admin navbar height).

```blade
@section('content')
<div class="w-full h-[calc(100vh-90px)] flex flex-col">
    <!-- Inner content -->
</div>
@endsection
```

### 2. Card Component
When using `<x-admin.card>`, configure it to stretch and handle internal scrolling. Note the usage of `min-h-0` to prevent flex items from overflowing.

```blade
<x-admin.card 
    class="flex-1 flex flex-col min-h-0 border-0 shadow-sm" 
    bodyClass="flex-1 flex flex-col min-h-0" 
    noPadding="true"
>
```

### 3. Filters / Header (Fixed)
Elements that shouldn't scroll must be set to `shrink-0`.

```blade
<div class="p-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
    <!-- Search forms, filters, action buttons -->
</div>
```

### 4. Table Wrapper (Scrolling)
The wrapper around the `<table>` must take up the remaining space and handle overflow. The `<thead>` should be sticky.

```blade
<div class="overflow-auto flex-1 relative">
    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.1)] bg-gray-50 dark:bg-gray-700">
            <!-- Headers -->
        </thead>
        <tbody>
            <!-- Data -->
        </tbody>
    </table>
</div>
```

### 5. Pagination (Footer)
Move pagination to the footer slot so it stays fixed at the bottom of the card, visible regardless of scroll position.

```blade
<x-slot name="footer">
    <div class="mt-2 flex justify-end w-full">
        {{ $data->links('pagination::tailwind') }}
    </div>
</x-slot>
</x-admin.card>
```

## Modals with Tabs
When applying this to a modal that contains tabs (like Create/Edit forms):
1. Give the modal a fixed height class or `size="xl"` and `static="true"`.
2. Give the inner Tabs Content wrapper a fixed maximum height `overflow-y-auto max-h-[calc(100vh-320px)]`.
3. Keep the modal footer buttons outside the scrolling container (using the `<x-slot name="footer">` of the modal) so they are always accessible.

## Common Mistakes
- **Double Scrollbars**: Forgetting to set `min-h-0` on flex children can cause the container to expand beyond `100vh`, triggering the browser's global scrollbar.
- **Lost Table Headers**: Forgetting `sticky top-0 z-10` on the `<thead>` means headers scroll out of view when reading data.
