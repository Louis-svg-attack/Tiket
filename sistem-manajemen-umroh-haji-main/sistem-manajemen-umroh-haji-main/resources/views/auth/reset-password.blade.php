<x-guest-layout
    title="Reset Password"
    heading="Buat password baru"
    subheading="Tentukan password baru untuk akun Anda.">

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}"
                   required autofocus autocomplete="username"
                   class="auth-input !pl-4 @error('email') has-error @enderror">
            @error('email')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password baru</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   placeholder="••••••••"
                   class="auth-input !pl-4 @error('password') has-error @enderror">
            @error('password')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">Ulangi password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   placeholder="••••••••"
                   class="auth-input !pl-4 @error('password_confirmation') has-error @enderror">
            @error('password_confirmation')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="auth-btn !mt-6">Simpan password baru</button>
    </form>
</x-guest-layout>
