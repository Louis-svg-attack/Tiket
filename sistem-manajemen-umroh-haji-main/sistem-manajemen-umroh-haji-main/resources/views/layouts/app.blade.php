<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ setting('app_name') }} — {{ setting('app_subtitle') }}</title>

    @if($favicon = setting('favicon_path'))
        <link rel="icon" href="{{ asset('storage/'.$favicon) }}">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <style>
        :root {
            --brand: {{ setting('primary_color', '#0d9488') }};
            --brand-dark: {{ setting('secondary_color', '#0f766e') }};
        }
        .bg-brand { background-color: var(--brand); }
        .bg-brand-dark { background-color: var(--brand-dark); }
        .text-brand { color: var(--brand); }
        .border-brand { border-color: var(--brand); }
        .hover\:bg-brand-dark:hover { background-color: var(--brand-dark); }
        .nav-active { background-color: rgba(13,148,136,.12); color: var(--brand-dark); font-weight: 600; }
        [x-cloak] { display: none !important; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-800">
<div x-data="{ sidebarOpen: false }" class="min-h-screen lg:flex">

    @include('layouts.partials.sidebar')

    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 z-20 bg-black/40 lg:hidden"></div>

    <div class="flex-1 flex flex-col min-w-0">
        @include('layouts.partials.topbar')

        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @if(session('status') || session('success'))
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    {{ session('status') ?? session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('generated_password'))
                <div class="mb-4 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800">
                    <strong>Akun login dibuat.</strong> Password sementara:
                    <code class="px-2 py-0.5 bg-white rounded border">{{ session('generated_password') }}</code>
                    — mohon catat &amp; sampaikan ke pengguna.
                </div>
            @endif

            @isset($header)
                <div class="mb-6">{{ $header }}</div>
            @endisset

            {{ $slot }}
        </main>

        <footer class="px-6 py-4 text-center text-xs text-gray-400">
            <div>{{ setting('footer_text') }}</div>
            <div class="mt-1">
                &copy; {{ date('Y') }}
                <a href="https://id.shp.ee/gteU4cYe" target="_blank" rel="noopener"
                   class="font-medium text-brand hover:underline">Toko Source Code</a>
            </div>
        </footer>
    </div>
</div>
@stack('scripts')
</body>
</html>
