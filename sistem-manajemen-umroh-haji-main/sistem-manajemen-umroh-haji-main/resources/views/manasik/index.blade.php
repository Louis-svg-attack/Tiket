<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Manasik']]" />
    <x-page-header title="Manasik" subtitle="Jadwal bimbingan manasik &amp; absensi jamaah">
        <x-slot name="actions">
            @can('create', \App\Models\ManasikSchedule::class)
                <x-action-link :href="route('manasik.create')" icon="calendar">Tambah Jadwal</x-action-link>
            @endcan
        </x-slot>
    </x-page-header>

    <x-card class="mt-6" padding="p-0">
        @if($schedules->total() > 0)
            <x-table :headers="['Tanggal', 'Judul', 'Paket', 'Pembimbing', 'Kehadiran', '']">
                @foreach($schedules as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-800">{{ $s->date ? format_date($s->date) : '-' }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $s->title }}</td>
                        <td class="px-4 py-3">{{ $s->package?->name ?? 'Umum' }}</td>
                        <td class="px-4 py-3">{{ $s->guide?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $s->presentCount() }} / {{ $s->attendances_count }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('manasik.show', $s) }}" class="text-brand text-sm hover:underline mr-3">Absensi</a>
                            @can('update', $s)
                                <a href="{{ route('manasik.edit', $s) }}" class="text-brand text-sm hover:underline mr-3">Edit</a>
                            @endcan
                            @can('delete', $s)
                                <x-delete-form :action="route('manasik.destroy', $s)" />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <div class="p-4">{{ $schedules->links() }}</div>
        @else
            <x-empty-state icon="calendar" title="Belum ada jadwal manasik"
                message="Tambahkan jadwal bimbingan manasik untuk paket atau jamaah cabang." />
        @endif
    </x-card>
</x-app-layout>
