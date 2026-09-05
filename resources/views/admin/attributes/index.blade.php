@extends('admin.layouts.admin')

@section('title', 'Quản lý Thuộc tính')

@section('content')
<div class="px-0 w-full h-[calc(100vh-90px)] flex flex-col">

    <div class="flex-1 flex flex-col min-h-0">
        <!-- Tabs Nav -->
        <div class="border-b border-gray-200 dark:border-gray-700 mb-4 shrink-0">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="attributeTabs" data-tabs-toggle="#attributeTabsContent" role="tablist">
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'colors' ? 'border-blue-600 text-blue-600 dark:text-blue-500 dark:border-blue-500' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' }}" id="colors-tab" data-tabs-target="#colors-pane" type="button" role="tab" aria-selected="{{ $activeTab === 'colors' ? 'true' : 'false' }}">
                        <i class="bi bi-palette mr-2"></i>Màu sắc
                    </button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'sizes' ? 'border-blue-600 text-blue-600 dark:text-blue-500 dark:border-blue-500' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' }}" id="sizes-tab" data-tabs-target="#sizes-pane" type="button" role="tab" aria-selected="{{ $activeTab === 'sizes' ? 'true' : 'false' }}">
                        <i class="bi bi-rulers mr-2"></i>Kích cỡ
                    </button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'materials' ? 'border-blue-600 text-blue-600 dark:text-blue-500 dark:border-blue-500' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' }}" id="materials-tab" data-tabs-target="#materials-pane" type="button" role="tab" aria-selected="{{ $activeTab === 'materials' ? 'true' : 'false' }}">
                        <i class="bi bi-layers mr-2"></i>Chất liệu
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
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.1)]">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 font-semibold">ID</th>
                                        <th scope="col" class="px-6 py-3 font-semibold">Tên màu</th>
                                        <th scope="col" class="px-6 py-3 font-semibold">Mã HEX</th>
                                        <th scope="col" class="px-6 py-3 font-semibold text-right">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($colors as $color)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            <td class="px-6 py-4 font-mono text-xs text-gray-500 dark:text-gray-400">#{{ $color->id }}</td>
                                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $color->name }}</td>
                                            <td class="px-6 py-4">
                                                @if($color->hex_code)
                                                    <div class="flex items-center">
                                                        <div class="w-5 h-5 rounded-full border border-gray-300 dark:border-gray-600 mr-2" style="background-color: {{ $color->hex_code }}"></div>
                                                        <span class="text-gray-900 dark:text-gray-300">{{ $color->hex_code }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <form action="{{ route('admin.colors.destroy', $color->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 hover:bg-gray-100 dark:bg-gray-800 dark:text-red-500 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700" onclick="return confirm('Xóa màu này?')">
                                                        <i class="bi bi-trash"></i>
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
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.1)]">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 font-semibold">ID</th>
                                        <th scope="col" class="px-6 py-3 font-semibold">Ký hiệu (Size)</th>

                                        <th scope="col" class="px-6 py-3 font-semibold text-right">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sizes as $size)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            <td class="px-6 py-4 font-mono text-xs text-gray-500 dark:text-gray-400">#{{ $size->id }}</td>
                                            <td class="px-6 py-4">
                                                <x-admin.badge variant="dark">{{ $size->name }}</x-admin.badge>
                                            </td>

                                            <td class="px-6 py-4 text-right">
                                                <form action="{{ route('admin.sizes.destroy', $size->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 hover:bg-gray-100 dark:bg-gray-800 dark:text-red-500 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700" onclick="return confirm('Xóa kích cỡ này?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Chưa có dữ liệu kích cỡ.</td>
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
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.1)]">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 font-semibold">ID</th>
                                        <th scope="col" class="px-6 py-3 font-semibold">Tên chất liệu</th>

                                        <th scope="col" class="px-6 py-3 font-semibold text-right">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($materials as $material)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            <td class="px-6 py-4 font-mono text-xs text-gray-500 dark:text-gray-400">#{{ $material->id }}</td>
                                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $material->name }}</td>

                                            <td class="px-6 py-4 text-right">
                                                <form action="{{ route('admin.materials.destroy', $material->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 hover:bg-gray-100 dark:bg-gray-800 dark:text-red-500 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700" onclick="return confirm('Xóa chất liệu này?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Chưa có dữ liệu chất liệu.</td>
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
