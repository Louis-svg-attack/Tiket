<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Jamaah', 'route' => 'pilgrims.index'], ['label' => $pilgrim->name]]" />
    <x-page-header :title="$pilgrim->name" :subtitle="$pilgrim->code ?? '-'">
        <x-slot name="actions">
            @can('update', $pilgrim)
                <x-action-link :href="route('pilgrims.edit', $pilgrim)" icon="user" variant="secondary">Edit</x-action-link>
            @endcan
        </x-slot>
    </x-page-header>

    @if($pilgrim->passportExpiringSoon())
        <div class="mt-6 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <x-icon name="doc" class="w-5 h-5 mt-0.5 shrink-0" />
            <div>
                <p class="font-medium">Paspor akan segera kadaluarsa</p>
                <p>Masa berlaku paspor berakhir {{ format_date($pilgrim->passport_expired_at) }}. Segera perpanjang sebelum keberangkatan.</p>
            </div>
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-card title="Data Pribadi">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-gray-400">Jenis Kelamin</dt>
                        <dd class="mt-0.5"><x-badge :color="$pilgrim->gender?->badgeColor() ?? 'gray'">{{ $pilgrim->gender?->label() ?? '-' }}</x-badge></dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">NIK</dt>
                        <dd class="mt-0.5 text-gray-800">{{ $pilgrim->nik ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Tempat / Tanggal Lahir</dt>
                        <dd class="mt-0.5 text-gray-800">{{ $pilgrim->birth_place ?? '-' }}{{ $pilgrim->birth_date ? ', '.format_date($pilgrim->birth_date) : '' }}{{ $pilgrim->age() !== null ? ' ('.$pilgrim->age().' th)' : '' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Golongan Darah</dt>
                        <dd class="mt-0.5 text-gray-800">{{ $pilgrim->blood_type ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Telepon</dt>
                        <dd class="mt-0.5">
                            @if($pilgrim->phone)
                                <a href="{{ wa_link($pilgrim->phone) }}" target="_blank" class="text-brand hover:underline">{{ $pilgrim->phone }}</a>
                            @else <span class="text-gray-800">-</span> @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Email</dt>
                        <dd class="mt-0.5 text-gray-800">{{ $pilgrim->email ?? '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-gray-400">Alamat</dt>
                        <dd class="mt-0.5 text-gray-800">{{ $pilgrim->address ?? '-' }}{{ $pilgrim->city ? ', '.$pilgrim->city : '' }}</dd>
                    </div>
                </dl>
            </x-card>

            <x-card title="Paspor">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-gray-400">Nomor Paspor</dt>
                        <dd class="mt-0.5 text-gray-800">{{ $pilgrim->passport_number ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Tempat Penerbitan</dt>
                        <dd class="mt-0.5 text-gray-800">{{ $pilgrim->passport_issued_place ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Tanggal Terbit</dt>
                        <dd class="mt-0.5 text-gray-800">{{ format_date($pilgrim->passport_issued_at) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Tanggal Kadaluarsa</dt>
                        <dd class="mt-0.5">
                            <span class="text-gray-800">{{ format_date($pilgrim->passport_expired_at) }}</span>
                            @if($pilgrim->passportExpiringSoon())
                                <x-badge color="red" class="ml-1">Segera kadaluarsa</x-badge>
                            @endif
                        </dd>
                    </div>
                </dl>
            </x-card>

            <x-card title="Mahram &amp; Kontak Darurat">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-gray-400">Nama Mahram</dt>
                        <dd class="mt-0.5 text-gray-800">{{ $pilgrim->mahram_name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Hubungan Mahram</dt>
                        <dd class="mt-0.5 text-gray-800">{{ $pilgrim->mahram_relation ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Kontak Darurat</dt>
                        <dd class="mt-0.5 text-gray-800">{{ $pilgrim->emergency_contact_name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Telepon Darurat</dt>
                        <dd class="mt-0.5 text-gray-800">{{ $pilgrim->emergency_contact_phone ?? '-' }}</dd>
                    </div>
                </dl>
            </x-card>

            <x-card title="Kesehatan">
                <p class="text-sm text-gray-800 whitespace-pre-line">{{ $pilgrim->health_notes ?? 'Tidak ada catatan kesehatan.' }}</p>
            </x-card>

            <x-card title="Pendaftaran Paket">
                @forelse($registrations as $registration)
                    <div class="flex items-center justify-between py-2 {{ ! $loop->last ? 'border-b border-gray-100' : '' }}">
                        <div>
                            <div class="font-medium text-gray-800">{{ $registration->package?->name ?? 'Paket dihapus' }}</div>
                            <div class="text-xs text-gray-400">{{ $registration->code ?? '-' }} &middot; {{ format_date($registration->registered_at) }}</div>
                        </div>
                        <x-badge :color="$registration->status?->badgeColor() ?? 'gray'">{{ $registration->status?->label() ?? '-' }}</x-badge>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Belum ada pendaftaran paket.</p>
                @endforelse
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card title="Ringkasan">
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-400">Status</dt>
                        <dd><x-badge :color="$pilgrim->is_active ? 'green' : 'gray'">{{ $pilgrim->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-400">Cabang</dt>
                        <dd class="text-gray-800">{{ $pilgrim->branch?->name ?? '-' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-400">Agen</dt>
                        <dd class="text-gray-800">{{ $pilgrim->agent?->name ?? '-' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-400">Akun Login</dt>
                        <dd><x-badge :color="$pilgrim->user_id ? 'green' : 'gray'">{{ $pilgrim->user_id ? 'Aktif' : '—' }}</x-badge></dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-400">Dokumen</dt>
                        <dd class="text-gray-800">{{ $pilgrim->documents_count ?? 0 }} berkas</dd>
                    </div>
                </dl>
            </x-card>
        </div>
    </div>
</x-app-layout>
