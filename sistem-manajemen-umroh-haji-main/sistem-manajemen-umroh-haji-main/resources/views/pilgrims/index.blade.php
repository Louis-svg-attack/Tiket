<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Jamaah']]" />
    <x-page-header title="Jamaah" subtitle="Daftar jamaah umroh &amp; haji">
        <x-slot name="actions">
            @can('create', \App\Models\Pilgrim::class)
                <x-action-link :href="route('pilgrims.create')" icon="users">Tambah Jamaah</x-action-link>
            @endcan
        </x-slot>
    </x-page-header>

    <x-card class="mt-6" padding="p-0">
        <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-gray-100">
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama / NIK / paspor…"
                   class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
            <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                <option value="">Semua Status</option>
                <option value="active" @selected(request('status')==='active')>Aktif</option>
                <option value="inactive" @selected(request('status')==='inactive')>Nonaktif</option>
            </select>
            <button class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium hover:bg-gray-200">Cari</button>
        </form>

        @if($pilgrims->total() > 0)
            <x-table :headers="['Kode / Nama', 'Gender', 'NIK', 'Kontak', 'Paspor', 'Agen', '']">
                @foreach($pilgrims as $pilgrim)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('pilgrims.show', $pilgrim) }}" class="font-medium text-gray-800 hover:text-brand hover:underline">{{ $pilgrim->name }}</a>
                            <div class="text-xs text-gray-400">{{ $pilgrim->code ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge :color="$pilgrim->gender?->badgeColor() ?? 'gray'">{{ $pilgrim->gender?->label() ?? '-' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $pilgrim->nik ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($pilgrim->phone)
                                <a href="{{ wa_link($pilgrim->phone) }}" target="_blank" class="text-brand text-sm hover:underline">{{ $pilgrim->phone }}</a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                            <div class="text-xs text-gray-400">{{ $pilgrim->email ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-600">{{ $pilgrim->passport_number ?? '-' }}</div>
                            @if($pilgrim->passport_expired_at)
                                @if($pilgrim->passportExpiringSoon())
                                    <x-badge color="red">Exp {{ format_date($pilgrim->passport_expired_at, 'M Y') }}</x-badge>
                                @else
                                    <div class="text-xs text-gray-400">Exp {{ format_date($pilgrim->passport_expired_at, 'M Y') }}</div>
                                @endif
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $pilgrim->agent?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('pilgrims.show', $pilgrim) }}" class="text-brand text-sm hover:underline mr-3">Lihat</a>
                            @can('update', $pilgrim)
                                <a href="{{ route('pilgrims.edit', $pilgrim) }}" class="text-brand text-sm hover:underline mr-3">Edit</a>
                            @endcan
                            @can('delete', $pilgrim)
                                <x-delete-form :action="route('pilgrims.destroy', $pilgrim)" />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <div class="p-4">{{ $pilgrims->links() }}</div>
        @else
            <x-empty-state icon="users" title="Belum ada jamaah"
                message="Tambahkan data jamaah untuk mulai mengelola pendaftaran paket." />
        @endif
    </x-card>
</x-app-layout>
