@props(['title', 'subtitle' => null])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-gray-800">{{ $title }}</h1>
        @if($subtitle)<p class="text-sm text-gray-400 mt-0.5">{{ $subtitle }}</p>@endif
    </div>
    @isset($actions)
        <div class="flex items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
