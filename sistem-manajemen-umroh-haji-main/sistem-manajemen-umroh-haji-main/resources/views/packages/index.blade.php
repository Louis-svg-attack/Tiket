<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Paket']]" />
    <x-page-header title="Paket" subtitle="Daftar paket Umroh &amp; Haji">
        <x-slot name="actions">
            @can('create', \App\Models\Package::class)
                <x-action-link :href="route('packages.create')" icon="package">Tambah Paket</x-action-link>
            @endcan
        </x-slot>
    </x-page-header>

    <x-card class="mt-6" padding="p-0">
        <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-gray-100">
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama / kode…"
                   class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
            <select name="type" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                <option value="">Semua Jenis</option>
                @foreach(\App\Enums\PackageType::options() as $val => $label)
                    <option value="{{ $val }}" @selected(request('type')===$val)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                <option value="">Semua Status</option>
                @foreach(\App\Enums\PackageStatus::options() as $val => $label)
                    <option value="{{ $val }}" @selected(request('status')===$val)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium hover:bg-gray-200">Cari</button>
        </form>

        @if($packages->total() > 0)
            <x-table :headers="['Kode / Nama', 'Jenis', 'Harga', 'Kuota', 'Berangkat', 'Status', '']">
                @foreach($packages as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $p->name }}</div>
                            <div class="text-xs text-gray-400">{{ $p->code ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge :color="$p->type?->badgeColor() ?? 'gray'">{{ $p->type?->label() ?? '-' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ currency($p->price) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $p->remainingQuota() }}/{{ $p->quota }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $p->departure_date ? format_date($p->departure_date) : '-' }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$p->status?->badgeColor() ?? 'gray'">{{ $p->status?->label() ?? '-' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('packages.show', $p) }}" class="text-brand text-sm hover:underline mr-3">Detail</a>
                            @can('update', $p)
                                <a href="{{ route('packages.edit', $p) }}" class="text-brand text-sm hover:underline mr-3">Edit</a>
                            @endcan
                            @can('delete', $p)
                                <x-delete-form :action="route('packages.destroy', $p)" />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <div class="p-4">{{ $packages->links() }}</div>
        @else
            <x-empty-state icon="package" title="Belum ada paket"
                message="Tambahkan paket Umroh atau Haji untuk mulai menerima pendaftaran." />
        @endif
    </x-card>
</x-app-layout>
