<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 11px; color: #222; margin: 0; padding: 18px 24px; }
        .head { border-bottom: 2px solid {{ $branding['color'] }}; padding-bottom: 8px; margin-bottom: 12px; }
        .head h1 { margin: 0; font-size: 16px; color: {{ $branding['color'] }}; }
        .head p { margin: 1px 0; font-size: 9px; color: #666; }
        .title { text-align: center; font-size: 13px; font-weight: bold; letter-spacing: 1px; margin: 6px 0 12px; }
        table.meta td { padding: 3px 4px; vertical-align: top; }
        .label { color: #777; width: 110px; }
        .amount-box { margin-top: 10px; background: #f3f4f6; border-radius: 6px; padding: 10px 12px; }
        .amount-box .big { font-size: 18px; font-weight: bold; color: {{ $branding['color'] }}; }
        .terbilang { font-style: italic; font-size: 10px; color: #555; margin-top: 4px; }
        .sign { margin-top: 26px; width: 100%; }
        .sign td { text-align: center; font-size: 10px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    @php($reg = $payment->registration)
    <div class="head">
        <h1>{{ $branding['name'] }}</h1>
        <p>{{ $branding['subtitle'] }}</p>
        <p>{{ $branding['address'] }} · {{ $branding['phone'] }} · {{ $branding['email'] }}</p>
    </div>

    <div class="title">KWITANSI PEMBAYARAN</div>

    <table class="meta" width="100%">
        <tr>
            <td class="label">No. Kwitansi</td><td><strong>{{ $payment->code }}</strong></td>
            <td class="label">Tanggal</td><td>{{ format_date($payment->paid_at ?? $payment->created_at) }}</td>
        </tr>
        <tr>
            <td class="label">Diterima dari</td><td>{{ $reg?->pilgrim?->name ?? '-' }}</td>
            <td class="label">Jenis</td><td><span class="badge">{{ $payment->type?->label() ?? '-' }}</span></td>
        </tr>
        <tr>
            <td class="label">Paket</td><td>{{ $reg?->package?->name ?? '-' }}</td>
            <td class="label">No. Pendaftaran</td><td>{{ $reg?->code ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Metode</td><td>{{ $payment->method ? ucfirst($payment->method) : '-' }}</td>
            <td class="label">Via Bank</td><td>{{ $payment->bankAccount?->bank_name ?? '-' }}</td>
        </tr>
    </table>

    <div class="amount-box">
        <span style="font-size:10px;color:#777;">Jumlah Dibayar</span><br>
        <span class="big">{{ currency($payment->paid_amount) }}</span>
        <div class="terbilang">Terbilang: {{ terbilang($payment->paid_amount) }}</div>
    </div>

    <table class="meta" width="100%" style="margin-top:8px;">
        <tr>
            <td class="label">Total Tagihan Paket</td><td>{{ currency($reg?->total_price) }}</td>
            <td class="label">Sisa Tagihan</td><td>{{ currency($reg ? $reg->outstanding() : 0) }}</td>
        </tr>
    </table>

    <table class="sign">
        <tr>
            <td></td>
            <td>
                {{ $branding['phone'] ? '' : '' }}
                Penerima,<br><br><br>
                <strong>( {{ $payment->recorder?->name ?? '..........................' }} )</strong>
            </td>
        </tr>
    </table>
</body>
</html>
