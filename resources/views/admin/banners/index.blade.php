@extends('admin.layouts.admin')

@section('title', 'Quản lý Banner Trang chủ')
@section('page_title', 'Quản lý Banner Trang chủ')

@section('content')
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6 mb-6">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Danh sách Banner</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Quản lý các hình ảnh slider trên trang chủ</p>
        </div>
        <a href="{{ route('admin.banners.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 dark:bg-primary-500 dark:hover:bg-primary-600 dark:focus:ring-primary-800 transition-colors">
            <i class="bi bi-plus-lg mr-2"></i> Thêm Banner Mới
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-slate-800 dark:text-green-400 border border-green-200 dark:border-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="relative overflow-x-auto rounded-lg">
        <table class="w-full text-sm text-left text-slate-500 dark:text-slate-400">
            <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700/50 dark:text-slate-300">
                <tr>
                    <th scope="col" class="px-6 py-4">Hình ảnh</th>
                    <th scope="col" class="px-6 py-4">Tiêu đề / Link</th>
                    <th scope="col" class="px-6 py-4 text-center">Thứ tự hiển thị</th>
                    <th scope="col" class="px-6 py-4 text-center">Trạng thái</th>
                    <th scope="col" class="px-6 py-4 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banners as $banner)
                <tr class="bg-white dark:bg-slate-800 border-b dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <td class="px-6 py-4">
                        <img src="{{ $banner->display_image_url }}" alt="Banner" class="h-20 w-auto object-cover rounded shadow-sm border border-slate-200 dark:border-slate-600">
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-slate-900 dark:text-white">{{ $banner->title ?? 'Không có tiêu đề' }}</div>
                        <div class="text-xs text-slate-500 mt-1 truncate max-w-[200px]" title="{{ $banner->link }}">
                            {{ $banner->link ?? 'Không có link' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="bg-slate-100 text-slate-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                            {{ $banner->position }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($banner->is_active)
                            <span class="inline-flex items-center bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">
                                <span class="w-2 h-2 me-1 bg-green-500 rounded-full"></span>
                                Hiển thị
                            </span>
                        @else
                            <span class="inline-flex items-center bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-red-900 dark:text-red-300">
                                <span class="w-2 h-2 me-1 bg-red-500 rounded-full"></span>
                                Ẩn
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('admin.banners.edit', $banner->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 bg-blue-50 hover:bg-blue-100 dark:text-blue-400 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 transition-colors">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xóa banner này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 bg-red-50 hover:bg-red-100 dark:text-red-400 dark:bg-red-900/30 dark:hover:bg-red-900/50 transition-colors">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">
                        Chưa có banner nào. Hãy thêm banner đầu tiên!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
