<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Komisi Agen']]" />
    <x-page-header title="Komisi Agen" subtitle="Rekap &amp; pembayaran komisi agen" />

    @php
        $rekapMap = [
            \App\Enums\CommissionStatus::Pending,
            \App\Enums\CommissionStatus::Approved,
            \App\Enums\CommissionStatus::Paid,
            \App\Enums\CommissionStatus::Cancelled,
        ];
        $rekapColors = ['pending' => 'amber', 'approved' => 'blue', 'paid' => 'green', 'cancelled' => 'red'];
        $rekapIcons = ['pending' => 'percent', 'approved' => 'check', 'paid' => 'cash', 'cancelled' => 'history'];
    @endphp

    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($rekapMap as $status)
            @php($row = $rekap[$status->value] ?? null)
            <x-stats-card
                :label="$status->label()"
                :value="currency((float) ($row->amount ?? 0))"
                :icon="$rekapIcons[$status->value] ?? 'percent'"
                :color="$rekapColors[$status->value] ?? 'teal'"
                :sub="(int) ($row->total ?? 0).' komisi'" />
        @endforeach
    </div>

    <x-card class="mt-6" padding="p-0">
        <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-gray-100">
            <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                <option value="">Semua Status</option>
                @foreach(\App\Enums\CommissionStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            @if($agents->isNotEmpty())
                <select name="agent" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-brand focus:ring-brand">
                    <option value="">Semua Agen</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" @selected((string) request('agent') === (string) $agent->id)>{{ $agent->name }}</option>
                    @endforeach
                </select>
            @endif
            <button class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium hover:bg-gray-200">Filter</button>
        </form>

        @if($commissions->total() > 0)
            <x-table :headers="['Agen', 'Jamaah', 'Paket', 'Dasar', 'Rate', 'Komisi', 'Status', '']">
                @foreach($commissions as $c)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $c->agent?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $c->registration?->pilgrim?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $c->registration?->package?->name ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ currency((float) $c->base_amount) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ number_format((float) $c->rate, 2) }}%</td>
                        <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-800">{{ currency((float) $c->amount) }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$c->status?->badgeColor() ?? 'gray'">{{ $c->status?->label() ?? '-' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @can('manage', $c)
                                @if(in_array($c->status, [\App\Enums\CommissionStatus::Paid, \App\Enums\CommissionStatus::Cancelled], true))
                                    <span class="text-xs text-gray-400">Final</span>
                                @else
                                    <form method="POST" action="{{ route('commissions.status', $c) }}" class="inline-flex items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" onchange="this.form.submit()"
                                                class="rounded-lg border-gray-300 text-xs focus:border-brand focus:ring-brand">
                                            <option value="">Ubah status…</option>
                                            <option value="approved">Setujui</option>
                                            <option value="paid">Tandai Dibayar</option>
                                            <option value="cancelled">Batalkan</option>
                                        </select>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <div class="p-4">{{ $commissions->links() }}</div>
        @else
            <x-empty-state icon="percent" title="Belum ada komisi"
                message="Komisi otomatis dibuat saat pendaftaran jamaah oleh agen." />
        @endif
    </x-card>
</x-app-layout>
