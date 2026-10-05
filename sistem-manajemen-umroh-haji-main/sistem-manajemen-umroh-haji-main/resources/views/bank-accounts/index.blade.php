<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Rekening Bank']]" />
    <x-page-header title="Rekening Bank" subtitle="Daftar rekening bank penerimaan pembayaran">
        <x-slot name="actions">
            @can('create', \App\Models\BankAccount::class)
                <x-action-link :href="route('bank-accounts.create')" icon="bank">Tambah Rekening</x-action-link>
            @endcan
        </x-slot>
    </x-page-header>

    <x-card class="mt-6" padding="p-0">
        <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-gray-100">
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama bank…"
                   class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
            <button class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium hover:bg-gray-200">Cari</button>
        </form>

        @if($accounts->total() > 0)
            <x-table :headers="['Bank', 'No. Rekening', 'Atas Nama', 'Cabang', 'Status', '']">
                @foreach($accounts as $account)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $account->bank_name }}</td>
                        <td class="px-4 py-3">{{ $account->account_number }}</td>
                        <td class="px-4 py-3">{{ $account->account_holder }}</td>
                        <td class="px-4 py-3">{{ $account->branch?->name ?? 'Semua Cabang' }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$account->is_active ? 'green' : 'gray'">{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @can('update', $account)
                                <a href="{{ route('bank-accounts.edit', $account) }}" class="text-brand text-sm hover:underline mr-3">Edit</a>
                            @endcan
                            @can('delete', $account)
                                <x-delete-form :action="route('bank-accounts.destroy', $account)" />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <div class="p-4">{{ $accounts->links() }}</div>
        @else
            <x-empty-state icon="bank" title="Belum ada rekening bank"
                message="Tambahkan rekening bank untuk menerima pembayaran jamaah." />
        @endif
    </x-card>
</x-app-layout>
