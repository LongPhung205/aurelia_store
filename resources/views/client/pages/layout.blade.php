@extends('layouts.client')

@section('content')
<div class="bg-gray-50 py-12 md:py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="bg-brand px-8 py-10 md:px-12 md:py-16 text-center relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
                <div class="absolute -left-10 -bottom-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                
                <h1 class="text-3xl md:text-5xl font-bold text-white relative z-10" style="font-family: 'Playfair Display', serif;">@yield('page_title')</h1>
                @hasSection('page_subtitle')
                    <p class="mt-4 text-brand-100 text-lg md:text-xl font-light relative z-10">@yield('page_subtitle')</p>
                @endif
            </div>

            <!-- Body -->
            <div class="p-8 md:p-12 lg:p-16">
                <div class="prose prose-lg max-w-none text-gray-700">
                    <style>
                        .prose h2 {
                            font-family: 'Playfair Display', serif;
                            color: #111827;
                            font-size: 1.8rem;
                            font-weight: 700;
                            margin-top: 2.5rem;
                            margin-bottom: 1.25rem;
                            padding-bottom: 0.5rem;
                            border-bottom: 2px solid #f3f4f6;
                        }
                        .prose h3 {
                            color: #1f2937;
                            font-weight: 600;
                            margin-top: 2rem;
                        }
                        .prose ul > li::marker {
                            color: var(--brand-color, #111827);
                        }
                    </style>
                    
                    @yield('page_content')
                </div>
            </div>
            
            <!-- Footer Contact -->
            <div class="bg-gray-50 px-8 py-8 md:px-12 border-t border-gray-100 text-center">
                <p class="text-gray-600">Bạn cần hỗ trợ thêm? <a href="{{ route('pages.contact') }}" class="text-brand font-semibold hover:underline">Liên hệ với chúng tôi</a> ngay.</p>
            </div>
        </div>
    </div>
</div>
@endsection
