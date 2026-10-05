<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Maskapai']]" />
    <x-page-header title="Maskapai" subtitle="Daftar maskapai penerbangan">
        <x-slot name="actions">
            @can('create', \App\Models\Airline::class)
                <x-action-link :href="route('airlines.create')" icon="plane">Tambah Maskapai</x-action-link>
            @endcan
        </x-slot>
    </x-page-header>

    <x-card class="mt-6" padding="p-0">
        <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-gray-100">
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama maskapai…"
                   class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
            <button class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium hover:bg-gray-200">Cari</button>
        </form>

        @if($airlines->total() > 0)
            <x-table :headers="['Nama', 'Kode', 'Transit', 'Status', '']">
                @foreach($airlines as $airline)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $airline->name }}</td>
                        <td class="px-4 py-3">{{ $airline->code ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $airline->transit ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$airline->is_active ? 'green' : 'gray'">{{ $airline->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @can('update', $airline)
                                <a href="{{ route('airlines.edit', $airline) }}" class="text-brand text-sm hover:underline mr-3">Edit</a>
                            @endcan
                            @can('delete', $airline)
                                <x-delete-form :action="route('airlines.destroy', $airline)" />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <div class="p-4">{{ $airlines->links() }}</div>
        @else
            <x-empty-state icon="plane" title="Belum ada maskapai"
                message="Tambahkan maskapai penerbangan untuk dipakai pada paket." />
        @endif
    </x-card>
</x-app-layout>
