@extends('admin.layouts.admin')

@section('content')
<div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Cập nhật Chương Trình Flash Sale</h2>

    <form action="{{ route('admin.flash_sales.update', $flash_sale) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="col-span-1 md:col-span-2">
                <label class="block mb-2 text-sm font-medium text-gray-900">Tên chương trình *</label>
                <input type="text" name="name" value="{{ old('name', $flash_sale->name) }}" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Thời gian bắt đầu *</label>
                <input type="datetime-local" name="start_time" value="{{ old('start_time', $flash_sale->start_time->format('Y-m-d\TH:i')) }}" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('start_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Thời gian kết thúc *</label>
                <input type="datetime-local" name="end_time" value="{{ old('end_time', $flash_sale->end_time->format('Y-m-d\TH:i')) }}" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('end_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Trạng thái</label>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $flash_sale->is_active) ? 'checked' : '' }} class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    <span class="ms-3 text-sm font-medium text-gray-900">Kích hoạt ngay</span>
                </label>
            </div>
        </div>

        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">Lưu lại</button>
        <a href="{{ route('admin.flash_sales.index') }}" class="ml-2 text-gray-500 hover:underline">Hủy</a>
    </form>
</div>
@endsection
