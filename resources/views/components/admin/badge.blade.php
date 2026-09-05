@props([
    'variant' => 'info',
    'rounded' => false
])

@php
    $baseClasses = 'text-xs font-medium px-2.5 py-0.5';
    $roundedClass = $rounded ? 'rounded-full' : 'rounded';
    
    $variantClasses = [
        'primary' => 'bg-primary-100 text-primary-800 border border-primary-200 dark:bg-primary-900/30 dark:text-primary-300 dark:border-primary-800',
        'dark' => 'bg-slate-100 text-slate-800 border border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600',
        'danger' => 'bg-rose-100 text-rose-800 border border-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-800',
        'success' => 'bg-emerald-100 text-emerald-800 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800',
        'warning' => 'bg-amber-100 text-amber-800 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800',
        'info' => 'bg-sky-100 text-sky-800 border border-sky-200 dark:bg-sky-900/30 dark:text-sky-300 dark:border-sky-800',
        'purple' => 'bg-fuchsia-100 text-fuchsia-800 border border-fuchsia-200 dark:bg-fuchsia-900/30 dark:text-fuchsia-300 dark:border-fuchsia-800',
        'pink' => 'bg-pink-100 text-pink-800 border border-pink-200 dark:bg-pink-900/30 dark:text-pink-300 dark:border-pink-800',
    ];

    $classes = $baseClasses . ' ' . $roundedClass . ' ' . $variantClasses[$variant];
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
