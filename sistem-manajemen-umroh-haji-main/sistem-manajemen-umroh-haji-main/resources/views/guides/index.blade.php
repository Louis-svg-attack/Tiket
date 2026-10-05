<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Muthawif']]" />
    <x-page-header title="Muthawif / Pembimbing" subtitle="Daftar muthawif &amp; pembimbing ibadah">
        <x-slot name="actions">
            @can('create', \App\Models\Guide::class)
                <x-action-link :href="route('guides.create')" icon="guide">Tambah Muthawif</x-action-link>
            @endcan
        </x-slot>
    </x-page-header>

    <x-card class="mt-6" padding="p-0">
        <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-gray-100">
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama / kode…"
                   class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
            <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                <option value="">Semua Status</option>
                <option value="active" @selected(request('status')==='active')>Aktif</option>
                <option value="inactive" @selected(request('status')==='inactive')>Nonaktif</option>
            </select>
            <button class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium hover:bg-gray-200">Cari</button>
        </form>

        @if($guides->total() > 0)
            <x-table :headers="['Kode / Nama', 'Gender', 'Kontak', 'Spesialisasi', 'Akun', 'Status', '']">
                @foreach($guides as $guide)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $guide->name }}</div>
                            <div class="text-xs text-gray-400">{{ $guide->code ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge :color="$guide->gender?->badgeColor() ?? 'gray'">{{ $guide->gender?->label() ?? '-' }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            @if($guide->phone)
                                <a href="{{ wa_link($guide->phone) }}" target="_blank" class="text-brand text-sm hover:underline">{{ $guide->phone }}</a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                            <div class="text-xs text-gray-400">{{ $guide->email ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $guide->specialization ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$guide->user_id ? 'green' : 'gray'">{{ $guide->user_id ? 'Aktif' : '—' }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge :color="$guide->is_active ? 'green' : 'gray'">{{ $guide->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @can('update', $guide)
                                <a href="{{ route('guides.edit', $guide) }}" class="text-brand text-sm hover:underline mr-3">Edit</a>
                            @endcan
                            @can('delete', $guide)
                                <x-delete-form :action="route('guides.destroy', $guide)" />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <div class="p-4">{{ $guides->links() }}</div>
        @else
            <x-empty-state icon="guide" title="Belum ada muthawif"
                message="Tambahkan muthawif atau pembimbing untuk ditugaskan pada paket dan manasik." />
        @endif
    </x-card>
</x-app-layout>
