{{-- Inline form to record a payment against a single bill (staff only). --}}
<form method="POST" action="{{ route('payments.record', $payment) }}" class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 bg-gray-50 rounded-lg p-3">
    @csrf
    <x-input name="amount" label="Jumlah Bayar" type="number" :value="old('amount', $payment->outstanding())" required />
    <x-input name="paid_at" label="Tanggal Bayar" type="date" :value="old('paid_at', now()->format('Y-m-d'))" />
    <x-select name="method" label="Metode" :options="['transfer' => 'Transfer', 'cash' => 'Tunai', 'edc' => 'EDC']"
        :selected="old('method', $payment->method)" placeholder="— Pilih —" />
    <x-select name="bank_account_id" label="Rekening Bank" :options="$bankAccounts"
        :selected="old('bank_account_id', $payment->bank_account_id)" placeholder="— Pilih —" />
    <x-input name="note" label="Catatan" :value="old('note')" class="sm:col-span-2" />
    <div class="sm:col-span-2">
        <button class="px-4 py-2 rounded-lg bg-brand text-white text-sm font-medium hover:bg-brand-dark">Catat Pembayaran</button>
    </div>
</form>
