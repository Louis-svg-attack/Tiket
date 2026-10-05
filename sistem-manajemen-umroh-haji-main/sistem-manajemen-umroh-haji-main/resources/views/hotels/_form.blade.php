@php($isEdit = $hotel->exists)
<form method="POST" action="{{ $isEdit ? route('hotels.update', $hotel) : route('hotels.store') }}" class="mt-6 max-w-2xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <x-card>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input name="name" label="Nama Hotel" :value="$hotel->name" required class="sm:col-span-2" />
            <x-select name="city" label="Kota" :options="['Mekah' => 'Mekah', 'Madinah' => 'Madinah']" :selected="$hotel->city" required />
            <x-select name="star" label="Bintang" :options="[1=>'1',2=>'2',3=>'3',4=>'4',5=>'5']" :selected="$hotel->star" required />
            <x-input name="distance_to_masjid" label="Jarak ke Masjid (meter)" type="number" :value="$hotel->distance_to_masjid" />
            <div class="flex items-center gap-2 pt-7">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $hotel->is_active ?? true))
                       class="rounded border-gray-300 text-brand focus:ring-brand">
                <label for="is_active" class="text-sm text-gray-700">Aktif</label>
            </div>
            <x-input name="description" label="Deskripsi" type="textarea" :value="$hotel->description" class="sm:col-span-2" />
        </div>
    </x-card>

    <div class="mt-4 flex items-center gap-3">
        <button class="px-5 py-2.5 rounded-lg bg-brand text-white font-medium hover:bg-brand-dark">
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}
        </button>
        <a href="{{ route('hotels.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</a>
    </div>
</form>
