<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Pendaftaran', 'route' => 'registrations.index'], ['label' => $registration->code]]" />
    <x-page-header :title="$registration->pilgrim?->name ?? 'Pendaftaran'" :subtitle="$registration->code">
        <x-slot name="actions">
            {{-- TODO: tombol cetak PDF pendaftaran ditambahkan menyusul --}}
            @can('update', $registration)
                <x-action-link :href="route('registrations.edit', $registration)" icon="list">Edit</x-action-link>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
        <x-stats-card label="Total Tagihan" :value="currency($registration->totalBilled())" icon="cash" color="blue" />
        <x-stats-card label="Sudah Dibayar" :value="currency($registration->totalPaid())" icon="bank" color="green" />
        <x-stats-card label="Sisa" :value="currency($registration->outstanding())" icon="list" color="amber"
            :sub="$registration->paymentStatus()->label()" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        <x-card title="Informasi Pendaftaran">
            <dl class="divide-y divide-gray-100 text-sm">
                <div class="flex justify-between py-2"><dt class="text-gray-500">Status</dt>
                    <dd><x-badge :color="$registration->status?->badgeColor() ?? 'gray'">{{ $registration->status?->label() ?? '-' }}</x-badge></dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">Tanggal Daftar</dt>
                    <dd class="text-gray-800">{{ $registration->registered_at ? format_date($registration->registered_at) : '-' }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">Harga Paket</dt>
                    <dd class="text-gray-800">{{ currency($registration->total_price) }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">Agen</dt>
                    <dd class="text-gray-800">{{ $registration->agent?->name ?? '-' }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">Pembimbing</dt>
                    <dd class="text-gray-800">{{ $registration->guide?->name ?? '-' }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">Cabang</dt>
                    <dd class="text-gray-800">{{ $registration->branch?->name ?? '-' }}</dd></div>
            </dl>
            @if($registration->notes)
                <p class="mt-3 text-sm text-gray-600 whitespace-pre-line">{{ $registration->notes }}</p>
            @endif

            @can('update', $registration)
                <form method="POST" action="{{ route('registrations.status', $registration) }}" class="mt-4 flex items-end gap-2 pt-4 border-t border-gray-100">
                    @csrf
                    @method('PUT')
                    <x-select name="status" label="Ubah Status" :options="$statuses" :selected="$registration->status?->value"
                        :placeholder="false" class="flex-1" />
                    <button class="px-4 py-2 rounded-lg bg-brand text-white text-sm font-medium hover:bg-brand-dark">Simpan</button>
                </form>
            @endcan
        </x-card>

        <x-card title="Jamaah &amp; Paket">
            <dl class="divide-y divide-gray-100 text-sm">
                <div class="flex justify-between py-2"><dt class="text-gray-500">Jamaah</dt>
                    <dd class="text-gray-800">{{ $registration->pilgrim?->name ?? '-' }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">No. Paspor</dt>
                    <dd class="text-gray-800">{{ $registration->pilgrim?->passport_number ?? '-' }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">Telepon</dt>
                    <dd class="text-gray-800">{{ $registration->pilgrim?->phone ?? '-' }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">Paket</dt>
                    <dd class="text-gray-800">{{ $registration->package?->name ?? '-' }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">Jenis Paket</dt>
                    <dd><x-badge :color="$registration->package?->type?->badgeColor() ?? 'gray'">{{ $registration->package?->type?->label() ?? '-' }}</x-badge></dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-500">Tanggal Berangkat</dt>
                    <dd class="text-gray-800">{{ $registration->package?->departure_date ? format_date($registration->package->departure_date) : '-' }}</dd></div>
            </dl>
        </x-card>
    </div>

    <x-card title="Tagihan &amp; Pembayaran" class="mt-4" padding="p-0">
        <x-slot name="actions">
            @can('view', $registration)
                <a href="{{ route('payments.bills', $registration) }}" class="text-brand text-sm hover:underline">Kelola Tagihan</a>
            @endcan
        </x-slot>

        @if($registration->payments->isNotEmpty())
            <x-table :headers="['Kode', 'Jenis', 'Tagihan', 'Dibayar', 'Status', 'Jatuh Tempo']">
                @foreach($registration->payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $payment->code }}</td>
                        <td class="px-4 py-3"><x-badge :color="$payment->type?->badgeColor() ?? 'gray'">{{ $payment->type?->label() ?? '-' }}</x-badge></td>
                        <td class="px-4 py-3">{{ currency($payment->amount) }}</td>
                        <td class="px-4 py-3">{{ currency($payment->paid_amount) }}</td>
                        <td class="px-4 py-3"><x-badge :color="$payment->status?->badgeColor() ?? 'gray'">{{ $payment->status?->label() ?? '-' }}</x-badge></td>
                        <td class="px-4 py-3">{{ $payment->due_date ? format_date($payment->due_date) : '-' }}</td>
                    </tr>
                @endforeach
            </x-table>
        @else
            <x-empty-state icon="cash" title="Belum ada tagihan"
                message="Buat tagihan DP &amp; cicilan dari halaman kelola tagihan." />
        @endif
    </x-card>
</x-app-layout>
