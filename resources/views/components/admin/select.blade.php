@props([
    'disabled' => false,
    'label' => null,
    'name' => null,
    'id' => null,
    'required' => false,
    'error' => null,
])

@php
    $id = $id ?? $name;
    $hasError = $error || ($name && $errors->has($name));
    
    $baseSelectClass = 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full p-2.5 shadow-sm';
    
    if ($hasError) {
        $baseSelectClass = 'bg-rose-50 dark:bg-rose-900/30 border-rose-300 dark:border-rose-800 text-rose-900 dark:text-rose-400 text-sm rounded-lg focus:ring-rose-500 focus:border-rose-500 block w-full p-2.5 shadow-sm';
    }
@endphp

<div class="mb-4">
    @if($label)
        <label for="{{ $id }}" class="block mb-2 text-sm font-semibold {{ $hasError ? 'text-rose-600 dark:text-rose-500' : 'text-slate-700 dark:text-slate-300' }}">
            {{ $label }} @if($required)<span class="text-rose-500 dark:text-rose-400">*</span>@endif
        </label>
    @endif
    
    <select 
        name="{{ $name }}" 
        id="{{ $id }}" 
        {{ $disabled ? 'disabled' : '' }} 
        {{ $required ? 'required' : '' }}
        {!! $attributes->merge(['class' => $baseSelectClass]) !!}>
        {{ $slot }}
    </select>
    
    @if($name && $errors->has($name))
        <p class="mt-2 text-sm text-rose-500 dark:text-rose-400">{{ $errors->first($name) }}</p>
    @elseif($error)
        <p class="mt-2 text-sm text-rose-500 dark:text-rose-400">{{ $error }}</p>
    @endif
</div>
