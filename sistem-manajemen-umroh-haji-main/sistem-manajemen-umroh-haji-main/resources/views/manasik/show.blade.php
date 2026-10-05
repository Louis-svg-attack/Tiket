<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Manasik', 'route' => 'manasik.index'], ['label' => $schedule->title]]" />
    <x-page-header :title="$schedule->title" :subtitle="$schedule->date ? format_date($schedule->date) : '-'">
        <x-slot name="actions">
            @can('update', $schedule)
                <x-action-link :href="route('manasik.edit', $schedule)" icon="calendar" variant="secondary">Edit</x-action-link>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
        <x-stats-card label="Hadir" :value="$rekap[\App\Enums\AttendanceStatus::Present->value] ?? 0" icon="check" color="green" />
        <x-stats-card label="Izin" :value="$rekap[\App\Enums\AttendanceStatus::Excused->value] ?? 0" icon="user" color="amber" />
        <x-stats-card label="Tidak Hadir" :value="$rekap[\App\Enums\AttendanceStatus::Absent->value] ?? 0" icon="users" color="red" />
    </div>

    <x-card class="mt-4" title="Informasi Jadwal">
        <dl class="divide-y divide-gray-100 text-sm">
            <div class="flex justify-between py-2"><dt class="text-gray-500">Paket</dt>
                <dd class="text-gray-800">{{ $schedule->package?->name ?? 'Umum' }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-gray-500">Pembimbing</dt>
                <dd class="text-gray-800">{{ $schedule->guide?->name ?? '-' }}</dd></div>
            <div class="flex justify-between py-2"><dt class="text-gray-500">Waktu</dt>
                <dd class="text-gray-800">{{ $schedule->start_time ?? '-' }} @if($schedule->end_time) – {{ $schedule->end_time }} @endif</dd></div>
            <div class="flex justify-between py-2"><dt class="text-gray-500">Lokasi</dt>
                <dd class="text-gray-800">{{ $schedule->location ?? '-' }}</dd></div>
        </dl>
    </x-card>

    <x-card class="mt-4" padding="p-0" title="Absensi Jamaah">
        @if($pilgrims->count() > 0)
            @can('recordAttendance', $schedule)
                <form method="POST" action="{{ route('manasik.attendance', $schedule) }}">
                    @csrf
                    <x-table :headers="['Jamaah', 'Kehadiran']">
                        @foreach($pilgrims as $pilgrim)
                            @php($current = ($attendances[$pilgrim->id] ?? null))
                            @php($currentValue = $current instanceof \App\Enums\AttendanceStatus ? $current->value : \App\Enums\AttendanceStatus::Absent->value)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $pilgrim->name }}</td>
                                <td class="px-4 py-3">
                                    <select name="status[{{ $pilgrim->id }}]"
                                        class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                                        @foreach(\App\Enums\AttendanceStatus::options() as $val => $label)
                                            <option value="{{ $val }}" @selected($currentValue === $val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </x-table>
                    <div class="p-4 flex items-center gap-3">
                        <button class="px-5 py-2.5 rounded-lg bg-brand text-white font-medium hover:bg-brand-dark">Simpan Absensi</button>
                        <a href="{{ route('manasik.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Kembali</a>
                    </div>
                </form>
            @else
                <x-table :headers="['Jamaah', 'Kehadiran']">
                    @foreach($pilgrims as $pilgrim)
                        @php($current = ($attendances[$pilgrim->id] ?? null))
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $pilgrim->name }}</td>
                            <td class="px-4 py-3">
                                <x-badge :color="$current?->badgeColor() ?? \App\Enums\AttendanceStatus::Absent->badgeColor()">
                                    {{ $current?->label() ?? \App\Enums\AttendanceStatus::Absent->label() }}
                                </x-badge>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endcan
        @else
            <x-empty-state icon="users" title="Belum ada jamaah"
                message="Belum ada jamaah dengan pendaftaran aktif untuk jadwal ini." />
        @endif
    </x-card>
</x-app-layout>
