<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Hotel']]" />
    <x-page-header title="Hotel" subtitle="Daftar hotel Mekah &amp; Madinah">
        <x-slot name="actions">
            @can('create', \App\Models\Hotel::class)
                <x-action-link :href="route('hotels.create')" icon="hotel">Tambah Hotel</x-action-link>
            @endcan
        </x-slot>
    </x-page-header>

    <x-card class="mt-6" padding="p-0">
        <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-gray-100">
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama hotel…"
                   class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
            <select name="city" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                <option value="">Semua Kota</option>
                <option value="Mekah" @selected(request('city')==='Mekah')>Mekah</option>
                <option value="Madinah" @selected(request('city')==='Madinah')>Madinah</option>
            </select>
            <button class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium hover:bg-gray-200">Cari</button>
        </form>

        @if($hotels->total() > 0)
            <x-table :headers="['Hotel', 'Kota', 'Bintang', 'Jarak ke Masjid', 'Status', '']">
                @foreach($hotels as $hotel)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $hotel->name }}</td>
                        <td class="px-4 py-3">{{ $hotel->city }}</td>
                        <td class="px-4 py-3 text-amber-500">{{ str_repeat('★', (int) $hotel->star) }}</td>
                        <td class="px-4 py-3">{{ $hotel->distance_to_masjid ? number_format($hotel->distance_to_masjid).' m' : '-' }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$hotel->is_active ? 'green' : 'gray'">{{ $hotel->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @can('update', $hotel)
                                <a href="{{ route('hotels.edit', $hotel) }}" class="text-brand text-sm hover:underline mr-3">Edit</a>
                            @endcan
                            @can('delete', $hotel)
                                <x-delete-form :action="route('hotels.destroy', $hotel)" />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <div class="p-4">{{ $hotels->links() }}</div>
        @else
            <x-empty-state icon="hotel" title="Belum ada hotel"
                message="Tambahkan hotel Mekah dan Madinah untuk dipakai pada paket." />
        @endif
    </x-card>
</x-app-layout>
