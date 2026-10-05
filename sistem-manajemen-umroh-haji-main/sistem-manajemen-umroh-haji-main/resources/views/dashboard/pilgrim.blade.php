<x-app-layout>
    <x-page-header title="Dashboard Jamaah" subtitle="Selamat datang, {{ auth()->user()->name }}" />

    @unless($data['hasProfile'])
        <div class="mt-6">
            <x-empty-state icon="user" title="Profil jamaah belum tertaut"
                message="Akun Anda belum terhubung ke data jamaah. Hubungi admin travel Anda." />
        </div>
    @else
        @php($reg = $data['registration'])
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mt-6">
            <x-stats-card label="Status Pendaftaran"
                :value="$reg?->status?->label() ?? 'Belum Mendaftar'" icon="list" color="blue" />
            <x-stats-card label="Progress Pembayaran"
                :value="$data['payment']['percent'].'%'" icon="cash" color="green"
                :sub="currency($data['payment']['paid']).' / '.currency($data['payment']['total'])" />
            <x-stats-card label="Dokumen Terverifikasi"
                :value="$data['documents']['verified'].' / '.$data['documents']['total']" icon="doc" color="teal" />
            <x-stats-card label="Hitung Mundur"
                :value="$data['countdown'] !== null ? $data['countdown'].' hari' : '—'" icon="calendar" color="purple"
                :sub="$data['package']?->departure_date ? format_date($data['package']->departure_date) : 'Belum dijadwalkan'" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
            <x-card title="Progress Pembayaran" class="lg:col-span-2">
                @php($p = $data['payment'])
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Terbayar</span>
                        <span class="font-semibold text-gray-800">{{ currency($p['paid']) }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3">
                        <div class="bg-brand h-3 rounded-full" style="width: {{ min(100, $p['percent']) }}%"></div>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Sisa Tagihan</span>
                        <span class="font-semibold text-red-600">{{ currency($p['outstanding']) }}</span>
                    </div>
                </div>

                @if($data['package'])
                    <div class="mt-5 pt-4 border-t border-gray-100 text-sm">
                        <p class="font-medium text-gray-800">{{ $data['package']->name }}</p>
                        <p class="text-gray-400 text-xs mt-0.5">
                            {{ $data['package']->type?->label() }} · {{ $data['package']->duration_days }} hari ·
                            Berangkat {{ format_date($data['package']->departure_date) }}
                        </p>
                    </div>
                @endif
            </x-card>

            <x-card title="Checklist Dokumen">
                @forelse($data['documents']['items'] as $doc)
                    <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                        <span class="text-sm text-gray-700">{{ $doc->label }}</span>
                        <x-badge :color="$doc->status?->badgeColor() ?? 'gray'">{{ $doc->status?->label() ?? '-' }}</x-badge>
                    </div>
                @empty
                    <x-empty-state icon="doc" title="Belum ada dokumen" message="Daftar dokumen muncul setelah pendaftaran." />
                @endforelse
            </x-card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
            <x-card title="Jadwal Manasik" class="lg:col-span-2">
                @forelse($data['manasik'] as $m)
                    <div class="flex items-center gap-3 py-2.5 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                        <div class="h-10 w-10 rounded-lg bg-teal-50 text-teal-600 grid place-items-center text-xs font-semibold text-center leading-none">
                            {{ format_date($m->date, 'd') }}<br>{{ format_date($m->date, 'M') }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $m->title }}</p>
                            <p class="text-xs text-gray-400">{{ $m->location ?? 'Lokasi belum ditentukan' }}</p>
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="calendar" title="Belum ada jadwal manasik" message="Jadwal manasik akan tampil di sini." />
                @endforelse
            </x-card>

            <div>
                @include('dashboard.partials.announcements', ['announcements' => $data['announcements']])
            </div>
        </div>
    @endunless
</x-app-layout>
