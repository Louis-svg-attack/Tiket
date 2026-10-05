<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Perlengkapan']]" />
    <x-page-header title="Perlengkapan" subtitle="Distribusi perlengkapan jamaah">
        <x-slot name="actions">
            @can('create', \App\Models\EquipmentDistribution::class)
                <x-action-link :href="route('equipment.create')" icon="box">Tambah Perlengkapan</x-action-link>
            @endcan
        </x-slot>
    </x-page-header>

    <x-card class="mt-6" padding="p-0">
        @if($equipment->total() > 0)
            <x-table :headers="['Jamaah', 'Item', 'Ukuran', 'Qty', 'Status', 'Diserahkan', '']">
                @foreach($equipment as $e)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $e->registration?->pilgrim?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $e->item }}</td>
                        <td class="px-4 py-3">{{ $e->size ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $e->quantity }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$e->status?->badgeColor() ?? 'gray'">{{ $e->status?->label() ?? '-' }}</x-badge>
                        </td>
                        <td class="px-4 py-3">{{ $e->handed_at ? format_date($e->handed_at) : '-' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @can('update', $e)
                                <a href="{{ route('equipment.edit', $e) }}" class="text-brand text-sm hover:underline mr-3">Edit</a>
                            @endcan
                            @can('delete', $e)
                                <x-delete-form :action="route('equipment.destroy', $e)" />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <div class="p-4">{{ $equipment->links() }}</div>
        @else
            <x-empty-state icon="box" title="Belum ada perlengkapan"
                message="Catat distribusi koper, seragam, ihram, dan tas paspor untuk jamaah." />
        @endif
    </x-card>
</x-app-layout>
