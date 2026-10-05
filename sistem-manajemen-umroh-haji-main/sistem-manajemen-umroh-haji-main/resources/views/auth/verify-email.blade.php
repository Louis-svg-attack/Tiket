<x-guest-layout
    title="Verifikasi Email"
    heading="Verifikasi email Anda"
    subheading="Kami telah mengirim tautan verifikasi ke alamat email Anda. Klik tautan tersebut untuk mengaktifkan akun.">

    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.7-9.3a1 1 0 00-1.4-1.4L9 10.6 7.7 9.3a1 1 0 10-1.4 1.4l2 2a1 1 0 001.4 0z" clip-rule="evenodd"/></svg>
            <span>Tautan verifikasi baru telah dikirim ke email Anda.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="auth-btn">Kirim ulang email verifikasi</button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm font-medium text-slate-500 hover:text-slate-700">Keluar</button>
    </form>
</x-guest-layout>
