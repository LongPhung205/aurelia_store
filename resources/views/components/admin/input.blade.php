@props([
    'disabled' => false,
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'placeholder' => null,
    'required' => false,
    'icon' => null,
    'error' => null,
])

@php
    $id = $id ?? $name;
    $hasError = $error || ($name && $errors->has($name));
    
    $baseInputClass = 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full shadow-sm';
    
    if ($hasError) {
        $baseInputClass = 'bg-rose-50 dark:bg-rose-900/30 border-rose-300 dark:border-rose-800 text-rose-900 dark:text-rose-400 placeholder-rose-400 dark:placeholder-rose-500 text-sm rounded-lg focus:ring-rose-500 focus:border-rose-500 block w-full shadow-sm';
    }
    
    $paddingClass = $icon ? 'pl-10 p-2.5' : 'p-2.5';
    
    $inputClasses = $baseInputClass . ' ' . $paddingClass;
@endphp

<div class="mb-4">
    @if($label)
        <label for="{{ $id }}" class="block mb-2 text-sm font-semibold {{ $hasError ? 'text-rose-600 dark:text-rose-500' : 'text-slate-700 dark:text-slate-300' }}">
            {{ $label }} @if($required)<span class="text-rose-500 dark:text-rose-400">*</span>@endif
        </label>
    @endif
    
    <div class="relative">
        @if($icon)
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i class="{{ $icon }} {{ $hasError ? 'text-rose-400 dark:text-rose-500' : 'text-slate-400 dark:text-slate-500' }}"></i>
            </div>
        @endif
        
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $id }}" 
            {{ $disabled ? 'disabled' : '' }} 
            {{ $required ? 'required' : '' }}
            placeholder="{{ $placeholder }}"
            {!! $attributes->merge(['class' => $inputClasses]) !!}>
    </div>
    
    @if($name && $errors->has($name))
        <p class="mt-2 text-sm text-rose-500 dark:text-rose-400">{{ $errors->first($name) }}</p>
    @elseif($error)
        <p class="mt-2 text-sm text-rose-500 dark:text-rose-400">{{ $error }}</p>
    @endif
</div>
