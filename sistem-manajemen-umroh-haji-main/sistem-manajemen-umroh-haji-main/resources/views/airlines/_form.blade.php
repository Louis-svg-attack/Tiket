@php($isEdit = $airline->exists)
<form method="POST" action="{{ $isEdit ? route('airlines.update', $airline) : route('airlines.store') }}" class="mt-6 max-w-2xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <x-card>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input name="name" label="Nama Maskapai" :value="$airline->name" required class="sm:col-span-2" />
            <x-input name="code" label="Kode" :value="$airline->code" />
            <x-input name="transit" label="Transit" :value="$airline->transit" />
            <div class="flex items-center gap-2 pt-7">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $airline->is_active ?? true))
                       class="rounded border-gray-300 text-brand focus:ring-brand">
                <label for="is_active" class="text-sm text-gray-700">Aktif</label>
            </div>
        </div>
    </x-card>

    <div class="mt-4 flex items-center gap-3">
        <button class="px-5 py-2.5 rounded-lg bg-brand text-white font-medium hover:bg-brand-dark">
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}
        </button>
        <a href="{{ route('airlines.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</a>
    </div>
</form>
