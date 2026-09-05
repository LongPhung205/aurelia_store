@extends('admin.layouts.admin')

@section('content')
<div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Cập nhật Mã Giảm Giá</h2>

    <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Mã Code *</label>
                <input type="text" name="code" value="{{ old('code', $coupon->code) }}" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Tên chương trình *</label>
                <input type="text" name="name" value="{{ old('name', $coupon->name) }}" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Loại giảm giá *</label>
                <select name="type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="fixed" {{ old('type', $coupon->type) == 'fixed' ? 'selected' : '' }}>Giảm tiền mặt (đ)</option>
                    <option value="percent" {{ old('type', $coupon->type) == 'percent' ? 'selected' : '' }}>Giảm theo phần trăm (%)</option>
                </select>
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Giá trị giảm *</label>
                <input type="number" step="0.01" name="value" value="{{ old('value', $coupon->value) }}" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Giá trị đơn hàng tối thiểu</label>
                <input type="number" name="min_order_value" value="{{ old('min_order_value', $coupon->min_order_value) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Giảm tối đa (Chỉ dùng cho %)</label>
                <input type="number" name="max_discount" value="{{ old('max_discount', $coupon->max_discount) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Thời gian bắt đầu *</label>
                <input type="datetime-local" name="start_time" value="{{ old('start_time', $coupon->start_time->format('Y-m-d\TH:i')) }}" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Thời gian kết thúc *</label>
                <input type="datetime-local" name="end_time" value="{{ old('end_time', $coupon->end_time->format('Y-m-d\TH:i')) }}" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Giới hạn số lần dùng (Để trống là ko giới hạn)</label>
                <input type="number" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Trạng thái</label>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }} class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    <span class="ms-3 text-sm font-medium text-gray-900">Kích hoạt</span>
                </label>
            </div>
        </div>

        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">Cập nhật</button>
        <a href="{{ route('admin.coupons.index') }}" class="ml-2 text-gray-500 hover:underline">Hủy</a>
    </form>
</div>
@endsection
