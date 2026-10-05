<x-app-layout>
    <x-page-header title="Dashboard Agen" subtitle="Ringkasan referral &amp; komisi Anda" />

    @unless($data['hasProfile'])
        <div class="mt-6">
            <x-empty-state icon="user" title="Profil agen belum tertaut"
                message="Akun Anda belum terhubung ke data agen. Hubungi admin untuk menautkan profil." />
        </div>
    @else
        @php($s = $data['stats'])
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mt-6">
            <x-stats-card label="Jamaah Referral" :value="number_format($s['referrals'])" icon="users" color="teal" />
            <x-stats-card label="Total Komisi" :value="currency($s['commission_total'])" icon="percent" color="green" />
            <x-stats-card label="Komisi Dibayar" :value="currency($s['commission_paid'])" icon="cash" color="blue" />
            <x-stats-card label="Komisi Pending" :value="currency($s['commission_pending'])" icon="history" color="amber" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
            <x-card title="Komisi per Bulan" class="lg:col-span-2">
                <x-chart id="chartAgentMonthly" type="bar"
                    :data="[
                        'labels' => $data['monthly']['labels'],
                        'datasets' => [[
                            'label' => 'Komisi (Rp)',
                            'data' => $data['monthly']['data'],
                            'backgroundColor' => '#0d9488',
                            'borderRadius' => 6,
                        ]],
                    ]"
                    :options="['plugins' => ['legend' => ['display' => false]]]" />
            </x-card>

            <x-card title="Status Bayar Jamaah">
                @if(array_sum($data['paymentStatus']['data']) > 0)
                    <x-chart id="chartAgentPay" type="doughnut"
                        :data="[
                            'labels' => $data['paymentStatus']['labels'],
                            'datasets' => [[
                                'data' => $data['paymentStatus']['data'],
                                'backgroundColor' => ['#16a34a', '#f59e0b', '#dc2626'],
                            ]],
                        ]" />
                @else
                    <x-empty-state icon="cash" title="Belum ada jamaah" message="Daftarkan jamaah untuk melihat grafik." />
                @endif
            </x-card>
        </div>
    @endunless

    <div class="mt-4">
        @include('dashboard.partials.announcements', ['announcements' => $data['announcements']])
    </div>
</x-app-layout>
