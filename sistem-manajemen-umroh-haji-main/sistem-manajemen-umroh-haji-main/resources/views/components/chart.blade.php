@props([
    'id',
    'type' => 'bar',
    'data' => [],       // Chart.js data object
    'options' => [],    // Chart.js options object
    'height' => 260,
])

<div style="position: relative; height: {{ $height }}px;">
    <canvas id="{{ $id }}"></canvas>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById(@json($id));
        if (!el || typeof window.Chart === 'undefined') return;
        new window.Chart(el.getContext('2d'), {
            type: @json($type),
            data: @json($data),
            options: Object.assign({
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { boxWidth: 12, font: { size: 11 } } } },
            }, @json($options)),
        });
    });
</script>
@endpush
