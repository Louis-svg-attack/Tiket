@php($isEdit = $equipment->exists)
<form method="POST" action="{{ $isEdit ? route('equipment.update', $equipment) : route('equipment.store') }}" class="mt-6 max-w-2xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <x-card>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-select name="registration_id" label="Jamaah" :options="$registrations" :selected="$equipment->registration_id"
                required class="sm:col-span-2" />
            <x-select name="item" label="Item"
                :options="['Koper' => 'Koper', 'Seragam' => 'Seragam', 'Ihram' => 'Ihram', 'Tas Paspor' => 'Tas Paspor']"
                :selected="$equipment->item" required />
            <x-input name="size" label="Ukuran" :value="$equipment->size" />
            <x-input name="quantity" label="Jumlah" type="number" :value="$equipment->quantity ?? 1" required />
            <x-select name="status" label="Status" :options="\App\Enums\EquipmentStatus::options()"
                :selected="$equipment->status?->value" :placeholder="false" required />
            <x-input name="handed_at" label="Tanggal Diserahkan" type="date"
                :value="$equipment->handed_at?->format('Y-m-d')" />
            <x-input name="note" label="Catatan" type="textarea" :value="$equipment->note" class="sm:col-span-2" />
        </div>
    </x-card>

    <div class="mt-4 flex items-center gap-3">
        <button class="px-5 py-2.5 rounded-lg bg-brand text-white font-medium hover:bg-brand-dark">
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}
        </button>
        <a href="{{ route('equipment.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</a>
    </div>
</form>
