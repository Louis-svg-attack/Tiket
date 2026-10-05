@props(['route' => null, 'icon' => 'dot', 'active' => null])

@php
    // Resolve the target URL only when the route actually exists, so a stray
    // sidebar link can never throw during rendering.
    $href = ($route && \Illuminate\Support\Facades\Route::has($route)) ? route($route) : null;
    $isActive = $active ?? ($route ? request()->routeIs($active ?: $route.'*') : false);
@endphp

@if($href)
    <a href="{{ $href }}"
       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100 transition {{ $isActive ? 'nav-active' : '' }}">
        <x-icon :name="$icon" class="w-5 h-5 shrink-0" />
        <span class="truncate">{{ $slot }}</span>
    </a>
@endif
