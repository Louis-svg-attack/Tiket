<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Agen']]" />
    <x-page-header title="Agen / Marketing" subtitle="Daftar agen &amp; marketing">
        <x-slot name="actions">
            @can('create', \App\Models\Agent::class)
                <x-action-link :href="route('agents.create')" icon="user">Tambah Agen</x-action-link>
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

        @if($agents->total() > 0)
            <x-table :headers="['Kode / Nama', 'Kontak', 'Komisi', 'Akun', 'Status', '']">
                @foreach($agents as $agent)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $agent->name }}</div>
                            <div class="text-xs text-gray-400">{{ $agent->code ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if($agent->phone)
                                <a href="{{ wa_link($agent->phone) }}" target="_blank" class="text-brand text-sm hover:underline">{{ $agent->phone }}</a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                            <div class="text-xs text-gray-400">{{ $agent->email ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ number_format((float) $agent->commission_rate, 2) }}%</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$agent->user_id ? 'green' : 'gray'">{{ $agent->user_id ? 'Aktif' : '—' }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge :color="$agent->is_active ? 'green' : 'gray'">{{ $agent->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @can('update', $agent)
                                <a href="{{ route('agents.edit', $agent) }}" class="text-brand text-sm hover:underline mr-3">Edit</a>
                            @endcan
                            @can('delete', $agent)
                                <x-delete-form :action="route('agents.destroy', $agent)" />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <div class="p-4">{{ $agents->links() }}</div>
        @else
            <x-empty-state icon="user" title="Belum ada agen"
                message="Tambahkan agen atau marketing untuk mulai mengelola pendaftaran jamaah." />
        @endif
    </x-card>
</x-app-layout>
