<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Pendaftaran']]" />
    <x-page-header title="Pendaftaran" subtitle="Booking jamaah ke paket Umroh &amp; Haji">
        <x-slot name="actions">
            @can('create', \App\Models\Registration::class)
                <x-action-link :href="route('registrations.create')" icon="list">Daftarkan Jamaah</x-action-link>
            @endcan
        </x-slot>
    </x-page-header>

    <x-card class="mt-6" padding="p-0">
        <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-gray-100">
            <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                <option value="">Semua Status</option>
                @foreach($statuses as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="package" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                <option value="">Semua Paket</option>
                @foreach($packages as $id => $name)
                    <option value="{{ $id }}" @selected((string) request('package') === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium hover:bg-gray-200">Filter</button>
        </form>

        @if($registrations->total() > 0)
            <x-table :headers="['Kode', 'Jamaah', 'Paket', 'Status', 'Bayar', 'Tgl Daftar', '']">
                @foreach($registrations as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $r->code }}</td>
                        <td class="px-4 py-3">{{ $r->pilgrim?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $r->package?->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$r->status?->badgeColor() ?? 'gray'">{{ $r->status?->label() ?? '-' }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge :color="$r->paymentStatus()->badgeColor()">{{ $r->paymentStatus()->label() }}</x-badge>
                        </td>
                        <td class="px-4 py-3">{{ $r->registered_at ? format_date($r->registered_at) : '-' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('registrations.show', $r) }}" class="text-brand text-sm hover:underline mr-3">Detail</a>
                            @can('update', $r)
                                <a href="{{ route('registrations.edit', $r) }}" class="text-brand text-sm hover:underline mr-3">Edit</a>
                            @endcan
                            @can('delete', $r)
                                <x-delete-form :action="route('registrations.destroy', $r)" />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <div class="p-4">{{ $registrations->links() }}</div>
        @else
            <x-empty-state icon="list" title="Belum ada pendaftaran"
                message="Daftarkan jamaah ke paket yang berstatus terbuka untuk memulai." />
        @endif
    </x-card>
</x-app-layout>
