@extends('layouts.client')

@section('title', $post->title)
@section('description', Str::limit(strip_tags($post->excerpt ?: $post->content), 150))
@if($post->image)
@section('og_image', asset('storage/' . $post->image))
@endif

@section('content')
<!-- Breadcrumbs -->
<div class="bg-gray-50 py-4 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex text-sm" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="text-gray-500 hover:text-brand transition-colors">Trang chủ</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="bi bi-chevron-right text-gray-400 text-xs mx-1"></i>
                        <a href="{{ route('posts.index') }}" class="text-gray-500 hover:text-brand transition-colors">Tạp chí</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="bi bi-chevron-right text-gray-400 text-xs mx-1"></i>
                        <span class="text-gray-900 font-medium truncate max-w-xs">{{ $post->title }}</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-10">
        
        <!-- Main Content -->
        <article class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @if($post->image)
            <div class="w-full h-64 md:h-96 lg:h-[500px] overflow-hidden">
                <img src="{{ Storage::url($post->image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
            </div>
            @endif
            
            <div class="p-6 md:p-10">
                <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                    <span class="flex items-center"><i class="bi bi-calendar-event mr-2 text-brand"></i> {{ $post->published_at ? $post->published_at->format('d/m/Y') : 'Mới nhất' }}</span>
                    <span class="flex items-center"><i class="bi bi-person mr-2 text-brand"></i> Admin</span>
                </div>
                
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6 leading-tight">{{ $post->title }}</h1>
                
                @if($post->excerpt)
                <div class="text-xl text-gray-600 font-medium italic mb-8 border-l-4 border-brand pl-4 py-1">
                    {{ $post->excerpt }}
                </div>
                @endif
                
                <div class="prose prose-lg max-w-none text-gray-700">
                    {!! nl2br(e($post->content)) !!}
                    <!-- Note: If using rich text editor later, remove nl2br(e()) and just use {!! $post->content !!} -->
                </div>
                
                <!-- Share -->
                <div class="mt-12 pt-6 border-t border-gray-100 flex items-center justify-between">
                    <span class="font-bold text-gray-900">Chia sẻ bài viết:</span>
                    <div class="flex items-center gap-3">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 transition-colors shadow-sm"><i class="bi bi-facebook"></i></a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}" target="_blank" class="w-10 h-10 rounded-full bg-sky-500 text-white flex items-center justify-center hover:bg-sky-600 transition-colors shadow-sm"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>
            </div>
        </article>
        
        <!-- Sidebar -->
        <aside class="space-y-8">
            @if($relatedPosts->count() > 0)
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-xl font-bold text-gray-900 mb-4 border-b border-gray-100 pb-3">Bài viết mới</h3>
                <div class="space-y-4">
                    @foreach($relatedPosts as $related)
                    <a href="{{ url('/' . $related->slug . '-p' . $related->id . '.html') }}" class="flex gap-4 group">
                        <div class="w-20 h-20 rounded-lg overflow-hidden shrink-0 bg-gray-100">
                            @if($related->image)
                                <img src="{{ Storage::url($related->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs text-center p-1">Aurelia</div>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800 text-sm line-clamp-2 group-hover:text-brand transition-colors leading-tight">{{ $related->title }}</h4>
                            <p class="text-xs text-gray-500 mt-1">{{ $related->published_at ? $related->published_at->format('d/m/Y') : '' }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
            
            <div class="bg-brand text-white p-6 rounded-2xl shadow-sm relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/20 rounded-full blur-xl"></div>
                <h3 class="text-xl font-bold mb-2 relative z-10">Đăng ký nhận tin</h3>
                <p class="text-white/80 text-sm mb-4 relative z-10">Nhận ngay voucher 10% và cập nhật các BST mới nhất.</p>
                <form class="relative z-10">
                    <input type="email" placeholder="Email của bạn..." class="w-full px-4 py-2 rounded-lg text-gray-900 mb-2 border-0 focus:ring-2 focus:ring-white">
                    <button type="button" class="w-full bg-gray-900 hover:bg-black text-white font-bold py-2 rounded-lg transition-colors">Đăng ký ngay</button>
                </form>
            </div>
        </aside>
        
    </div>
</div>
@endsection
