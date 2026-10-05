<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Paket', 'route' => 'packages.index'], ['label' => $package->name]]" />
    <x-page-header :title="$package->name" :subtitle="$package->code ?? '-'">
        <x-slot name="actions">
            @can('update', $package)
                <x-action-link :href="route('packages.edit', $package)" icon="package">Edit</x-action-link>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
        <x-stats-card label="Harga" :value="currency($package->price)" icon="cash" color="green"
            :sub="'DP '.currency($package->down_payment)" />
        <x-stats-card label="Pendaftar Aktif" :value="$package->activeRegistrationsCount()" icon="users" color="blue"
            :sub="'Kuota '.$package->quota" />
        <x-stats-card label="Sisa Kuota" :value="$package->remainingQuota()" icon="list" color="teal"
            :sub="$package->isFull() ? 'Penuh' : 'Tersedia'" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        <x-card title="Informasi Paket">
            <dl class="divide-y divide-gray-100 text-sm">
                <div class="flex justify-between py-2"><dt class="text-gray-500">Jenis</dt>
                    <dd><x-badge :color="$package->type?->badgeColor() ?? 'gray'">{{ $package->type?->label() ?? '-' }}</x-badge></dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">Status</dt>
                    <dd><x-badge :color="$package->status?->badgeColor() ?? 'gray'">{{ $package->status?->label() ?? '-' }}</x-badge></dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">Cabang</dt>
                    <dd class="text-gray-800">{{ $package->branch?->name ?? '-' }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">Durasi</dt>
                    <dd class="text-gray-800">{{ $package->duration_days ? $package->duration_days.' hari' : '-' }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">Tanggal Berangkat</dt>
                    <dd class="text-gray-800">{{ $package->departure_date ? format_date($package->departure_date) : '-' }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">Kota Keberangkatan</dt>
                    <dd class="text-gray-800">{{ $package->departure_city ?? '-' }}</dd></div>
            </dl>
        </x-card>

        <x-card title="Akomodasi &amp; Transportasi">
            <dl class="divide-y divide-gray-100 text-sm">
                <div class="flex justify-between py-2"><dt class="text-gray-500">Hotel Mekah</dt>
                    <dd class="text-gray-800">{{ $package->hotelMakkah?->name ?? '-' }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">Hotel Madinah</dt>
                    <dd class="text-gray-800">{{ $package->hotelMadinah?->name ?? '-' }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">Maskapai</dt>
                    <dd class="text-gray-800">{{ $package->airline?->name ?? '-' }}</dd></div>
            </dl>
        </x-card>

        <x-card title="Fasilitas Termasuk">
            @forelse($package->facilities_included ?? [] as $item)
                <div class="flex items-center gap-2 py-1 text-sm text-gray-700">
                    <x-icon name="check" class="w-4 h-4 text-green-500" />{{ $item }}
                </div>
            @empty
                <p class="text-sm text-gray-400">-</p>
            @endforelse
        </x-card>

        <x-card title="Fasilitas Tidak Termasuk">
            @forelse($package->facilities_excluded ?? [] as $item)
                <div class="flex items-center gap-2 py-1 text-sm text-gray-700">
                    <x-icon name="dot" class="w-4 h-4 text-red-400" />{{ $item }}
                </div>
            @empty
                <p class="text-sm text-gray-400">-</p>
            @endforelse
        </x-card>
    </div>

    @if($package->description)
        <x-card title="Deskripsi" class="mt-4">
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $package->description }}</p>
        </x-card>
    @endif
</x-app-layout>
