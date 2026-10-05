<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Log Aktivitas']]" />
    <x-page-header title="Log Aktivitas" subtitle="Riwayat aktivitas pengguna" />

    <x-card class="mt-6" padding="p-0">
        <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-gray-100">
            <select name="action" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                <option value="">Semua Aksi</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                @endforeach
            </select>
            <select name="user" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                <option value="">Semua Pengguna</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected((string) request('user') === (string) $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium hover:bg-gray-200">Filter</button>
        </form>

        @if($logs->total() > 0)
            <x-table :headers="['Waktu', 'Pengguna', 'Aksi', 'Deskripsi', 'IP']">
                @foreach($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ format_datetime($log->created_at) }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $log->user?->name ?? 'Sistem' }}</td>
                        <td class="px-4 py-3"><x-badge color="gray">{{ $log->action }}</x-badge></td>
                        <td class="px-4 py-3">{{ $log->description ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-400">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @endforeach
            </x-table>
            <div class="p-4">{{ $logs->links() }}</div>
        @else
            <x-empty-state icon="history" title="Belum ada aktivitas"
                message="Aktivitas pengguna akan tercatat di sini." />
        @endif
    </x-card>
</x-app-layout>
