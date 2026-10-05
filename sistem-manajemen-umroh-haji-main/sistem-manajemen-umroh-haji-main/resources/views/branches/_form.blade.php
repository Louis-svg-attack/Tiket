@php($isEdit = $branch->exists)
<form method="POST" action="{{ $isEdit ? route('branches.update', $branch) : route('branches.store') }}" class="mt-6 max-w-2xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <x-card>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input name="code" label="Kode" :value="$branch->code" required />
            <x-input name="name" label="Nama Cabang" :value="$branch->name" required />
            <x-input name="phone" label="Telepon" :value="$branch->phone" />
            <x-input name="email" label="Email" type="email" :value="$branch->email" />
            <x-input name="city" label="Kota" :value="$branch->city" />
            <x-input name="address" label="Alamat" type="textarea" :value="$branch->address" class="sm:col-span-2" />
            <div class="flex items-center gap-2 pt-1">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $branch->is_active ?? true))
                       class="rounded border-gray-300 text-brand focus:ring-brand">
                <label for="is_active" class="text-sm text-gray-700">Aktif</label>
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="hidden" name="is_head_office" value="0">
                <input type="checkbox" id="is_head_office" name="is_head_office" value="1" @checked(old('is_head_office', $branch->is_head_office ?? false))
                       class="rounded border-gray-300 text-brand focus:ring-brand">
                <label for="is_head_office" class="text-sm text-gray-700">Kantor Pusat</label>
            </div>
        </div>
    </x-card>

    <div class="mt-4 flex items-center gap-3">
        <button class="px-5 py-2.5 rounded-lg bg-brand text-white font-medium hover:bg-brand-dark">
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}
        </button>
        <a href="{{ route('branches.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</a>
    </div>
</form>
