<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { margin: 0; padding: 0; }
        .card { width: 100%; height: 100%; padding: 12px 16px; box-sizing: border-box; }
        .top { border-bottom: 2px solid {{ $branding['color'] }}; padding-bottom: 4px; margin-bottom: 8px; }
        .top h1 { margin: 0; font-size: 13px; color: {{ $branding['color'] }}; }
        .top p { margin: 0; font-size: 7px; color: #666; }
        .name { font-size: 14px; font-weight: bold; margin: 2px 0; }
        table.info { width: 100%; font-size: 9px; }
        table.info td { padding: 2px 3px; vertical-align: top; }
        .lbl { color: #888; width: 70px; }
        .pill { display:inline-block; padding:2px 8px; border-radius:10px; background:{{ $branding['color'] }}; color:#fff; font-size:8px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="top">
            <h1>{{ $branding['name'] }}</h1>
            <p>KARTU JAMAAH · {{ $branding['phone'] }}</p>
        </div>

        <div class="name">{{ $pilgrim?->name ?? '-' }}</div>
        <span class="pill">{{ $package?->type?->label() ?? 'Umroh/Haji' }}</span>

        <table class="info" style="margin-top:8px;">
            <tr><td class="lbl">No. Daftar</td><td><strong>{{ $registration->code }}</strong></td></tr>
            <tr><td class="lbl">Paket</td><td>{{ $package?->name ?? '-' }}</td></tr>
            <tr><td class="lbl">Berangkat</td><td>{{ format_date($package?->departure_date) }}</td></tr>
            <tr><td class="lbl">No. Paspor</td><td>{{ $pilgrim?->passport_number ?? '-' }}</td></tr>
            <tr><td class="lbl">Maskapai</td><td>{{ $package?->airline?->name ?? '-' }}</td></tr>
            <tr><td class="lbl">Kontak</td><td>{{ $pilgrim?->phone ?? '-' }}</td></tr>
            <tr><td class="lbl">Darurat</td><td>{{ $pilgrim?->emergency_contact_name ?? '-' }} ({{ $pilgrim?->emergency_contact_phone ?? '-' }})</td></tr>
        </table>
    </div>
</body>
</html>
