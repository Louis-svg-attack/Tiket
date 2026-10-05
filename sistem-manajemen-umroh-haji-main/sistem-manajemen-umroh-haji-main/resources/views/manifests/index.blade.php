<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Manifest']]" />
    <x-page-header title="Manifest &amp; Rooming" subtitle="Pengaturan kamar, bus, dan kursi jamaah per paket" />

    <x-card class="mt-6" padding="p-0">
        @if($packages->total() > 0)
            <x-table :headers="['Paket', 'Jenis', 'Jumlah Jamaah', 'Berangkat', '']">
                @foreach($packages as $package)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $package->name }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$package->type?->badgeColor() ?? 'gray'">{{ $package->type?->label() ?? '-' }}</x-badge>
                        </td>
                        <td class="px-4 py-3">{{ $package->active_registrations_count }}</td>
                        <td class="px-4 py-3">{{ $package->departure_date ? format_date($package->departure_date) : '-' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('manifests.show', $package) }}" class="text-brand text-sm hover:underline">Kelola Manifest</a>
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <div class="p-4">{{ $packages->links() }}</div>
        @else
            <x-empty-state icon="bus" title="Belum ada paket"
                message="Buat paket dan daftarkan jamaah untuk mengelola manifest." />
        @endif
    </x-card>
</x-app-layout>
