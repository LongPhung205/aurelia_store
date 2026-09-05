@extends('admin.layouts.admin')

@section('content')
<div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Quản lý Mã Giảm Giá</h2>
        <a href="{{ route('admin.coupons.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Thêm mới
        </a>
    </div>



    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500 border">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3">Mã Code</th>
                    <th class="px-6 py-3">Tên chương trình</th>
                    <th class="px-6 py-3">Giá trị</th>
                    <th class="px-6 py-3">Thời gian</th>
                    <th class="px-6 py-3">Đã dùng/Giới hạn</th>
                    <th class="px-6 py-3">Trạng thái</th>
                    <th class="px-6 py-3 text-right">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($coupons as $coupon)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4 font-bold text-gray-900">{{ $coupon->code }}</td>
                    <td class="px-6 py-4">{{ $coupon->name }}</td>
                    <td class="px-6 py-4">
                        {{ $coupon->type == 'fixed' ? number_format($coupon->value) . ' đ' : $coupon->value . '%' }}
                        @if($coupon->type == 'percent' && $coupon->max_discount)
                            <div class="text-xs text-gray-400">Tối đa: {{ number_format($coupon->max_discount) }} đ</div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-xs">Bắt đầu: {{ $coupon->start_time->format('d/m/Y H:i') }}</div>
                        <div class="text-xs text-red-500">Kết thúc: {{ $coupon->end_time->format('d/m/Y H:i') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        {{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded text-xs text-white {{ $coupon->is_active ? 'bg-green-500' : 'bg-red-500' }}">
                            {{ $coupon->is_active ? 'Hoạt động' : 'Đã tắt' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-blue-600 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 hover:bg-gray-100 dark:bg-gray-800 dark:text-blue-500 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700" title="Sửa">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline-block form-delete" data-confirm-title="Bạn có chắc chắn muốn xóa mã giảm giá này?">
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
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">Chưa có mã giảm giá nào</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $coupons->links() }}
    </div>
</div>
@endsection
