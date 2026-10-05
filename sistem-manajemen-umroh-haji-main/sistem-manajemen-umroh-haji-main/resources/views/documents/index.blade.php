<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Dokumen']]" />
    <x-page-header title="Dokumen Jamaah" subtitle="Kelengkapan &amp; verifikasi dokumen per jamaah" />

    @if($selfOnly ?? false)
        <div class="mt-6">
            <x-empty-state icon="user" title="Profil jamaah belum tertaut"
                message="Akun Anda belum terhubung ke data jamaah. Hubungi admin travel Anda." />
        </div>
    @else
        <x-card class="mt-6" padding="p-0">
            <form method="GET" class="flex gap-3 p-4 border-b border-gray-100">
                <input name="q" value="{{ request('q') }}" placeholder="Cari nama jamaah…"
                       class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                <button class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium hover:bg-gray-200">Cari</button>
            </form>

            @if($pilgrims->total() > 0)
                <x-table :headers="['Jamaah', 'Cabang', 'Kelengkapan', 'Status', '']">
                    @foreach($pilgrims as $p)
                        @php($pct = percent($p->verified_documents_count, $p->documents_count))
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $p->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $p->branch?->name ?? '-' }}</td>
                            <td class="px-4 py-3 w-48">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-100 rounded-full h-2">
                                        <div class="bg-brand h-2 rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $p->verified_documents_count }}/{{ $p->documents_count }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($p->documents_count === 0)
                                    <x-badge color="gray">Belum ada</x-badge>
                                @elseif($pct >= 100)
                                    <x-badge color="green">Lengkap</x-badge>
                                @else
                                    <x-badge color="amber">Proses</x-badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('documents.show', $p) }}" class="text-brand text-sm hover:underline">Kelola</a>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
                <div class="p-4">{{ $pilgrims->links() }}</div>
            @else
                <x-empty-state icon="doc" title="Belum ada jamaah"
                    message="Tambahkan data jamaah terlebih dahulu untuk mengelola dokumen." />
            @endif
        </x-card>
    @endif
</x-app-layout>
