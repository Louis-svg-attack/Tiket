@props(['title' => null, 'subtitle' => null, 'padding' => 'p-5'])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-gray-100 shadow-sm']) }}>
    @if($title || $subtitle || isset($actions))
        <div class="flex items-start justify-between gap-3 px-5 py-4 border-b border-gray-100">
            <div>
                @if($title)<h3 class="font-semibold text-gray-800">{{ $title }}</h3>@endif
                @if($subtitle)<p class="text-xs text-gray-400 mt-0.5">{{ $subtitle }}</p>@endif
            </div>
            @isset($actions)<div class="shrink-0">{{ $actions }}</div>@endisset
        </div>
    @endif
    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</div>
