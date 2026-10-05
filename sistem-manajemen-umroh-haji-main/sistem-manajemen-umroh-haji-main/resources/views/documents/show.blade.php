<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Dokumen', 'route' => 'documents.index'], ['label' => $pilgrim->name]]" />
    <x-page-header title="Dokumen — {{ $pilgrim->name }}" :subtitle="'Kode: '.$pilgrim->code">
        <x-slot name="actions">
            @can('update', $pilgrim)
                <form method="POST" action="{{ route('documents.store', $pilgrim) }}">
                    @csrf
                    <input type="hidden" name="action" value="seed">
                    <button class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50">
                        <x-icon name="check" class="w-4 h-4" /> Buat Checklist Standar
                    </button>
                </form>
            @endcan
        </x-slot>
    </x-page-header>

    @php($canVerify = auth()->user()->isStaff())

    <x-card class="mt-6" padding="p-0">
        @if($documents->count() > 0)
            <x-table :headers="['Dokumen', 'Status', 'Berkas', 'Aksi']">
                @foreach($documents as $doc)
                    <tr class="hover:bg-gray-50 align-top">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $doc->label }}</p>
                            @if($doc->note)<p class="text-xs text-gray-400 mt-0.5">Catatan: {{ $doc->note }}</p>@endif
                        </td>
                        <td class="px-4 py-3">
                            <x-badge :color="$doc->status?->badgeColor() ?? 'gray'">{{ $doc->status?->label() ?? '-' }}</x-badge>
                            @if($doc->verified_at)
                                <p class="text-[11px] text-gray-400 mt-1">{{ format_date($doc->verified_at) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($doc->hasFile())
                                <a href="{{ route('documents.download', $doc) }}" class="inline-flex items-center gap-1 text-sm text-brand hover:underline">
                                    <x-icon name="download" class="w-4 h-4" /> {{ \Illuminate\Support\Str::limit($doc->original_name, 24) }}
                                </a>
                            @else
                                <span class="text-xs text-gray-400">Belum diunggah</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-2">
                                @can('upload', $doc)
                                    <form method="POST" action="{{ route('documents.upload', $doc) }}" enctype="multipart/form-data" class="flex items-center gap-2">
                                        @csrf
                                        <input type="file" name="file" required
                                               class="text-xs file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-gray-100 file:text-gray-600">
                                        <button class="text-xs px-2.5 py-1.5 rounded bg-brand text-white hover:bg-brand-dark">Unggah</button>
                                    </form>
                                    @error('file')<span class="text-[11px] text-red-500">{{ $message }}</span>@enderror
                                @endcan

                                @if($canVerify && $doc->hasFile())
                                    <form method="POST" action="{{ route('documents.verify', $doc) }}" class="flex items-center gap-2">
                                        @csrf @method('PUT')
                                        <select name="status" class="text-xs rounded border-gray-300 py-1 focus:border-brand focus:ring-brand">
                                            @foreach(\App\Enums\DocumentStatus::options() as $val => $label)
                                                <option value="{{ $val }}" @selected($doc->status?->value === $val)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button class="text-xs px-2.5 py-1.5 rounded border border-gray-300 hover:bg-gray-50">Set</button>
                                    </form>
                                @endif

                                @can('delete', $doc)
                                    <x-delete-form :action="route('documents.destroy', $doc)" label="Hapus" :icon="false" confirm="Hapus item dokumen ini dari checklist?" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        @else
            <x-empty-state icon="doc" title="Checklist masih kosong"
                message="Klik “Buat Checklist Standar” untuk membuat daftar dokumen wajib jamaah.">
            </x-empty-state>
        @endif
    </x-card>
</x-app-layout>
