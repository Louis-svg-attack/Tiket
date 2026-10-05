@php($isEdit = $registration->exists)
<form method="POST" action="{{ $isEdit ? route('registrations.update', $registration) : route('registrations.store') }}" class="mt-6 max-w-2xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <x-card>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @if($isEdit)
                <div class="sm:col-span-2 text-sm text-gray-500">
                    Jamaah: <span class="font-medium text-gray-800">{{ $registration->pilgrim?->name ?? '-' }}</span> &middot;
                    Paket: <span class="font-medium text-gray-800">{{ $registration->package?->name ?? '-' }}</span>
                </div>
            @else
                <x-select name="pilgrim_id" label="Jamaah" :options="$pilgrims" :selected="$registration->pilgrim_id"
                    required class="sm:col-span-2" placeholder="— Pilih Jamaah —" />
                <x-select name="package_id" label="Paket" :options="$packages" :selected="$registration->package_id"
                    required class="sm:col-span-2" placeholder="— Pilih Paket —"
                    hint="Hanya paket berstatus terbuka yang ditampilkan." />
            @endif
            <x-select name="guide_id" label="Pembimbing (opsional)" :options="$guides" :selected="$registration->guide_id"
                placeholder="— Tanpa Pembimbing —" />
            <x-input name="registered_at" label="Tanggal Daftar" type="date"
                :value="optional($registration->registered_at)->format('Y-m-d')" required />
            <x-input name="notes" label="Catatan" type="textarea" :value="$registration->notes" class="sm:col-span-2" />
        </div>
    </x-card>

    <div class="mt-4 flex items-center gap-3">
        <button class="px-5 py-2.5 rounded-lg bg-brand text-white font-medium hover:bg-brand-dark">
            {{ $isEdit ? 'Simpan Perubahan' : 'Daftarkan' }}
        </button>
        <a href="{{ route('registrations.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</a>
    </div>
</form>
