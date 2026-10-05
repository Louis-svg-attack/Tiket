@props(['items' => []])
{{-- $items: array of ['label' => ..., 'route' => ... (optional)] --}}

<nav class="flex items-center gap-1.5 text-xs text-gray-400 mb-3" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}" class="hover:text-brand">Beranda</a>
    @foreach($items as $item)
        <x-icon name="chevron" class="w-3 h-3" />
        @if(!empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']))
            <a href="{{ route($item['route']) }}" class="hover:text-brand">{{ $item['label'] ?? '-' }}</a>
        @else
            <span class="text-gray-600">{{ $item['label'] ?? '-' }}</span>
        @endif
    @endforeach
</nav>
