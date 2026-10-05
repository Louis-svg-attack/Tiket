<x-app-layout>
    <x-page-header title="Dashboard Operasional"
        subtitle="Ringkasan {{ app(\App\Services\BranchContext::class)->currentBranch()?->name ?? 'Semua Cabang' }}" />

    @php($s = $data['stats'])
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mt-6">
        <x-stats-card label="Total Jamaah" :value="number_format($s['pilgrims'])" icon="users" color="teal" />
        <x-stats-card label="Pendaftaran Aktif" :value="number_format($s['registrations'])" icon="list" color="blue" />
        <x-stats-card label="Omzet Diterima" :value="currency($s['omzet'])" icon="cash" color="green" />
        <x-stats-card label="Outstanding" :value="currency($s['outstanding'])" icon="percent" color="amber" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
        <x-card title="Pendaftaran 12 Bulan Terakhir" class="lg:col-span-2">
            <x-chart id="chartMonthly" type="line"
                :data="[
                    'labels' => $data['monthly']['labels'],
                    'datasets' => [[
                        'label' => 'Pendaftaran',
                        'data' => $data['monthly']['data'],
                        'borderColor' => '#0d9488',
                        'backgroundColor' => 'rgba(13,148,136,.12)',
                        'fill' => true,
                        'tension' => 0.35,
                    ]],
                ]" />
        </x-card>

        <x-card title="Status Pembayaran">
            @if(array_sum($data['paymentStatus']['data']) > 0)
                <x-chart id="chartPayStatus" type="doughnut" :height="260"
                    :data="[
                        'labels' => $data['paymentStatus']['labels'],
                        'datasets' => [[
                            'data' => $data['paymentStatus']['data'],
                            'backgroundColor' => ['#16a34a', '#f59e0b', '#dc2626'],
                        ]],
                    ]" />
            @else
                <x-empty-state icon="cash" title="Belum ada data pembayaran" message="Grafik tampil setelah ada pendaftaran." />
            @endif
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
        <x-card title="Jamaah per Paket" class="lg:col-span-2">
            @if(count($data['perPackage']['labels']) > 0)
                <x-chart id="chartPerPackage" type="bar"
                    :data="[
                        'labels' => $data['perPackage']['labels'],
                        'datasets' => [[
                            'label' => 'Jamaah',
                            'data' => $data['perPackage']['data'],
                            'backgroundColor' => '#0d9488',
                            'borderRadius' => 6,
                        ]],
                    ]"
                    :options="['plugins' => ['legend' => ['display' => false]]]" />
            @else
                <x-empty-state icon="package" title="Belum ada paket dengan jamaah" message="Tambahkan paket lalu daftarkan jamaah." />
            @endif
        </x-card>

        <x-card title="Keberangkatan Terdekat">
            @forelse($data['upcoming'] as $pkg)
                <div class="py-2.5 flex items-center justify-between {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <div class="min-w-0">
                        <p class="font-medium text-gray-800 text-sm truncate">{{ $pkg->name }}</p>
                        <p class="text-xs text-gray-400">{{ format_date($pkg->departure_date) }} · sisa kuota {{ $pkg->remainingQuota() }}</p>
                    </div>
                    <x-badge :color="$pkg->type?->badgeColor() ?? 'gray'">{{ $pkg->type?->label() ?? '-' }}</x-badge>
                </div>
            @empty
                <x-empty-state icon="calendar" title="Tidak ada keberangkatan" message="Belum ada paket dengan tanggal berangkat mendatang." />
            @endforelse
        </x-card>
    </div>

    <div class="mt-4">
        @include('dashboard.partials.announcements', ['announcements' => $data['announcements']])
    </div>
</x-app-layout>
