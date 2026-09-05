@extends('layouts.client')

@section('title', 'Tạp chí & Lookbook')
@section('description', 'Cập nhật các xu hướng thời trang mới nhất, cẩm nang phối đồ và các bộ sưu tập Lookbook ấn tượng từ Aurelia.')

@section('content')
<!-- Page Header -->
<div class="bg-gray-50 py-10 md:py-16 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl md:text-5xl font-bold text-gray-900 mb-4">Tạp Chí & Lookbook</h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">Nguồn cảm hứng bất tận cho phong cách thời trang của bạn. Khám phá các xu hướng mới và bí quyết mặc đẹp mỗi ngày.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
    
    @if($posts->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 md:gap-12">
            @foreach($posts as $post)
            <article class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col h-full">
                <a href="{{ url('/' . $post->slug . '-p' . $post->id . '.html') }}" class="block relative overflow-hidden aspect-[4/3]">
                    @if($post->image)
                        <img src="{{ Storage::url($post->image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    @else
                        <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400">Aurelia Style</div>
                    @endif
                    <div class="absolute top-4 left-4">
                        <span class="bg-white/90 backdrop-blur text-gray-900 text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">{{ $post->published_at ? $post->published_at->format('d/m/Y') : 'Mới' }}</span>
                    </div>
                </a>
                
                <div class="p-6 md:p-8 flex flex-col flex-grow">
                    <div class="flex items-center text-brand text-xs font-bold uppercase tracking-wider mb-3">
                        <span>Aurelia Style</span>
                        <span class="mx-2">•</span>
                        <span>Đọc 3 phút</span>
                    </div>
                    <a href="{{ url('/' . $post->slug . '-p' . $post->id . '.html') }}">
                        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-brand transition-colors">{{ $post->title }}</h2>
                    </a>
                    <p class="text-gray-600 mb-6 line-clamp-3">{{ $post->excerpt }}</p>
                    
                    <div class="mt-auto pt-4 border-t border-gray-100">
                        <a href="{{ url('/' . $post->slug . '-p' . $post->id . '.html') }}" class="inline-flex items-center text-brand font-semibold hover:text-black transition-colors">
                            Đọc tiếp <i class="bi bi-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
        
        <div class="mt-16 flex justify-center">
            {{ $posts->links() }}
        </div>
    @else
        <div class="text-center py-20">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="bi bi-journal-x text-4xl text-gray-400"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Chưa có bài viết nào</h2>
            <p class="text-gray-500">Chúng tôi đang chuẩn bị nội dung. Vui lòng quay lại sau!</p>
            <a href="{{ route('home') }}" class="btn btn-primary mt-6 rounded-full px-8">Quay lại trang chủ</a>
        </div>
    @endif
</div>
@endsection
