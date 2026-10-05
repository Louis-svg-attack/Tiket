@php($isEdit = $pilgrim->exists)
<form method="POST" action="{{ $isEdit ? route('pilgrims.update', $pilgrim) : route('pilgrims.store') }}" class="mt-6 max-w-3xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <x-card title="Penempatan">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-select name="branch_id" label="Cabang"
                :options="$branches->pluck('name', 'id')->all()"
                :selected="$pilgrim->branch_id"
                placeholder="Pilih Cabang" />
            @unless(auth()->user()?->isAgent())
                <x-select name="agent_id" label="Agen / Referral"
                    :options="$agents->pluck('name', 'id')->all()"
                    :selected="$pilgrim->agent_id"
                    placeholder="Tanpa Agen" />
            @endunless
        </div>
    </x-card>

    <x-card title="Data Pribadi" class="mt-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input name="name" label="Nama Lengkap" :value="$pilgrim->name" required class="sm:col-span-2" />
            <x-select name="gender" label="Jenis Kelamin" :options="\App\Enums\Gender::options()" :selected="$pilgrim->gender?->value" required />
            <x-input name="nik" label="NIK" :value="$pilgrim->nik" />
            <x-input name="birth_place" label="Tempat Lahir" :value="$pilgrim->birth_place" />
            <x-input name="birth_date" label="Tanggal Lahir" type="date" :value="optional($pilgrim->birth_date)->format('Y-m-d')" />
            <x-input name="blood_type" label="Golongan Darah" :value="$pilgrim->blood_type" hint="A / B / AB / O" />
            <x-input name="phone" label="Telepon / WhatsApp" :value="$pilgrim->phone" />
            <x-input name="email" label="Email" type="email" :value="$pilgrim->email" />
            <x-input name="city" label="Kota" :value="$pilgrim->city" />
            <x-input name="address" label="Alamat" type="textarea" :value="$pilgrim->address" class="sm:col-span-2" />
        </div>
    </x-card>

    <x-card title="Paspor" class="mt-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input name="passport_number" label="Nomor Paspor" :value="$pilgrim->passport_number" />
            <x-input name="passport_issued_place" label="Tempat Penerbitan" :value="$pilgrim->passport_issued_place" />
            <x-input name="passport_issued_at" label="Tanggal Terbit" type="date" :value="optional($pilgrim->passport_issued_at)->format('Y-m-d')" />
            <x-input name="passport_expired_at" label="Tanggal Kadaluarsa" type="date" :value="optional($pilgrim->passport_expired_at)->format('Y-m-d')" />
        </div>
    </x-card>

    <x-card title="Mahram &amp; Kontak Darurat" class="mt-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input name="mahram_name" label="Nama Mahram" :value="$pilgrim->mahram_name" />
            <x-input name="mahram_relation" label="Hubungan Mahram" :value="$pilgrim->mahram_relation" hint="Mis. Suami, Ayah, Anak" />
            <x-input name="emergency_contact_name" label="Nama Kontak Darurat" :value="$pilgrim->emergency_contact_name" />
            <x-input name="emergency_contact_phone" label="Telepon Kontak Darurat" :value="$pilgrim->emergency_contact_phone" />
        </div>
    </x-card>

    <x-card title="Kesehatan" class="mt-4">
        <div class="grid grid-cols-1 gap-4">
            <x-input name="health_notes" label="Catatan Kesehatan" type="textarea" :value="$pilgrim->health_notes"
                hint="Riwayat penyakit, alergi, kebutuhan khusus" />
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $pilgrim->is_active ?? true))
                       class="rounded border-gray-300 text-brand focus:ring-brand">
                <label for="is_active" class="text-sm text-gray-700">Aktif</label>
            </div>
        </div>
    </x-card>

    @if(! $pilgrim->user_id)
        <x-card title="Akun Login" class="mt-4" x-data="{ create: {{ old('create_account') ? 'true' : 'false' }} }">
            <div class="flex items-center gap-2">
                <input type="hidden" name="create_account" value="0">
                <input type="checkbox" id="create_account" name="create_account" value="1" x-model="create"
                       @checked(old('create_account'))
                       class="rounded border-gray-300 text-brand focus:ring-brand">
                <label for="create_account" class="text-sm text-gray-700">Buatkan akun login</label>
            </div>
            <div class="mt-4" x-show="create" x-cloak>
                <x-input name="account_email" label="Email Akun" type="email" :value="old('account_email', $pilgrim->email)"
                    hint="Kosongkan untuk memakai email jamaah di atas" class="max-w-sm" />
            </div>
        </x-card>
    @endif

    <div class="mt-4 flex items-center gap-3">
        <button class="px-5 py-2.5 rounded-lg bg-brand text-white font-medium hover:bg-brand-dark">
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}
        </button>
        <a href="{{ route('pilgrims.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</a>
    </div>
</form>
