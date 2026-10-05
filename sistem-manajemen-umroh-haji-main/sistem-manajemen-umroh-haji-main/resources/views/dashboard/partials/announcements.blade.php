@props(['announcements'])
<x-card title="Pengumuman Terbaru">
    @forelse($announcements ?? [] as $a)
        <div class="py-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
            <div class="flex items-center gap-2">
                <x-badge :color="$a->audience?->badgeColor() ?? 'gray'">{{ $a->audience?->label() ?? 'Umum' }}</x-badge>
                <span class="text-xs text-gray-400">{{ format_date($a->publish_at ?? $a->created_at) }}</span>
            </div>
            <p class="font-medium text-gray-800 mt-1">{{ $a->title }}</p>
            <p class="text-sm text-gray-500 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($a->body), 140) }}</p>
        </div>
    @empty
        <x-empty-state icon="megaphone" title="Belum ada pengumuman"
            message="Pengumuman dari travel akan muncul di sini." />
    @endforelse
</x-card>
