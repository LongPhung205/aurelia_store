@props([
    'disabled' => false,
    'label' => null,
    'name' => null,
    'id' => null,
    'placeholder' => null,
    'required' => false,
    'rows' => 4,
    'error' => null,
])

@php
    $id = $id ?? $name;
    $hasError = $error || ($name && $errors->has($name));
    
    $baseTextareaClass = 'block p-2.5 w-full text-sm text-slate-900 bg-white dark:bg-slate-800 dark:text-white rounded-lg border border-slate-200 dark:border-slate-700 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 shadow-sm';
    
    if ($hasError) {
        $baseTextareaClass = 'block p-2.5 w-full text-sm text-rose-900 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/30 rounded-lg border border-rose-300 dark:border-rose-800 dark:placeholder-rose-500 focus:ring-rose-500 focus:border-rose-500 shadow-sm';
    }
@endphp

<div class="mb-4">
    @if($label)
        <label for="{{ $id }}" class="block mb-2 text-sm font-semibold {{ $hasError ? 'text-rose-600 dark:text-rose-500' : 'text-slate-700 dark:text-slate-300' }}">
            {{ $label }} @if($required)<span class="text-rose-500 dark:text-rose-400">*</span>@endif
        </label>
    @endif
    
    <textarea 
        name="{{ $name }}" 
        id="{{ $id }}" 
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $disabled ? 'disabled' : '' }} 
        {{ $required ? 'required' : '' }}
        {!! $attributes->merge(['class' => $baseTextareaClass]) !!}>{{ $slot }}</textarea>
    
    @if($name && $errors->has($name))
        <p class="mt-2 text-sm text-rose-500 dark:text-rose-400">{{ $errors->first($name) }}</p>
    @elseif($error)
        <p class="mt-2 text-sm text-rose-500 dark:text-rose-400">{{ $error }}</p>
    @endif
</div>
