<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Pengumuman']]" />
    <x-page-header title="Pengumuman" subtitle="Kelola pengumuman untuk pengguna">
        <x-slot name="actions">
            @can('create', \App\Models\Announcement::class)
                <x-action-link :href="route('announcements.create')" icon="megaphone">Tambah Pengumuman</x-action-link>
            @endcan
        </x-slot>
    </x-page-header>

    <x-card class="mt-6" padding="p-0">
        @if($announcements->total() > 0)
            <x-table :headers="['Judul', 'Audiens', 'Cabang', 'Status', 'Publish', '']">
                @foreach($announcements as $a)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $a->title }}</div>
                            <div class="text-xs text-gray-400">{{ $a->package?->name ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge :color="$a->audience?->badgeColor() ?? 'gray'">{{ $a->audience?->label() ?? '-' }}</x-badge>
                        </td>
                        <td class="px-4 py-3">{{ $a->branch?->name ?? 'Semua' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $isLive = $a->is_published
                                    && (! $a->publish_at || $a->publish_at->lessThanOrEqualTo(now()))
                                    && (! $a->expires_at || $a->expires_at->greaterThanOrEqualTo(now()));
                            @endphp
                            @if($a->is_published)
                                <x-badge color="green">Published</x-badge>
                                @if($isLive)
                                    <span class="ml-1 inline-flex items-center text-[11px] text-green-600">
                                        <x-icon name="dot" class="w-3 h-3" /> Live
                                    </span>
                                @endif
                            @else
                                <x-badge color="gray">Draft</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $a->publish_at ? format_datetime($a->publish_at) : '-' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @can('update', $a)
                                <a href="{{ route('announcements.edit', $a) }}" class="text-brand text-sm hover:underline mr-3">Edit</a>
                            @endcan
                            @can('delete', $a)
                                <x-delete-form :action="route('announcements.destroy', $a)" />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <div class="p-4">{{ $announcements->links() }}</div>
        @else
            <x-empty-state icon="megaphone" title="Belum ada pengumuman"
                message="Buat pengumuman untuk menyampaikan informasi kepada pengguna." />
        @endif
    </x-card>
</x-app-layout>
