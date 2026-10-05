<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Pembayaran', 'route' => 'payments.index'], ['label' => 'Tagihan Massal']]" />
    <x-page-header title="Buat Tagihan Massal" subtitle="Buat skema DP &amp; cicilan untuk seluruh jamaah aktif sebuah paket" />

    <form method="POST" action="{{ route('payments.generate-massal') }}" class="mt-6 max-w-2xl">
        @csrf
        <x-card>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-select name="package_id" label="Paket" :options="$packages" :selected="old('package_id')"
                    required class="sm:col-span-2" placeholder="— Pilih Paket —"
                    hint="Hanya pendaftaran aktif yang belum memiliki tagihan yang akan diproses." />
                <x-input name="dp_amount" label="Uang Muka / DP (opsional)" type="number" :value="old('dp_amount')"
                    hint="Kosongkan jika tanpa DP." />
                <x-input name="installments" label="Jumlah Cicilan" type="number" :value="old('installments', 3)" required />
                <x-input name="first_due" label="Jatuh Tempo Cicilan Pertama" type="date"
                    :value="old('first_due', now()->addMonth()->format('Y-m-d'))" class="sm:col-span-2" />
            </div>
        </x-card>

        <div class="mt-4 flex items-center gap-3">
            <button class="px-5 py-2.5 rounded-lg bg-brand text-white font-medium hover:bg-brand-dark">Buat Tagihan Massal</button>
            <a href="{{ route('payments.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</a>
        </div>
    </form>
</x-app-layout>
