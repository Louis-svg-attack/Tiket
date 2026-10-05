@props([
    'href' => '#',
    'variant' => 'primary',   // primary | secondary | ghost | danger
    'icon' => null,
])

@php
    $variants = [
        'primary'   => 'bg-brand text-white hover:bg-brand-dark',
        'secondary' => 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50',
        'ghost'     => 'text-brand hover:underline',
        'danger'    => 'bg-red-600 text-white hover:bg-red-700',
    ];
    $cls = $variants[$variant] ?? $variants['primary'];
@endphp

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm font-medium transition $cls"]) }}>
    @if($icon)<x-icon :name="$icon" class="w-4 h-4" />@endif
    {{ $slot }}
</a>
