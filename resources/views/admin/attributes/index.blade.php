@extends('admin.layouts.admin')

@section('title', 'Quản lý Thuộc tính')

@section('content')
<div class="px-0 w-full h-[calc(100vh-90px)] flex flex-col">

    <div class="flex-1 flex flex-col min-h-0">
        <!-- Tabs Nav -->
        <!-- Tabs Nav -->
        <div class="mb-4 shrink-0 px-1">
            <ul class="flex flex-wrap gap-2 text-sm font-medium" id="attributeTabs" data-tabs-toggle="#attributeTabsContent" role="tablist">
                <li role="presentation">
                    <button class="inline-flex items-center px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all {{ $activeTab === 'colors' ? 'bg-white dark:bg-[#0a0a0a] text-blue-600 shadow-sm ring-1 ring-black/5 dark:ring-white/10' : 'text-gray-500 hover:bg-white/50 dark:hover:bg-white/5' }}" id="colors-tab" data-tabs-target="#colors-pane" type="button" role="tab" aria-selected="{{ $activeTab === 'colors' ? 'true' : 'false' }}">
                        <i class="bi bi-palette mr-2 text-lg"></i>Màu sắc
                    </button>
                </li>
                <li role="presentation">
                    <button class="inline-flex items-center px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all {{ $activeTab === 'sizes' ? 'bg-white dark:bg-[#0a0a0a] text-blue-600 shadow-sm ring-1 ring-black/5 dark:ring-white/10' : 'text-gray-500 hover:bg-white/50 dark:hover:bg-white/5' }}" id="sizes-tab" data-tabs-target="#sizes-pane" type="button" role="tab" aria-selected="{{ $activeTab === 'sizes' ? 'true' : 'false' }}">
                        <i class="bi bi-rulers mr-2 text-lg"></i>Kích cỡ
                    </button>
                </li>
                <li role="presentation">
                    <button class="inline-flex items-center px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all {{ $activeTab === 'materials' ? 'bg-white dark:bg-[#0a0a0a] text-blue-600 shadow-sm ring-1 ring-black/5 dark:ring-white/10' : 'text-gray-500 hover:bg-white/50 dark:hover:bg-white/5' }}" id="materials-tab" data-tabs-target="#materials-pane" type="button" role="tab" aria-selected="{{ $activeTab === 'materials' ? 'true' : 'false' }}">
                        <i class="bi bi-layers mr-2 text-lg"></i>Chất liệu
                    </button>
                </li>
            </ul>
        </div>

        <!-- Tabs Content -->
        <div id="attributeTabsContent" class="flex-1 min-h-0 relative">
            
            <!-- COLORS TAB -->
            <div class="{{ $activeTab === 'colors' ? '' : 'hidden' }} h-full p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="colors-pane" role="tabpanel" aria-labelledby="colors-tab">
                <x-admin.card class="h-full flex flex-col border-0 shadow-sm" bodyClass="flex-1 flex flex-col min-h-0" noPadding="true">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 shrink-0 flex justify-between items-center bg-white dark:bg-gray-800 rounded-t-lg">
                        <h5 class="mb-0 font-semibold text-blue-600 dark:text-blue-400">Tất cả màu sắc</h5>
                        <x-admin.button type="button" variant="primary" size="sm" icon="bi bi-plus-circle" data-modal-target="createColorModal" data-modal-toggle="createColorModal">
                            Thêm Màu Mới
                        </x-admin.button>
                    </div>
                    
                    <div class="overflow-auto flex-1 relative">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-[#111] dark:text-gray-400 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.05)] border-b border-gray-200 dark:border-white/10">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 font-semibold w-20">ID</th>
                                        <th scope="col" class="px-6 py-4 font-semibold">Tên màu</th>
                                        <th scope="col" class="px-6 py-4 font-semibold w-48">Mã HEX</th>
                                        <th scope="col" class="px-6 py-4 font-semibold w-28 text-right">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                    @forelse($colors as $color)
                                        <tr class="bg-white dark:bg-[#0a0a0a] hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                                            <td class="px-6 py-4 font-mono text-xs text-gray-500">#{{ $color->id }}</td>
                                            <td class="px-6 py-4 font-medium text-sm text-gray-800 whitespace-nowrap dark:text-gray-200">{{ $color->name }}</td>
                                            <td class="px-6 py-4">
                                                @if($color->hex_code)
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-6 h-6 rounded-full shadow-sm ring-1 ring-black/10 dark:ring-white/20" style="background-color: {{ $color->hex_code }}"></div>
                                                        <span class="text-xs font-mono font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $color->hex_code }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <form action="{{ route('admin.colors.destroy', $color->id) }}" method="POST" class="inline-block form-delete"
                                                      data-confirm-title="Xóa màu sắc?"
                                                      data-confirm-text="Bạn có chắc chắn muốn xóa màu &quot;{{ $color->name }}&quot; không?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-8 h-8 rounded-xl inline-flex items-center justify-center bg-white border border-gray-200 text-gray-600 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 focus:ring-4 focus:outline-none focus:ring-rose-50 dark:bg-[#111] dark:border-white/10 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-rose-400 transition-all shadow-sm" title="Xóa màu">
                                                        <i class="bi bi-trash text-sm"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Chưa có dữ liệu màu sắc.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                        </table>
                    </div>
                    @if($colors->hasPages())
                        <x-slot name="footer">
                            <div class="mt-2 flex justify-end w-full">
                                {{ $colors->links('pagination::tailwind') }}
                            </div>
                        </x-slot>
                    @endif
                </x-admin.card>
            </div>

            <!-- SIZES TAB -->
            <div class="{{ $activeTab === 'sizes' ? '' : 'hidden' }} h-full p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="sizes-pane" role="tabpanel" aria-labelledby="sizes-tab">
                <x-admin.card class="h-full flex flex-col border-0 shadow-sm" bodyClass="flex-1 flex flex-col min-h-0" noPadding="true">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 shrink-0 flex justify-between items-center bg-white dark:bg-gray-800 rounded-t-lg">
                        <h5 class="mb-0 font-semibold text-blue-600 dark:text-blue-400">Tất cả kích cỡ</h5>
                        <x-admin.button type="button" variant="primary" size="sm" icon="bi bi-plus-circle" data-modal-target="createSizeModal" data-modal-toggle="createSizeModal">
                            Thêm Kích Cỡ Mới
                        </x-admin.button>
                    </div>
                    
                    <div class="overflow-auto flex-1 relative">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-[#111] dark:text-gray-400 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.05)] border-b border-gray-200 dark:border-white/10">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 font-semibold w-20">ID</th>
                                        <th scope="col" class="px-6 py-4 font-semibold">Ký hiệu (Size)</th>
                                        <th scope="col" class="px-6 py-4 font-semibold w-28 text-right">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                    @forelse($sizes as $size)
                                        <tr class="bg-white dark:bg-[#0a0a0a] hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                                            <td class="px-6 py-4 font-mono text-xs text-gray-500">#{{ $size->id }}</td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center justify-center min-w-[2.5rem] px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-gray-200 uppercase tracking-wide">
                                                    {{ $size->name }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <form action="{{ route('admin.sizes.destroy', $size->id) }}" method="POST" class="inline-block form-delete"
                                                      data-confirm-title="Xóa kích cỡ?"
                                                      data-confirm-text="Bạn có chắc chắn muốn xóa kích cỡ &quot;{{ $size->name }}&quot; không?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-8 h-8 rounded-xl inline-flex items-center justify-center bg-white border border-gray-200 text-gray-600 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 focus:ring-4 focus:outline-none focus:ring-rose-50 dark:bg-[#111] dark:border-white/10 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-rose-400 transition-all shadow-sm" title="Xóa kích cỡ">
                                                        <i class="bi bi-trash text-sm"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Chưa có dữ liệu kích cỡ.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                        </table>
                    </div>
                    @if($sizes->hasPages())
                        <x-slot name="footer">
                            <div class="mt-2 flex justify-end w-full">
                                {{ $sizes->links('pagination::tailwind') }}
                            </div>
                        </x-slot>
                    @endif
                </x-admin.card>
            </div>

            <!-- MATERIALS TAB -->
            <div class="{{ $activeTab === 'materials' ? '' : 'hidden' }} h-full p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="materials-pane" role="tabpanel" aria-labelledby="materials-tab">
                <x-admin.card class="h-full flex flex-col border-0 shadow-sm" bodyClass="flex-1 flex flex-col min-h-0" noPadding="true">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 shrink-0 flex justify-between items-center bg-white dark:bg-gray-800 rounded-t-lg">
                        <h5 class="mb-0 font-semibold text-blue-600 dark:text-blue-400">Tất cả chất liệu</h5>
                        <x-admin.button type="button" variant="primary" size="sm" icon="bi bi-plus-circle" data-modal-target="createMaterialModal" data-modal-toggle="createMaterialModal">
                            Thêm Chất Liệu Mới
                        </x-admin.button>
                    </div>
                    
                    <div class="overflow-auto flex-1 relative">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-[#111] dark:text-gray-400 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.05)] border-b border-gray-200 dark:border-white/10">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 font-semibold w-20">ID</th>
                                        <th scope="col" class="px-6 py-4 font-semibold">Tên chất liệu</th>
                                        <th scope="col" class="px-6 py-4 font-semibold w-28 text-right">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                    @forelse($materials as $material)
                                        <tr class="bg-white dark:bg-[#0a0a0a] hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                                            <td class="px-6 py-4 font-mono text-xs text-gray-500">#{{ $material->id }}</td>
                                            <td class="px-6 py-4 font-medium text-sm text-gray-800 whitespace-nowrap dark:text-gray-200">{{ $material->name }}</td>
                                            <td class="px-6 py-4 text-right">
                                                <form action="{{ route('admin.materials.destroy', $material->id) }}" method="POST" class="inline-block form-delete"
                                                      data-confirm-title="Xóa chất liệu?"
                                                      data-confirm-text="Bạn có chắc chắn muốn xóa chất liệu &quot;{{ $material->name }}&quot; không?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-8 h-8 rounded-xl inline-flex items-center justify-center bg-white border border-gray-200 text-gray-600 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 focus:ring-4 focus:outline-none focus:ring-rose-50 dark:bg-[#111] dark:border-white/10 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-rose-400 transition-all shadow-sm" title="Xóa chất liệu">
                                                        <i class="bi bi-trash text-sm"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Chưa có dữ liệu chất liệu.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                        </table>
                    </div>
                    @if($materials->hasPages())
                        <x-slot name="footer">
                            <div class="mt-2 flex justify-end w-full">
                                {{ $materials->links('pagination::tailwind') }}
                            </div>
                        </x-slot>
                    @endif
                </x-admin.card>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create Color -->
<x-admin.modal id="createColorModal" title="Thêm Màu Mới">
    <form action="{{ route('admin.colors.store') }}" method="POST">
        @csrf
        
        <div class="mb-4">
            <x-admin.input name="name" label="Tên màu" placeholder="VD: Đỏ" required="true" />
        </div>
        
        <div class="mb-4">
            <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Mã HEX (Tùy chọn)</label>
            <input type="color" name="hex_code" class="h-10 w-full cursor-pointer rounded-lg border border-gray-300 bg-gray-50 p-1 dark:border-gray-600 dark:bg-gray-700" value="#000000" title="Chọn màu">
        </div>
        
        <div class="flex justify-end gap-2 mt-6">
            <x-admin.button type="button" variant="secondary" data-modal-hide="createColorModal">Hủy</x-admin.button>
            <x-admin.button type="submit" variant="primary">Lưu màu</x-admin.button>
        </div>
    </form>
</x-admin.modal>

<!-- Modal Create Size -->
<x-admin.modal id="createSizeModal" title="Thêm Kích cỡ Mới">
    <form action="{{ route('admin.sizes.store') }}" method="POST">
        @csrf
        
        <div class="mb-4">
            <x-admin.input name="name" label="Ký hiệu" placeholder="VD: S, M, L..." required="true" />
        </div>
        

        
        <div class="flex justify-end gap-2 mt-6">
            <x-admin.button type="button" variant="secondary" data-modal-hide="createSizeModal">Hủy</x-admin.button>
            <x-admin.button type="submit" variant="primary">Lưu kích cỡ</x-admin.button>
        </div>
    </form>
</x-admin.modal>

<!-- Modal Create Material -->
<x-admin.modal id="createMaterialModal" title="Thêm Chất Liệu Mới">
    <form action="{{ route('admin.materials.store') }}" method="POST">
        @csrf
        
        <div class="mb-4">
            <x-admin.input name="name" label="Tên chất liệu" placeholder="VD: Lụa tơ tằm" required="true" />
        </div>
        

        
        <div class="flex justify-end gap-2 mt-6">
            <x-admin.button type="button" variant="secondary" data-modal-hide="createMaterialModal">Hủy</x-admin.button>
            <x-admin.button type="submit" variant="primary">Lưu chất liệu</x-admin.button>
        </div>
    </form>
</x-admin.modal>
@endsection
