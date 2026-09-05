@props([
    'variant' => 'primary',
    'type' => 'button',
    'size' => 'md',
    'href' => null,
    'icon' => null
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-colors focus:ring-4 focus:outline-none rounded-lg text-center';
    
    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
        'icon' => 'p-2', // For icon-only buttons
    ];

    $variantClasses = [
        'primary' => 'text-white bg-primary-600 hover:bg-primary-700 shadow-sm shadow-primary-500/30 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800',
        'secondary' => 'text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 hover:text-primary-700 shadow-sm focus:z-10 focus:ring-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:text-white dark:hover:bg-slate-700 dark:focus:ring-slate-700',
        'info' => 'text-white bg-cyan-600 hover:bg-cyan-700 shadow-sm shadow-cyan-500/30 focus:ring-cyan-300 dark:bg-cyan-600 dark:hover:bg-cyan-700 dark:focus:ring-cyan-800',
        'warning' => 'text-white bg-amber-500 hover:bg-amber-600 shadow-sm shadow-amber-500/30 focus:ring-amber-300 dark:bg-amber-500 dark:hover:bg-amber-600 dark:focus:ring-amber-800',
        'danger' => 'text-white bg-rose-600 hover:bg-rose-700 shadow-sm shadow-rose-500/30 focus:ring-rose-300 dark:bg-rose-600 dark:hover:bg-rose-700 dark:focus:ring-rose-900',
        'success' => 'text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm shadow-emerald-500/30 focus:ring-emerald-300 dark:bg-emerald-600 dark:hover:bg-emerald-700 dark:focus:ring-emerald-800',
        'outline-primary' => 'text-primary-700 hover:text-white border border-primary-600 hover:bg-primary-700 focus:ring-primary-300 dark:border-primary-500 dark:text-primary-500 dark:hover:text-white dark:hover:bg-primary-600 dark:focus:ring-primary-800',
        'ghost' => 'text-slate-500 bg-transparent hover:bg-slate-100 focus:ring-slate-200 dark:text-slate-400 dark:hover:bg-slate-700 dark:focus:ring-slate-700',
    ];

    $classes = $baseClasses . ' ' . $sizeClasses[$size] . ' ' . $variantClasses[$variant];
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <i class="{{ $icon }} {{ $slot->isEmpty() ? '' : 'mr-2' }}"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <i class="{{ $icon }} {{ $slot->isEmpty() ? '' : 'mr-2' }}"></i>
        @endif
        {{ $slot }}
    </button>
@endif
