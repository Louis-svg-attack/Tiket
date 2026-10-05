@php($isEdit = $announcement->exists)
<form method="POST" action="{{ $isEdit ? route('announcements.update', $announcement) : route('announcements.store') }}" class="mt-6 max-w-2xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <x-card title="Detail Pengumuman">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input name="title" label="Judul" :value="$announcement->title" required class="sm:col-span-2" />
            <x-input name="body" label="Isi Pengumuman" type="textarea" :value="$announcement->body" required class="sm:col-span-2" />
            <x-select name="audience" label="Audiens"
                :options="\App\Enums\AnnouncementAudience::options()"
                :selected="$announcement->audience?->value"
                placeholder="— Pilih —" required />
            <x-select name="branch_id" label="Cabang"
                :options="$branches->pluck('name', 'id')->all()"
                :selected="$announcement->branch_id"
                placeholder="Semua Cabang" />
            <x-select name="package_id" label="Paket (opsional)"
                :options="$packages"
                :selected="$announcement->package_id"
                placeholder="Semua Paket" class="sm:col-span-2" />
            <x-input name="publish_at" label="Tayang Mulai" type="datetime-local"
                :value="old('publish_at', $announcement->publish_at?->format('Y-m-d\TH:i'))" />
            <x-input name="expires_at" label="Kedaluwarsa" type="datetime-local"
                :value="old('expires_at', $announcement->expires_at?->format('Y-m-d\TH:i'))" />
            <div class="flex items-center gap-2 pt-2">
                <input type="hidden" name="is_published" value="0">
                <input type="checkbox" id="is_published" name="is_published" value="1" @checked(old('is_published', $announcement->is_published ?? true))
                       class="rounded border-gray-300 text-brand focus:ring-brand">
                <label for="is_published" class="text-sm text-gray-700">Terbitkan</label>
            </div>
        </div>
    </x-card>

    <div class="mt-4 flex items-center gap-3">
        <button class="px-5 py-2.5 rounded-lg bg-brand text-white font-medium hover:bg-brand-dark">
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}
        </button>
        <a href="{{ route('announcements.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</a>
    </div>
</form>
