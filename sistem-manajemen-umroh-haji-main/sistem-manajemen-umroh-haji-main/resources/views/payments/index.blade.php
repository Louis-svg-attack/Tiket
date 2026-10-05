<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Pembayaran']]" />
    <x-page-header title="Pembayaran &amp; Cicilan" subtitle="Daftar tagihan jamaah">
        <x-slot name="actions">
            @can('create', \App\Models\Payment::class)
                <x-action-link :href="route('payments.generate-massal')" icon="cash">Buat Tagihan Massal</x-action-link>
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
            <select name="type" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                <option value="">Semua Jenis</option>
                @foreach($types as $val => $label)
                    <option value="{{ $val }}" @selected(request('type') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium hover:bg-gray-200">Filter</button>
        </form>

        @if($bills->total() > 0)
            <x-table :headers="['Kode', 'Jamaah', 'Paket', 'Jenis', 'Tagihan', 'Dibayar', 'Status', 'Jatuh Tempo', '']">
                @foreach($bills as $bill)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $bill->code }}</td>
                        <td class="px-4 py-3">{{ $bill->registration?->pilgrim?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $bill->registration?->package?->name ?? '-' }}</td>
                        <td class="px-4 py-3"><x-badge :color="$bill->type?->badgeColor() ?? 'gray'">{{ $bill->type?->label() ?? '-' }}</x-badge></td>
                        <td class="px-4 py-3">{{ currency($bill->amount) }}</td>
                        <td class="px-4 py-3">{{ currency($bill->paid_amount) }}</td>
                        <td class="px-4 py-3"><x-badge :color="$bill->status?->badgeColor() ?? 'gray'">{{ $bill->status?->label() ?? '-' }}</x-badge></td>
                        <td class="px-4 py-3">{{ $bill->due_date ? format_date($bill->due_date) : '-' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @if($bill->registration)
                                <a href="{{ route('payments.bills', $bill->registration_id) }}" class="text-brand text-sm hover:underline">Detail</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <div class="p-4">{{ $bills->links() }}</div>
        @else
            <x-empty-state icon="cash" title="Belum ada tagihan"
                message="Buat tagihan dari halaman pendaftaran atau lewat tagihan massal per paket." />
        @endif
    </x-card>
</x-app-layout>
