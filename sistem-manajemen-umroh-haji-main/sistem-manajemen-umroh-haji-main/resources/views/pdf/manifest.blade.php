<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 9px; color: #222; margin: 0; padding: 16px 20px; }
        .head { border-bottom: 2px solid {{ $branding['color'] }}; padding-bottom: 6px; margin-bottom: 8px; }
        .head h1 { margin: 0; font-size: 14px; color: {{ $branding['color'] }}; }
        .head p { margin: 1px 0; font-size: 8px; color: #666; }
        .title { text-align: center; font-size: 12px; font-weight: bold; margin: 4px 0 8px; }
        .pkg { font-size: 9px; margin-bottom: 8px; }
        .pkg strong { color: {{ $branding['color'] }}; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 0.5px solid #cbd5e1; padding: 3px 4px; text-align: left; }
        table.data th { background: #f1f5f9; font-size: 8px; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="head">
        <h1>{{ $branding['name'] }}</h1>
        <p>{{ $branding['address'] }} · {{ $branding['phone'] }}</p>
    </div>

    <div class="title">MANIFEST JAMAAH</div>

    <div class="pkg">
        <strong>{{ $package->name }}</strong> ({{ $package->code }}) ·
        {{ $package->type?->label() ?? '-' }} ·
        Berangkat {{ format_date($package->departure_date) }} ·
        {{ $package->duration_days }} hari ·
        Maskapai {{ $package->airline?->name ?? '-' }}<br>
        Hotel Mekah: {{ $package->hotelMakkah?->name ?? '-' }} · Hotel Madinah: {{ $package->hotelMadinah?->name ?? '-' }}
    </div>

    <table class="data">
        <thead>
            <tr>
                <th style="width:18px;">No</th>
                <th>Nama Jamaah</th>
                <th>L/P</th>
                <th>No. Paspor</th>
                <th>Kamar</th>
                <th>Tipe</th>
                <th>Bus</th>
                <th>Kursi</th>
                <th>Mahram/Grup</th>
            </tr>
        </thead>
        <tbody>
            @forelse($registrations as $i => $reg)
                @php($m = $reg->manifestEntry)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $reg->pilgrim?->name ?? '-' }}</td>
                    <td>{{ $reg->pilgrim?->gender?->value === 'female' ? 'P' : 'L' }}</td>
                    <td>{{ $reg->pilgrim?->passport_number ?? '-' }}</td>
                    <td>{{ $m?->room_number ?? '-' }}</td>
                    <td>{{ $m?->room_type ?? '-' }}</td>
                    <td>{{ $m?->bus_number ?? '-' }}</td>
                    <td>{{ $m?->seat_number ?? '-' }}</td>
                    <td>{{ $m?->mahram_group ?? ($reg->pilgrim?->mahram_name ?? '-') }}</td>
                </tr>
            @empty
                <tr><td colspan="9" style="text-align:center;padding:12px;color:#888;">Belum ada jamaah aktif pada paket ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top:8px;font-size:8px;color:#666;">
        Total jamaah aktif: {{ $registrations->count() }} ·
        Dicetak {{ format_datetime(now()) }}
    </p>
</body>
</html>
