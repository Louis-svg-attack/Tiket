@php($user = auth()->user())
<header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-10">
    <div class="flex items-center gap-3">
        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-500 hover:text-gray-700">
            <x-icon name="list" class="w-6 h-6" />
        </button>
        <div>
            <p class="text-sm font-semibold text-gray-700">@yield('page-title', 'Selamat datang')</p>
            <p class="text-[11px] text-gray-400">{{ format_date(now(), 'l, d F Y') }}</p>
        </div>
    </div>

    <div class="flex items-center gap-3">
        {{-- Branch focus selector (super_admin only) --}}
        @if($user?->isSuperAdmin())
            @php($branches = \App\Models\Branch::where('is_active', true)->orderBy('name')->get())
            <form method="POST" action="{{ route('branch.focus') }}" class="hidden sm:block">
                @csrf
                <select name="branch_id" onchange="this.form.submit()"
                        class="text-xs border-gray-200 rounded-lg py-1.5 pr-8 focus:border-brand focus:ring-0">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" @selected(app(\App\Services\BranchContext::class)->focusedBranchId() === $b->id)>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 text-sm">
                <div class="h-8 w-8 rounded-full bg-brand text-white grid place-items-center text-xs font-semibold">
                    {{ initials($user?->name) }}
                </div>
                <div class="hidden sm:block text-left">
                    <p class="text-gray-700 font-medium leading-tight">{{ $user?->name }}</p>
                    <p class="text-[11px] text-gray-400 leading-tight">{{ $user?->roleLabel() }}</p>
                </div>
            </button>
            <div x-show="open" x-cloak @click.outside="open = false"
                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 text-sm">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-600 hover:bg-gray-50">Profil Saya</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50">Keluar</button>
                </form>
            </div>
        </div>
    </div>
</header>
