@props([
    'title' => null,
    'icon' => null,
    'headerActions' => null,
    'footer' => null,
    'noPadding' => false,
    'bodyClass' => ''
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-lg shadow-[0_4px_20px_rgba(0,0,0,0.03)] dark:shadow-none']) }}>
    @if($title || $headerActions || isset($header))
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-white dark:bg-slate-800 rounded-t-lg">
            <div class="flex items-center gap-2">
                @if($icon)
                    <i class="{{ $icon }} text-primary-500 dark:text-primary-400"></i>
                @endif
                @if($title)
                    <h5 class="text-lg font-bold text-slate-800 dark:text-white m-0">{{ $title }}</h5>
                @endif
                {{ $header ?? '' }}
            </div>
            
            @if($headerActions)
                <div class="flex items-center gap-2">
                    {{ $headerActions }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $noPadding ? '' : 'p-6' }} {{ $bodyClass }}">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 rounded-b-lg">
            {{ $footer }}
        </div>
    @endif
</div>
