<x-guest-layout
    title="Konfirmasi Password"
    heading="Konfirmasi password"
    subheading="Area ini bersifat sensitif. Masukkan kembali password Anda untuk melanjutkan.">

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
            <input id="password" name="password" type="password" required autofocus autocomplete="current-password"
                   placeholder="••••••••"
                   class="auth-input !pl-4 @error('password') has-error @enderror">
            @error('password')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="auth-btn !mt-6">Konfirmasi</button>
    </form>
</x-guest-layout>
