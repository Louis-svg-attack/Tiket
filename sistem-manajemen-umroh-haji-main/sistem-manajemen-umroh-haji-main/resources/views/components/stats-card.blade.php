@props([
    'label' => '',
    'value' => '0',
    'icon' => 'dot',
    'color' => 'teal',
    'sub' => null,
])

@php
    $map = [
        'teal' => 'bg-teal-50 text-teal-600',
        'blue' => 'bg-blue-50 text-blue-600',
        'green' => 'bg-green-50 text-green-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'purple' => 'bg-purple-50 text-purple-600',
        'red' => 'bg-red-50 text-red-600',
    ];
    $cls = $map[$color] ?? $map['teal'];
@endphp

<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
    <div class="h-12 w-12 rounded-lg grid place-items-center shrink-0 {{ $cls }}">
        <x-icon :name="$icon" class="w-6 h-6" />
    </div>
    <div class="min-w-0">
        <p class="text-xs text-gray-400 truncate">{{ $label }}</p>
        <p class="text-xl font-bold text-gray-800 truncate">{{ $value }}</p>
        @if($sub)<p class="text-[11px] text-gray-400 truncate">{{ $sub }}</p>@endif
    </div>
</div>
