<x-app-layout>
    <x-page-header title="Dashboard Muthawif" subtitle="Jamaah bimbingan &amp; jadwal manasik Anda" />

    @unless($data['hasProfile'])
        <div class="mt-6">
            <x-empty-state icon="user" title="Profil muthawif belum tertaut"
                message="Akun Anda belum terhubung ke data pembimbing. Hubungi admin." />
        </div>
    @else
        @php($s = $data['stats'])
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
            <x-stats-card label="Jamaah Bimbingan" :value="number_format($s['guided'])" icon="users" color="teal" />
            <x-stats-card label="Manasik Hari Ini" :value="number_format($s['today_manasik'])" icon="calendar" color="blue" />
            <x-stats-card label="Total Kehadiran" :value="number_format($s['total_present'])" icon="check" color="green" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
            <x-card title="Manasik Mendatang" class="lg:col-span-2">
                @forelse($data['upcoming'] as $m)
                    <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-teal-50 text-teal-600 grid place-items-center text-xs font-semibold text-center leading-none">
                                {{ format_date($m->date, 'd') }}<br>{{ format_date($m->date, 'M') }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $m->title }}</p>
                                <p class="text-xs text-gray-400">{{ $m->location ?? '-' }}</p>
                            </div>
                        </div>
                        @if(\Illuminate\Support\Facades\Route::has('manasik.show'))
                            <a href="{{ route('manasik.show', $m) }}" class="text-xs text-brand hover:underline">Absensi</a>
                        @endif
                    </div>
                @empty
                    <x-empty-state icon="calendar" title="Belum ada manasik" message="Jadwal yang Anda pimpin akan tampil di sini." />
                @endforelse
            </x-card>

            <x-card title="Rekap Kehadiran">
                @if(array_sum($data['attendance']['data']) > 0)
                    <x-chart id="chartGuideAttendance" type="doughnut"
                        :data="[
                            'labels' => $data['attendance']['labels'],
                            'datasets' => [[
                                'data' => $data['attendance']['data'],
                                'backgroundColor' => ['#16a34a', '#dc2626', '#f59e0b'],
                            ]],
                        ]" />
                @else
                    <x-empty-state icon="check" title="Belum ada absensi" message="Rekap muncul setelah absensi dicatat." />
                @endif
            </x-card>
        </div>

        <div class="mt-4">
            @include('dashboard.partials.announcements', ['announcements' => $data['announcements']])
        </div>
    @endunless
</x-app-layout>
