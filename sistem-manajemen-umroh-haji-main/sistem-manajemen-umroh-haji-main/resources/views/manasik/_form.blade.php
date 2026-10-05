@php($isEdit = $schedule->exists)
<form method="POST" action="{{ $isEdit ? route('manasik.update', $schedule) : route('manasik.store') }}" class="mt-6 max-w-2xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <x-card>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input name="title" label="Judul" :value="$schedule->title" required class="sm:col-span-2" />
            <x-select name="package_id" label="Paket" :options="$packages" :selected="$schedule->package_id"
                placeholder="— Umum (semua jamaah) —" hint="Kosongkan untuk manasik umum cabang." />
            <x-select name="guide_id" label="Pembimbing" :options="$guides" :selected="$schedule->guide_id"
                placeholder="— Tanpa pembimbing —" />
            <x-input name="date" label="Tanggal" type="date"
                :value="$schedule->date?->format('Y-m-d')" required />
            <x-input name="location" label="Lokasi" :value="$schedule->location" />
            <x-input name="start_time" label="Jam Mulai" type="time" :value="$schedule->start_time" />
            <x-input name="end_time" label="Jam Selesai" type="time" :value="$schedule->end_time" />
            <x-input name="description" label="Deskripsi" type="textarea" :value="$schedule->description" class="sm:col-span-2" />
        </div>
    </x-card>

    <div class="mt-4 flex items-center gap-3">
        <button class="px-5 py-2.5 rounded-lg bg-brand text-white font-medium hover:bg-brand-dark">
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}
        </button>
        <a href="{{ route('manasik.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</a>
    </div>
</form>
