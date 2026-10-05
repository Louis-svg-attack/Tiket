<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * White-label branding settings, cached forever and auto-flushed on write.
 */
class SettingService
{
    private const CACHE_KEY = 'app.settings.all';

    /** Sensible white-label defaults so the UI never renders blank. */
    public const DEFAULTS = [
        'app_name' => 'Travel Umroh & Haji',
        'app_subtitle' => 'Sistem Manajemen Umroh & Haji',
        'company_address' => 'Jl. Melati No. 1, Jakarta',
        'company_phone' => '+62 21 1234 5678',
        'company_email' => 'info@example.com',
        'footer_text' => '© Travel Umroh & Haji. Seluruh hak cipta dilindungi.',
        'primary_color' => '#0d9488',
        'secondary_color' => '#0f766e',
        'logo_path' => null,
        'favicon_path' => null,
    ];

    /** @return array<string, mixed> */
    public function all(): array
    {
        // During early boot / before migration the table may not exist yet.
        if (! $this->tableExists()) {
            return self::DEFAULTS;
        }

        $stored = Cache::rememberForever(self::CACHE_KEY, function () {
            return Setting::query()->pluck('value', 'key')->toArray();
        });

        return array_merge(self::DEFAULTS, array_filter($stored, fn ($v) => $v !== null));
    }

    public function get(string $key, $default = null)
    {
        $all = $this->all();

        return $all[$key] ?? $default ?? (self::DEFAULTS[$key] ?? null);
    }

    public function set(string $key, $value, string $type = 'string'): void
    {
        if (! $this->tableExists()) {
            return;
        }

        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => 'branding'],
        );

        $this->flush();
    }

    /** @param array<string, mixed> $values */
    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            if ($value === null) {
                continue;
            }
            $this->set($key, $value);
        }
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function tableExists(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Throwable) {
            return false;
        }
    }
}
