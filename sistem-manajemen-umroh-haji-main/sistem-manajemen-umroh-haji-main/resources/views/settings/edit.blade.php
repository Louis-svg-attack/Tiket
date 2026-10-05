<x-app-layout>
    <x-breadcrumb :items="[['label' => 'White-label']]" />
    <x-page-header title="Pengaturan White-label" subtitle="Branding tampil di seluruh aplikasi (cache otomatis diperbarui)." />

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
        @csrf
        @method('PUT')

        <div class="lg:col-span-2 space-y-4">
            <x-card title="Identitas">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input name="app_name" label="Nama Aplikasi" :value="$settings['app_name'] ?? ''" required />
                    <x-input name="app_subtitle" label="Subjudul" :value="$settings['app_subtitle'] ?? ''" />
                    <x-input name="company_phone" label="Telepon" :value="$settings['company_phone'] ?? ''" />
                    <x-input name="company_email" label="Email" type="email" :value="$settings['company_email'] ?? ''" />
                    <x-input name="company_address" label="Alamat" type="textarea" class="sm:col-span-2" :value="$settings['company_address'] ?? ''" />
                    <x-input name="footer_text" label="Teks Footer" class="sm:col-span-2" :value="$settings['footer_text'] ?? ''" />
                </div>
            </x-card>

            <x-card title="Warna & Logo">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input name="primary_color" label="Warna Utama (hex)" type="color" :value="$settings['primary_color'] ?? '#0d9488'" required />
                    <x-input name="secondary_color" label="Warna Sekunder (hex)" type="color" :value="$settings['secondary_color'] ?? '#0f766e'" required />
                    <x-input name="logo" label="Logo (PNG/JPG, maks 2MB)" type="file" accept="image/*" hint="Kosongkan jika tidak ingin mengubah." />
                    <x-input name="favicon" label="Favicon" type="file" accept="image/*" hint="Kosongkan jika tidak ingin mengubah." />
                </div>
            </x-card>
        </div>

        <div class="space-y-4">
            <x-card title="Pratinjau">
                <div class="flex items-center gap-3">
                    @if($logo = ($settings['logo_path'] ?? null))
                        <img src="{{ asset('storage/'.$logo) }}" class="h-12 w-12 object-contain rounded" alt="logo">
                    @else
                        <div class="h-12 w-12 rounded-lg grid place-items-center text-white font-bold"
                             style="background: {{ $settings['primary_color'] ?? '#0d9488' }}">
                            {{ initials($settings['app_name'] ?? 'KT') }}
                        </div>
                    @endif
                    <div>
                        <p class="font-semibold text-gray-800">{{ $settings['app_name'] ?? 'Travel Umroh & Haji' }}</p>
                        <p class="text-xs text-gray-400">{{ $settings['app_subtitle'] ?? '' }}</p>
                    </div>
                </div>
                <button type="submit" class="mt-5 w-full px-4 py-2.5 rounded-lg bg-brand text-white font-medium hover:bg-brand-dark">
                    Simpan Pengaturan
                </button>
            </x-card>
        </div>
    </form>
</x-app-layout>
