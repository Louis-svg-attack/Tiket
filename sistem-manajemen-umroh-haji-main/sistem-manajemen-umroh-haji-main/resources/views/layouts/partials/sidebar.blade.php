@php
    $user = auth()->user();
    $isStaff = $user?->isStaff() ?? false;
    $isSuper = $user?->isSuperAdmin() ?? false;
    $isAgent = $user?->isAgent() ?? false;
    $isPilgrim = $user?->isPilgrim() ?? false;
    $isGuide = $user?->isGuide() ?? false;
@endphp

<aside
    class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-200 flex flex-col transform transition-transform lg:translate-x-0 lg:static lg:inset-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

    {{-- Brand --}}
    <div class="h-16 flex items-center gap-3 px-5 border-b border-gray-100">
        @if($logo = setting('logo_path'))
            <img src="{{ asset('storage/'.$logo) }}" alt="logo" class="h-9 w-9 object-contain rounded">
        @else
            <div class="h-9 w-9 rounded-lg bg-brand text-white grid place-items-center font-bold">
                {{ initials(setting('app_name')) }}
            </div>
        @endif
        <div class="min-w-0">
            <p class="font-semibold text-gray-800 text-sm truncate">{{ setting('app_name') }}</p>
            <p class="text-[11px] text-gray-400 truncate">{{ setting('app_subtitle') }}</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <x-nav-item route="dashboard" icon="dashboard">Dashboard</x-nav-item>

        {{-- ============ STAFF: MASTER DATA ============ --}}
        @if($isStaff)
            <p class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Master Data</p>
            @if($isSuper)
                <x-nav-item route="branches.index" icon="building">Cabang</x-nav-item>
            @endif
            <x-nav-item route="packages.index" icon="package">Paket</x-nav-item>
            <x-nav-item route="hotels.index" icon="hotel">Hotel</x-nav-item>
            <x-nav-item route="airlines.index" icon="plane">Maskapai</x-nav-item>
            <x-nav-item route="bank-accounts.index" icon="bank">Rekening Bank</x-nav-item>
            <x-nav-item route="agents.index" icon="user">Agen</x-nav-item>
            <x-nav-item route="guides.index" icon="guide">Muthawif</x-nav-item>
            <x-nav-item route="pilgrims.index" icon="users">Jamaah</x-nav-item>
        @endif

        {{-- ============ STAFF: OPERASIONAL ============ --}}
        @if($isStaff)
            <p class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Operasional</p>
            <x-nav-item route="registrations.index" icon="list">Pendaftaran</x-nav-item>
            <x-nav-item route="documents.index" icon="doc">Dokumen</x-nav-item>
            <x-nav-item route="payments.index" icon="cash">Pembayaran</x-nav-item>
            <x-nav-item route="manasik.index" icon="calendar">Manasik</x-nav-item>
            <x-nav-item route="manifests.index" icon="bus">Manifest &amp; Rooming</x-nav-item>
            <x-nav-item route="equipment.index" icon="box">Perlengkapan</x-nav-item>
            <x-nav-item route="commissions.index" icon="percent">Komisi Agen</x-nav-item>
            <x-nav-item route="announcements.index" icon="megaphone">Pengumuman</x-nav-item>
        @endif

        {{-- ============ AGENT ============ --}}
        @if($isAgent)
            <p class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Marketing</p>
            <x-nav-item route="pilgrims.index" icon="users">Jamaah Referral</x-nav-item>
            <x-nav-item route="registrations.index" icon="list">Pendaftaran</x-nav-item>
            <x-nav-item route="commissions.index" icon="percent">Komisi Saya</x-nav-item>
        @endif

        {{-- ============ PILGRIM ============ --}}
        @if($isPilgrim)
            <p class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Perjalanan Saya</p>
            <x-nav-item route="registrations.index" icon="list">Pendaftaran Saya</x-nav-item>
            <x-nav-item route="documents.index" icon="doc">Dokumen Saya</x-nav-item>
            <x-nav-item route="payments.index" icon="cash">Pembayaran</x-nav-item>
            <x-nav-item route="manasik.index" icon="calendar">Jadwal Manasik</x-nav-item>
        @endif

        {{-- ============ GUIDE ============ --}}
        @if($isGuide)
            <p class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Bimbingan</p>
            <x-nav-item route="pilgrims.index" icon="users">Jamaah Bimbingan</x-nav-item>
            <x-nav-item route="manasik.index" icon="calendar">Manasik &amp; Absensi</x-nav-item>
        @endif

        {{-- ============ SYSTEM ============ --}}
        @if($isStaff)
            <p class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Sistem</p>
            <x-nav-item route="users.index" icon="user">Pengguna</x-nav-item>
            @if($isSuper)
                <x-nav-item route="settings.edit" icon="cog">White-label</x-nav-item>
            @endif
            <x-nav-item route="activity-logs.index" icon="history">Log Aktivitas</x-nav-item>
        @endif
    </nav>

    <div class="border-t border-gray-100 p-3">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-red-50 hover:text-red-600 transition">
                <x-icon name="logout" class="w-5 h-5" />
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>
