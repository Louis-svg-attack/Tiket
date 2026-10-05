{{-- Form to generate a DP + installment schedule for this registration. --}}
<form method="POST" action="{{ route('payments.generate', $registration) }}">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-input name="dp_amount" label="Uang Muka / DP (opsional)" type="number"
            :value="old('dp_amount', $registration->package?->down_payment)" hint="Kosongkan jika tanpa DP." />
        <x-input name="installments" label="Jumlah Cicilan" type="number" :value="old('installments', 3)" required />
        <x-input name="first_due" label="Jatuh Tempo Cicilan Pertama" type="date"
            :value="old('first_due', now()->addMonth()->format('Y-m-d'))" class="sm:col-span-2" />
    </div>
    <div class="mt-4">
        <button class="px-5 py-2.5 rounded-lg bg-brand text-white font-medium hover:bg-brand-dark">Buat Tagihan</button>
    </div>
</form>
