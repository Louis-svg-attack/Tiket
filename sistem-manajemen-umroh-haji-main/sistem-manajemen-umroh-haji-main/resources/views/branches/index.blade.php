<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Cabang']]" />
    <x-page-header title="Cabang" subtitle="Daftar cabang travel">
        <x-slot name="actions">
            @can('create', \App\Models\Branch::class)
                <x-action-link :href="route('branches.create')" icon="building">Tambah Cabang</x-action-link>
            @endcan
        </x-slot>
    </x-page-header>

    <x-card class="mt-6" padding="p-0">
        <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-gray-100">
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama atau kode cabang…"
                   class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
            <button class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium hover:bg-gray-200">Cari</button>
        </form>

        @if($branches->total() > 0)
            <x-table :headers="['Kode', 'Nama', 'Kota', 'Telepon', 'Status', '']">
                @foreach($branches as $branch)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $branch->code }}</td>
                        <td class="px-4 py-3">
                            {{ $branch->name }}
                            @if($branch->is_head_office)
                                <x-badge color="blue">Pusat</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $branch->city ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $branch->phone ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$branch->is_active ? 'green' : 'gray'">{{ $branch->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @can('update', $branch)
                                <a href="{{ route('branches.edit', $branch) }}" class="text-brand text-sm hover:underline mr-3">Edit</a>
                            @endcan
                            @can('delete', $branch)
                                <x-delete-form :action="route('branches.destroy', $branch)" />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <div class="p-4">{{ $branches->links() }}</div>
        @else
            <x-empty-state icon="building" title="Belum ada cabang"
                message="Tambahkan cabang travel untuk mulai mengelola data." />
        @endif
    </x-card>
</x-app-layout>
