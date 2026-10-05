@php($isEdit = $agent->exists)
<form method="POST" action="{{ $isEdit ? route('agents.update', $agent) : route('agents.store') }}" class="mt-6 max-w-2xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <x-card title="Data Agen">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-select name="branch_id" label="Cabang"
                :options="$branches->pluck('name', 'id')->all()"
                :selected="$agent->branch_id"
                placeholder="Pilih Cabang" class="sm:col-span-2" />
            <x-input name="name" label="Nama" :value="$agent->name" required class="sm:col-span-2" />
            <x-input name="phone" label="Telepon / WhatsApp" :value="$agent->phone" />
            <x-input name="email" label="Email" type="email" :value="$agent->email" />
            <x-input name="commission_rate" label="Komisi (%)" type="number" step="0.01" :value="$agent->commission_rate" />
            <div class="flex items-center gap-2 pt-7">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $agent->is_active ?? true))
                       class="rounded border-gray-300 text-brand focus:ring-brand">
                <label for="is_active" class="text-sm text-gray-700">Aktif</label>
            </div>
            <x-input name="address" label="Alamat" type="textarea" :value="$agent->address" class="sm:col-span-2" />
        </div>
    </x-card>

    @if(! $agent->user_id)
        <x-card title="Akun Login" class="mt-4" x-data="{ create: {{ old('create_account') ? 'true' : 'false' }} }">
            <div class="flex items-center gap-2">
                <input type="hidden" name="create_account" value="0">
                <input type="checkbox" id="create_account" name="create_account" value="1" x-model="create"
                       @checked(old('create_account'))
                       class="rounded border-gray-300 text-brand focus:ring-brand">
                <label for="create_account" class="text-sm text-gray-700">Buatkan akun login</label>
            </div>
            <div class="mt-4" x-show="create" x-cloak>
                <x-input name="account_email" label="Email Akun" type="email" :value="old('account_email', $agent->email)"
                    hint="Kosongkan untuk memakai email agen di atas" class="max-w-sm" />
            </div>
        </x-card>
    @endif

    <div class="mt-4 flex items-center gap-3">
        <button class="px-5 py-2.5 rounded-lg bg-brand text-white font-medium hover:bg-brand-dark">
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}
        </button>
        <a href="{{ route('agents.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</a>
    </div>
</form>
