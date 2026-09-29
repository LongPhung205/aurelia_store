@extends('admin.layouts.admin')

@section('title', 'Quản lý Tạp chí & Lookbook')

@section('content')
<div class="px-0 w-full h-[calc(100vh-90px)] flex flex-col">
    <!-- Removed Header & Breadcrumb for a cleaner look -->

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
            <span class="font-medium"><i class="bi bi-check-circle mr-2"></i>Thành công!</span> {{ session('success') }}
        </div>
    @endif

    <x-admin.card class="flex-1 flex flex-col min-h-0 border-0 shadow-sm" bodyClass="flex-1 flex flex-col min-h-0" noPadding="true">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 shrink-0 flex justify-between items-center bg-white dark:bg-gray-800 rounded-t-lg">
            <h5 class="mb-0 font-semibold text-blue-600 dark:text-blue-400"><i class="bi bi-journal-text mr-2"></i>Tất cả bài viết</h5>
            <a href="{{ route('admin.posts.create') }}">
                <x-admin.button type="button" variant="primary" size="sm" icon="bi bi-plus-circle">
                    Thêm bài viết
                </x-admin.button>
            </a>
        </div>
        
        <div class="overflow-auto flex-1 relative">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.1)]">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold w-16">ID</th>
                        <th scope="col" class="px-6 py-3 font-semibold w-24 text-center">Ảnh bìa</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Tiêu đề & Trích dẫn</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-center">Trạng thái</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-center">Ngày đăng</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-right w-32">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                            {{ $post->id }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($post->image)
                                <div class="w-16 h-20 overflow-hidden rounded-md border border-gray-200 mx-auto">
                                    <img src="{{ Storage::url($post->image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                                </div>
                            @else
                                <div class="w-16 h-20 flex items-center justify-center bg-gray-100 rounded-md text-gray-400 text-xs border border-gray-200 mx-auto">
                                    No img
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900 dark:text-white text-base mb-1">{{ $post->title }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 max-w-md">{{ $post->excerpt }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($post->is_active)
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Đang hiện</span>
                            @else
                                <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">Đang ẩn</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-400">
                            {{ $post->published_at ? $post->published_at->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.posts.edit', $post) }}" class="text-blue-600 hover:bg-blue-100 p-2 rounded-lg transition-colors dark:text-blue-500 dark:hover:bg-blue-900" title="Sửa">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="inline-block form-delete"
                                      data-confirm-title="Xóa bài viết?"
                                      data-confirm-text="Bạn có chắc chắn muốn xóa bài viết này không?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:bg-red-100 p-2 rounded-lg transition-colors dark:text-red-500 dark:hover:bg-red-900" title="Xóa">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                            <i class="bi bi-journal-x text-3xl mb-3 block"></i>
                            Chưa có bài viết / lookbook nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($posts->hasPages())
        <x-slot name="footer">
            <div class="mt-2 flex justify-end w-full">
                {{ $posts->links('pagination::tailwind') }}
            </div>
        </x-slot>
        @endif
    </x-admin.card>
</div>
@endsection
