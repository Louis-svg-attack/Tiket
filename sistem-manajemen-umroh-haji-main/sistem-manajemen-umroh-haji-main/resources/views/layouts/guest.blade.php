@props(['title' => null, 'heading' => null, 'subheading' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ setting('primary_color', '#0d9488') }}">
    <title>{{ $title ?? 'Masuk' }} — {{ setting('app_name') }}</title>
    @if($favicon = setting('favicon_path'))
        <link rel="icon" href="{{ asset('storage/'.$favicon) }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root{
            --brand: {{ setting('primary_color', '#0d9488') }};
            --brand-dark: {{ setting('secondary_color', '#0f766e') }};
        }
        .bg-brand{background-color:var(--brand)}
        .text-brand{color:var(--brand)}
        .border-brand{border-color:var(--brand)}
        [x-cloak]{display:none!important}

        /* Panel branding kiri */
        .auth-hero{
            background:
                radial-gradient(120% 90% at 100% 0%, rgba(255,255,255,.18) 0%, transparent 55%),
                radial-gradient(90% 70% at 0% 100%, rgba(0,0,0,.28) 0%, transparent 60%),
                linear-gradient(150deg, var(--brand) 0%, var(--brand-dark) 100%);
        }
        /* Ornamen geometris islami */
        .auth-pattern{
            background-image:
                radial-gradient(circle at 50% 0,   rgba(255,255,255,.10) 1.5px, transparent 1.6px),
                radial-gradient(circle at 0   50%, rgba(255,255,255,.10) 1.5px, transparent 1.6px);
            background-size: 34px 34px;
        }
        .auth-input{
            width:100%;
            border-radius:.75rem;
            border:1px solid #e2e8f0;
            background:#fff;
            padding:.7rem .95rem .7rem 2.75rem;
            font-size:.925rem;
            color:#0f172a;
            transition:border-color .15s, box-shadow .15s, background-color .15s;
        }
        .auth-input::placeholder{color:#94a3b8}
        .auth-input:focus{
            outline:none;
            border-color:var(--brand);
            box-shadow:0 0 0 4px color-mix(in srgb, var(--brand) 18%, transparent);
        }
        .auth-input.has-error{border-color:#ef4444}
        .auth-btn{
            width:100%;
            display:inline-flex;align-items:center;justify-content:center;gap:.5rem;
            border-radius:.75rem;
            padding:.75rem 1rem;
            font-weight:600;font-size:.95rem;color:#fff;
            background:linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
            box-shadow:0 10px 20px -10px color-mix(in srgb, var(--brand) 70%, transparent);
            transition:transform .12s, filter .15s, box-shadow .15s;
        }
        .auth-btn:hover{filter:brightness(1.06);box-shadow:0 14px 26px -12px color-mix(in srgb, var(--brand) 80%, transparent)}
        .auth-btn:active{transform:translateY(1px)}
        .auth-btn:focus-visible{outline:2px solid var(--brand);outline-offset:2px}
    </style>
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50">
<div class="min-h-screen lg:grid lg:grid-cols-[1.05fr_1fr]">

    {{-- ================= Panel branding (desktop) ================= --}}
    <aside class="hidden lg:flex auth-hero relative flex-col justify-between p-12 text-white overflow-hidden">
        <div class="auth-pattern absolute inset-0 opacity-70"></div>

        {{-- siluet kubah & menara --}}
        <svg class="absolute bottom-0 left-0 w-full opacity-[.13]" viewBox="0 0 600 200" fill="currentColor" aria-hidden="true">
            <path d="M60 200v-90a12 12 0 0124 0v90zM66 96c0-12 6-18 6-26s-6-10-6-18c0 8-6 10-6 18s6 14 6 26z"/>
            <path d="M540 200v-90a12 12 0 0124 0v90zM546 96c0-12 6-18 6-26s-6-10-6-18c0 8-6 10-6 18s6 14 6 26z"/>
            <path d="M120 200v-70h360v70zM300 40c50 26 80 56 80 90H220c0-34 30-64 80-90zM300 10c8 10 8 20 0 30-8-10-8-20 0-30z"/>
            <path d="M160 130c0-26 18-42 40-42s40 16 40 42zM360 130c0-26 18-42 40-42s40 16 40 42z"/>
        </svg>

        <div class="relative">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-3">
                @if($logo = setting('logo_path'))
                    <img src="{{ asset('storage/'.$logo) }}" class="h-12 w-auto rounded-xl bg-white/95 p-1.5" alt="{{ setting('app_name') }}">
                @else
                    <span class="h-12 w-12 grid place-items-center rounded-xl bg-white/15 ring-1 ring-white/30 text-lg font-extrabold backdrop-blur">
                        {{ initials(setting('app_name')) }}
                    </span>
                @endif
                <span class="leading-tight">
                    <span class="block font-bold text-lg">{{ setting('app_name') }}</span>
                    <span class="block text-xs text-white/70">{{ setting('app_subtitle') }}</span>
                </span>
            </a>
        </div>

        <div class="relative max-w-lg">
            <p class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-medium ring-1 ring-white/25 backdrop-blur">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                Sistem Terintegrasi Multi-Cabang
            </p>
            <h2 class="mt-5 text-4xl font-extrabold leading-tight tracking-tight">
                Kelola perjalanan ibadah<br>dengan lebih tenang.
            </h2>
            <p class="mt-4 text-white/75 leading-relaxed">
                Pendaftaran jamaah, dokumen, pembayaran, manasik, manifest keberangkatan,
                hingga komisi agen — semuanya rapi dalam satu platform.
            </p>

            <ul class="mt-8 space-y-3.5 text-sm">
                @foreach ([
                    'Data jamaah &amp; dokumen terpusat dan aman',
                    'Pembayaran, cicilan, dan kuitansi otomatis',
                    'Manasik, manifest, serta komisi agen real-time',
                ] as $point)
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-white/20 ring-1 ring-white/30">
                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                        </span>
                        <span class="text-white/85">{!! $point !!}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <p class="relative text-xs text-white/55">{{ setting('footer_text') }}</p>
    </aside>

    {{-- ================= Kolom form ================= --}}
    <main class="flex min-h-screen flex-col justify-center px-6 py-10 sm:px-10 lg:px-14 bg-white">
        <div class="mx-auto w-full max-w-[26rem]">

            {{-- Logo untuk mobile --}}
            <div class="lg:hidden mb-8 flex flex-col items-center text-center">
                @if($logo = setting('logo_path'))
                    <img src="{{ asset('storage/'.$logo) }}" class="h-14 w-auto" alt="{{ setting('app_name') }}">
                @else
                    <span class="h-14 w-14 grid place-items-center rounded-2xl bg-brand text-white text-xl font-extrabold shadow-lg">
                        {{ initials(setting('app_name')) }}
                    </span>
                @endif
                <p class="mt-3 font-bold text-slate-800">{{ setting('app_name') }}</p>
                <p class="text-xs text-slate-400">{{ setting('app_subtitle') }}</p>
            </div>

            <header class="mb-7">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
                    {{ $heading ?? 'Selamat datang kembali' }}
                </h1>
                <p class="mt-1.5 text-sm text-slate-500">
                    {{ $subheading ?? 'Masuk menggunakan akun Anda untuk melanjutkan.' }}
                </p>
            </header>

            {{ $slot }}

            <p class="mt-10 text-center text-[11px] text-slate-400">
                &copy; {{ date('Y') }}
                <a href="https://id.shp.ee/gteU4cYe" target="_blank" rel="noopener"
                   class="font-medium text-brand hover:underline">Toko Source Code</a>
            </p>
        </div>
    </main>
</div>
</body>
</html>
