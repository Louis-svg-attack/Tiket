@php($isEdit = $account->exists)
<form method="POST" action="{{ $isEdit ? route('bank-accounts.update', $account) : route('bank-accounts.store') }}" class="mt-6 max-w-2xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <x-card>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-select name="branch_id" label="Cabang"
                :options="$branches->pluck('name', 'id')->all()"
                :selected="$account->branch_id"
                placeholder="Semua Cabang" class="sm:col-span-2" />
            <x-input name="bank_name" label="Nama Bank" :value="$account->bank_name" required />
            <x-input name="account_number" label="No. Rekening" :value="$account->account_number" required />
            <x-input name="account_holder" label="Atas Nama" :value="$account->account_holder" required />
            <x-input name="branch_office" label="Kantor Cabang Bank" :value="$account->branch_office" />
            <div class="flex items-center gap-2 pt-7">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $account->is_active ?? true))
                       class="rounded border-gray-300 text-brand focus:ring-brand">
                <label for="is_active" class="text-sm text-gray-700">Aktif</label>
            </div>
        </div>
    </x-card>

    <div class="mt-4 flex items-center gap-3">
        <button class="px-5 py-2.5 rounded-lg bg-brand text-white font-medium hover:bg-brand-dark">
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}
        </button>
        <a href="{{ route('bank-accounts.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</a>
    </div>
</form>
