<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Pembayaran', 'route' => 'payments.index'], ['label' => $registration->code]]" />
    <x-page-header :title="'Tagihan: '.($registration->pilgrim?->name ?? '-')" :subtitle="$registration->code">
        <x-slot name="actions">
            <x-action-link :href="route('registrations.show', $registration)" icon="list" variant="secondary">Detail Pendaftaran</x-action-link>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
        <x-stats-card label="Total Tagihan" :value="currency($registration->totalBilled())" icon="cash" color="blue" />
        <x-stats-card label="Sudah Dibayar" :value="currency($registration->totalPaid())" icon="bank" color="green" />
        <x-stats-card label="Sisa" :value="currency($registration->outstanding())" icon="list" color="amber"
            :sub="$registration->paymentStatus()->label()" />
    </div>

    @if($registration->payments->isNotEmpty())
        <x-card title="Daftar Tagihan" class="mt-4">
            <div class="space-y-4">
                @foreach($registration->payments as $payment)
                    <div class="border border-gray-100 rounded-lg p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="font-medium text-gray-800">{{ $payment->code }}</span>
                                <x-badge :color="$payment->type?->badgeColor() ?? 'gray'">{{ $payment->type?->label() ?? '-' }}</x-badge>
                                <x-badge :color="$payment->status?->badgeColor() ?? 'gray'">{{ $payment->status?->label() ?? '-' }}</x-badge>
                            </div>
                            <div class="text-sm text-gray-500">
                                Jatuh tempo: {{ $payment->due_date ? format_date($payment->due_date) : '-' }}
                                {{-- TODO: tombol cetak kwitansi PDF ditambahkan menyusul. --}}
                            </div>
                        </div>
                        <div class="mt-2 text-sm text-gray-600">
                            Tagihan <span class="font-medium text-gray-800">{{ currency($payment->amount) }}</span> &middot;
                            Dibayar <span class="font-medium text-gray-800">{{ currency($payment->paid_amount) }}</span> &middot;
                            Sisa <span class="font-medium text-gray-800">{{ currency($payment->outstanding()) }}</span>
                        </div>

                        @can('record', $payment)
                            @if($payment->outstanding() > 0)
                                @include('payments._record', ['payment' => $payment, 'bankAccounts' => $bankAccounts])
                            @endif
                        @endcan
                    </div>
                @endforeach
            </div>
        </x-card>
    @else
        @can('create', \App\Models\Payment::class)
            <x-card title="Buat Tagihan" class="mt-4">
                @include('payments._generate', ['registration' => $registration])
            </x-card>
        @else
            <x-card class="mt-4">
                <x-empty-state icon="cash" title="Belum ada tagihan"
                    message="Tagihan untuk pendaftaran ini belum dibuat." />
            </x-card>
        @endcan
    @endif
</x-app-layout>
