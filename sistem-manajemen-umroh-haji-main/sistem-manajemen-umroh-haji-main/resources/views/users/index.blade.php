<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Pengguna']]" />
    <x-page-header title="Pengguna" subtitle="Kelola akun login">
        <x-slot name="actions">
            @can('create', \App\Models\User::class)
                <x-action-link :href="route('users.create')" icon="users">Tambah Pengguna</x-action-link>
            @endcan
        </x-slot>
    </x-page-header>

    <x-card class="mt-6" padding="p-0">
        <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-gray-100">
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama / email…"
                   class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
            <select name="role" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                <option value="">Semua Role</option>
                @foreach(\App\Enums\Role::cases() as $role)
                    <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium hover:bg-gray-200">Cari</button>
        </form>

        @if($users->total() > 0)
            <x-table :headers="['Nama', 'Email', 'Role', 'Cabang', 'Status', 'Login Terakhir', '']">
                @foreach($users as $u)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $u->name }}</td>
                        <td class="px-4 py-3">{{ $u->email }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$u->role?->badgeColor() ?? 'gray'">{{ $u->role?->label() ?? '-' }}</x-badge>
                        </td>
                        <td class="px-4 py-3">{{ $u->branch?->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$u->is_active ? 'green' : 'gray'">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $u->last_login_at ? format_datetime($u->last_login_at) : '-' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @can('update', $u)
                                <a href="{{ route('users.edit', $u) }}" class="text-brand text-sm hover:underline mr-3">Edit</a>
                                <form method="POST" action="{{ route('users.reset-password', $u) }}" class="inline"
                                      onsubmit="return confirm('Reset kata sandi {{ $u->name }}? Kata sandi sementara akan ditampilkan setelah reset.')">
                                    @csrf
                                    <button type="submit" class="text-amber-600 text-sm hover:underline mr-3">Reset Sandi</button>
                                </form>
                            @endcan
                            @if($u->id !== auth()->id())
                                @can('delete', $u)
                                    <x-delete-form :action="route('users.destroy', $u)" />
                                @endcan
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <div class="p-4">{{ $users->links() }}</div>
        @else
            <x-empty-state icon="users" title="Belum ada pengguna"
                message="Tambahkan akun login untuk staff, agen, jamaah, atau muthawif." />
        @endif
    </x-card>
</x-app-layout>
