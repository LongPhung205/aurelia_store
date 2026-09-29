@extends('admin.layouts.admin')

@section('title', 'Quản lý Banner')
@section('page_title', 'Quản lý Banner')

@section('content')
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6 mb-6">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Danh sách Banner</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Quản lý banner slider trang chủ và banner hiển thị trên trang danh mục sản phẩm</p>
        </div>
        <a href="{{ route('admin.banners.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 dark:bg-primary-500 dark:hover:bg-primary-600 dark:focus:ring-primary-800 transition-colors shadow-sm">
            <i class="bi bi-plus-lg mr-2"></i> Thêm Banner Mới
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-slate-800 dark:text-green-400 border border-green-200 dark:border-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter Tabs --}}
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 dark:border-slate-700 pb-4 mb-6">
        <a href="{{ route('admin.banners.index', ['type' => 'all']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $currentType === 'all' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
            <i class="bi bi-grid-fill"></i>
            Tất cả
            <span class="px-2 py-0.5 text-xs rounded-full {{ $currentType === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                {{ $counts['all'] }}
            </span>
        </a>
        <a href="{{ route('admin.banners.index', ['type' => 'home_slider']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $currentType === 'home_slider' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
            <i class="bi bi-house-door-fill"></i>
            Slider Trang Chủ
            <span class="px-2 py-0.5 text-xs rounded-full {{ $currentType === 'home_slider' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                {{ $counts['home_slider'] }}
            </span>
        </a>
        <a href="{{ route('admin.banners.index', ['type' => 'category_header']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $currentType === 'category_header' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
            <i class="bi bi-folder2-open"></i>
            Banner Trang Danh Mục
            <span class="px-2 py-0.5 text-xs rounded-full {{ $currentType === 'category_header' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                {{ $counts['category_header'] }}
            </span>
        </a>
    </div>

    <div class="relative overflow-x-auto rounded-lg">
        <table class="w-full text-sm text-left text-slate-500 dark:text-slate-400">
            <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700/50 dark:text-slate-300">
                <tr>
                    <th scope="col" class="px-6 py-4">Hình ảnh</th>
                    <th scope="col" class="px-6 py-4">Vị trí áp dụng</th>
                    <th scope="col" class="px-6 py-4">Tiêu đề / Link</th>
                    <th scope="col" class="px-6 py-4 text-center">Thứ tự</th>
                    <th scope="col" class="px-6 py-4 text-center">Trạng thái</th>
                    <th scope="col" class="px-6 py-4 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banners as $banner)
                <tr class="bg-white dark:bg-slate-800 border-b dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <td class="px-6 py-4">
                        <img src="{{ $banner->display_image_url }}" alt="Banner" class="h-16 w-32 object-cover rounded shadow-sm border border-slate-200 dark:border-slate-600">
                    </td>
                    <td class="px-6 py-4">
                        @if($banner->type === 'category_header')
                            @if($banner->category)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                    <i class="bi bi-tag-fill text-[10px]"></i>
                                    Danh mục: {{ $banner->category->name }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                    <i class="bi bi-collection-fill text-[10px]"></i>
                                    Tất cả danh mục (Dùng chung)
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                <i class="bi bi-images text-[10px]"></i>
                                Slider Trang Chủ
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-slate-900 dark:text-white">{{ $banner->title ?: '—' }}</div>
                        @if($banner->link)
                            <div class="text-xs text-slate-500 mt-1 truncate max-w-[200px]" title="{{ $banner->link }}">
                                <a href="{{ $banner->link }}" target="_blank" class="hover:text-primary-600 inline-flex items-center gap-1">
                                    <i class="bi bi-link-45deg"></i> {{ $banner->link }}
                                </a>
                            </div>
                        @endif
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
                        <a href="{{ route('admin.banners.edit', $banner->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 bg-blue-50 hover:bg-blue-100 dark:text-blue-400 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 transition-colors" title="Chỉnh sửa">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" class="inline-block form-delete"
                              data-confirm-title="Xóa banner?"
                              data-confirm-text="Bạn có chắc chắn muốn xóa banner này không?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 bg-red-50 hover:bg-red-100 dark:text-red-400 dark:bg-red-900/30 dark:hover:bg-red-900/50 transition-colors" title="Xóa">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                        <div class="flex flex-col items-center justify-center">
                            <i class="bi bi-images text-4xl text-slate-300 dark:text-slate-600 mb-2"></i>
                            <p class="font-medium">Chưa có banner nào trong mục này.</p>
                            <a href="{{ route('admin.banners.create') }}" class="text-primary-600 hover:underline text-xs mt-1">Thêm banner mới ngay</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($banners->hasPages())
        <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
            {{ $banners->links() }}
        </div>
    @endif
</div>
@endsection
