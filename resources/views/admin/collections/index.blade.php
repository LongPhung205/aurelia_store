@extends('admin.layouts.admin')

@section('title', 'Quản lý Bộ Sưu Tập')

@section('content')
<div class="w-full h-[calc(100vh-90px)] flex flex-col">

    <x-admin.card noPadding="true" title="Tất cả Bộ Sưu Tập" icon="bi bi-collection" class="flex-1 flex flex-col min-h-0" bodyClass="flex-1 flex flex-col min-h-0">
        <x-slot name="headerActions">
            <x-admin.button href="{{ route('admin.collections.create') }}" variant="primary" size="sm" icon="bi bi-plus-circle">
                Thêm Mới
            </x-admin.button>
        </x-slot>
        
        <div class="overflow-auto flex-1 relative shadow-inner">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 relative">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.1)]">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-16 text-center">STT</th>
                        <th scope="col" class="px-6 py-3 w-24">Hình ảnh</th>
                        <th scope="col" class="px-6 py-3">Tên bộ sưu tập</th>
                        <th scope="col" class="px-6 py-3 text-center">Vị trí</th>
                        <th scope="col" class="px-6 py-3 text-center">Trạng thái</th>
                        <th scope="col" class="px-6 py-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($collections as $collection)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 text-center font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="w-16 h-12 rounded bg-gray-100 flex items-center justify-center overflow-hidden shrink-0">
                                    @if($collection->image)
                                        <img src="{{ Storage::url($collection->image) }}" alt="{{ $collection->name }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="bi bi-image text-gray-400 text-xl"></i>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $collection->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $collection->slug }}</div>
                                <div class="text-xs text-blue-600 mt-1"><i class="bi bi-box-seam mr-1"></i> {{ $collection->products()->count() }} sản phẩm</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300 border border-gray-500">
                                    {{ $collection->position }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($collection->is_active)
                                    <span class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">
                                        <span class="w-2 h-2 mr-1 bg-green-500 rounded-full inline-block"></span>
                                        Hiển thị
                                    </span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded-full dark:bg-gray-700 dark:text-gray-300">
                                        <span class="w-2 h-2 mr-1 bg-gray-500 rounded-full inline-block"></span>
                                        Đã ẩn
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-admin.button href="{{ route('admin.collections.edit', $collection) }}" variant="info" size="sm" icon="bi bi-pencil-square" title="Sửa" />
                                    
                                    <form action="{{ route('admin.collections.destroy', $collection) }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bộ sưu tập này? Mọi sản phẩm bên trong sẽ không bị xóa mà chỉ bị gỡ khỏi bộ sưu tập này.');">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.button type="submit" variant="danger" size="sm" icon="bi bi-trash" title="Xóa" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="bi bi-inbox text-4xl mb-2 text-gray-300 dark:text-gray-600"></i>
                                    <p>Chưa có bộ sưu tập nào được tạo.</p>
                                    <a href="{{ route('admin.collections.create') }}" class="text-blue-600 hover:underline mt-2 inline-block">Thêm bộ sưu tập đầu tiên ngay</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</div>
@endsection
