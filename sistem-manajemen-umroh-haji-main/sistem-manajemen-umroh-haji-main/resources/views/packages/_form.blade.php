@php($isEdit = $package->exists)
<form method="POST" action="{{ $isEdit ? route('packages.update', $package) : route('packages.store') }}" class="mt-6 max-w-3xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <x-card title="Informasi Paket">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input name="name" label="Nama Paket" :value="$package->name" required class="sm:col-span-2" />
            <x-input name="code" label="Kode" :value="$package->code" hint="Kosongkan untuk dibuat otomatis" />
            <x-select name="branch_id" label="Cabang"
                :options="$branches->pluck('name', 'id')->all()"
                :selected="$package->branch_id"
                placeholder="Pilih Cabang" />
            <x-select name="type" label="Jenis"
                :options="\App\Enums\PackageType::options()"
                :selected="$package->type?->value" required />
            <x-select name="status" label="Status"
                :options="\App\Enums\PackageStatus::options()"
                :selected="$package->status?->value" required />
            <x-input name="price" label="Harga" type="number" step="0.01" :value="$package->price" required />
            <x-input name="down_payment" label="Uang Muka (DP)" type="number" step="0.01" :value="$package->down_payment" />
            <x-input name="quota" label="Kuota" type="number" :value="$package->quota" required />
            <x-input name="duration_days" label="Durasi (hari)" type="number" :value="$package->duration_days" required />
        </div>
    </x-card>

    <x-card title="Keberangkatan &amp; Akomodasi" class="mt-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input name="departure_date" label="Tanggal Berangkat" type="date"
                :value="optional($package->departure_date)->format('Y-m-d')" />
            <x-input name="departure_city" label="Kota Keberangkatan" :value="$package->departure_city" />
            <x-select name="hotel_makkah_id" label="Hotel Mekah"
                :options="$hotelsMakkah->pluck('name', 'id')->all()"
                :selected="$package->hotel_makkah_id"
                placeholder="Pilih Hotel Mekah" />
            <x-select name="hotel_madinah_id" label="Hotel Madinah"
                :options="$hotelsMadinah->pluck('name', 'id')->all()"
                :selected="$package->hotel_madinah_id"
                placeholder="Pilih Hotel Madinah" />
            <x-select name="airline_id" label="Maskapai"
                :options="$airlines->pluck('name', 'id')->all()"
                :selected="$package->airline_id"
                placeholder="Pilih Maskapai" class="sm:col-span-2" />
        </div>
    </x-card>

    <x-card title="Fasilitas &amp; Deskripsi" class="mt-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input name="facilities_included" label="Fasilitas Termasuk" type="textarea"
                :value="implode(\"\n\", $package->facilities_included ?? [])"
                hint="Satu fasilitas per baris" />
            <x-input name="facilities_excluded" label="Fasilitas Tidak Termasuk" type="textarea"
                :value="implode(\"\n\", $package->facilities_excluded ?? [])"
                hint="Satu fasilitas per baris" />
            <x-input name="description" label="Deskripsi" type="textarea" :value="$package->description" class="sm:col-span-2" />
        </div>
    </x-card>

    <div class="mt-4 flex items-center gap-3">
        <button class="px-5 py-2.5 rounded-lg bg-brand text-white font-medium hover:bg-brand-dark">
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}
        </button>
        <a href="{{ route('packages.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</a>
    </div>
</form>
