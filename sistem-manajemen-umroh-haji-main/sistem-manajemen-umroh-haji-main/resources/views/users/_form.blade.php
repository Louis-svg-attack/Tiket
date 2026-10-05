@php($isEdit = $user->exists)
<form method="POST" action="{{ $isEdit ? route('users.update', $user) : route('users.store') }}" class="mt-6 max-w-2xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <x-card title="Data Pengguna">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input name="name" label="Nama" :value="$user->name" required />
            <x-input name="email" label="Email" type="email" :value="$user->email" required />
            <x-select name="role" label="Role"
                :options="$roles"
                :selected="$user->role?->value"
                placeholder="— Pilih —" required />
            <x-select name="branch_id" label="Cabang"
                :options="$branches->pluck('name', 'id')->all()"
                :selected="$user->branch_id"
                placeholder="Tanpa Cabang" />
            <x-input name="phone" label="Telepon / WhatsApp" :value="$user->phone" />
            <div class="flex items-center gap-2 pt-7">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))
                       class="rounded border-gray-300 text-brand focus:ring-brand">
                <label for="is_active" class="text-sm text-gray-700">Aktif</label>
            </div>
            <x-input name="password" label="Kata Sandi" type="password"
                :required="! $isEdit"
                :hint="$isEdit ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter'"
                class="sm:col-span-2" />
        </div>
    </x-card>

    <div class="mt-4 flex items-center gap-3">
        <button class="px-5 py-2.5 rounded-lg bg-brand text-white font-medium hover:bg-brand-dark">
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}
        </button>
        <a href="{{ route('users.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</a>
    </div>
</form>
