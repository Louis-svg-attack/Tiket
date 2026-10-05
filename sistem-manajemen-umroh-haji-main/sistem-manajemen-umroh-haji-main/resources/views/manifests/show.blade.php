<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Manifest', 'route' => 'manifests.index'], ['label' => $package->name]]" />
    <x-page-header :title="'Manifest: '.$package->name" :subtitle="$package->code ?? '-'" />

    {{-- TODO: PDF manifest export (placeholder). --}}

    <x-card class="mt-6" padding="p-0">
        @if($registrations->count() > 0)
            <form method="POST" action="{{ route('manifests.update', $package) }}">
                @csrf
                @method('PUT')
                <x-table :headers="['Jamaah', 'No. Kamar', 'Tipe Kamar', 'No. Bus', 'No. Kursi', 'Grup Mahram']">
                    @foreach($registrations as $r)
                        @php($entry = $r->manifestEntry)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $r->pilgrim?->name ?? '-' }}
                                <span class="block text-xs text-gray-400">{{ $r->code }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <input name="rows[{{ $r->id }}][room_number]" value="{{ old('rows.'.$r->id.'.room_number', $entry?->room_number) }}"
                                    class="w-24 rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                            </td>
                            <td class="px-4 py-3">
                                <select name="rows[{{ $r->id }}][room_type]"
                                    class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                                    <option value="">—</option>
                                    @foreach(['quad' => 'Quad', 'triple' => 'Triple', 'double' => 'Double'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('rows.'.$r->id.'.room_type', $entry?->room_type) === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3">
                                <input name="rows[{{ $r->id }}][bus_number]" value="{{ old('rows.'.$r->id.'.bus_number', $entry?->bus_number) }}"
                                    class="w-20 rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                            </td>
                            <td class="px-4 py-3">
                                <input name="rows[{{ $r->id }}][seat_number]" value="{{ old('rows.'.$r->id.'.seat_number', $entry?->seat_number) }}"
                                    class="w-20 rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                            </td>
                            <td class="px-4 py-3">
                                <input name="rows[{{ $r->id }}][mahram_group]" value="{{ old('rows.'.$r->id.'.mahram_group', $entry?->mahram_group) }}"
                                    class="w-24 rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                            </td>
                        </tr>
                    @endforeach
                </x-table>
                <div class="p-4 flex items-center gap-3">
                    <button class="px-5 py-2.5 rounded-lg bg-brand text-white font-medium hover:bg-brand-dark">Simpan Manifest</button>
                    <a href="{{ route('manifests.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Kembali</a>
                </div>
            </form>
        @else
            <x-empty-state icon="bus" title="Belum ada jamaah aktif"
                message="Daftarkan jamaah ke paket ini untuk menyusun manifest." />
        @endif
    </x-card>
</x-app-layout>
