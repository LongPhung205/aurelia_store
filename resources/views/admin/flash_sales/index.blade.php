@extends('admin.layouts.admin')

@section('content')
<div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Quản lý Flash Sale</h2>
        <a href="{{ route('admin.flash_sales.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Thêm chương trình
        </a>
    </div>



    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500 border">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3">Tên chương trình</th>
                    <th class="px-6 py-3">Thời gian diễn ra</th>
                    <th class="px-6 py-3">Trạng thái</th>
                    <th class="px-6 py-3 text-right">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($flashSales as $flash_sale)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4 font-bold text-gray-900">{{ $flash_sale->name }}</td>
                    <td class="px-6 py-4">
                        <div class="text-xs">Từ: {{ $flash_sale->start_time->format('d/m/Y H:i') }}</div>
                        <div class="text-xs text-red-500">Đến: {{ $flash_sale->end_time->format('d/m/Y H:i') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @if($flash_sale->isActive())
                            <span class="px-2 py-1 rounded text-xs text-white bg-green-500">Đang diễn ra</span>
                        @else
                            <span class="px-2 py-1 rounded text-xs text-white bg-gray-500">Đã dừng / Chờ</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.flash_sales.items', $flash_sale) }}" class="text-indigo-600 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 hover:bg-gray-100 dark:bg-gray-800 dark:text-indigo-500 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700" title="Sản phẩm tham gia">
                                <i class="bi bi-box-seam"></i>
                            </a>
                            <a href="{{ route('admin.flash_sales.edit', $flash_sale) }}" class="text-blue-600 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 hover:bg-gray-100 dark:bg-gray-800 dark:text-blue-500 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700" title="Sửa">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('admin.flash_sales.destroy', $flash_sale) }}" method="POST" class="inline-block form-delete" data-confirm-title="Bạn có chắc chắn muốn xóa chương trình này?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 hover:bg-gray-100 dark:bg-gray-800 dark:text-red-500 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700" title="Xóa">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Chưa có chương trình Flash Sale nào</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $flashSales->links() }}
    </div>
</div>
@endsection
