<?php

use App\Models\User;
use App\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

if (! function_exists('currency')) {
    /**
     * Format a number as Indonesian Rupiah. Null-safe.
     */
    function currency(int|float|string|null $amount, bool $withSymbol = true): string
    {
        $value = (float) ($amount ?? 0);
        $formatted = number_format($value, 0, ',', '.');

        return $withSymbol ? 'Rp '.$formatted : $formatted;
    }
}

if (! function_exists('terbilang')) {
    /**
     * Spell out an integer amount in Indonesian words (for kwitansi).
     */
    function terbilang(int|float|string|null $number): string
    {
        $number = (int) abs((float) ($number ?? 0));

        $words = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima',
            'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas',
        ];

        $spell = function ($n) use (&$spell, $words) {
            if ($n < 12) {
                return ' '.$words[$n];
            }
            if ($n < 20) {
                return $spell($n - 10).' belas';
            }
            if ($n < 100) {
                return $spell(intval($n / 10)).' puluh'.$spell($n % 10);
            }
            if ($n < 200) {
                return ' seratus'.$spell($n - 100);
            }
            if ($n < 1000) {
                return $spell(intval($n / 100)).' ratus'.$spell($n % 100);
            }
            if ($n < 2000) {
                return ' seribu'.$spell($n - 1000);
            }
            if ($n < 1000000) {
                return $spell(intval($n / 1000)).' ribu'.$spell($n % 1000);
            }
            if ($n < 1000000000) {
                return $spell(intval($n / 1000000)).' juta'.$spell($n % 1000000);
            }
            if ($n < 1000000000000) {
                return $spell(intval($n / 1000000000)).' miliar'.$spell($n % 1000000000);
            }

            return $spell(intval($n / 1000000000000)).' triliun'.$spell($n % 1000000000000);
        };

        $result = trim(preg_replace('/\s+/', ' ', $spell($number)));

        return ucwords($result === '' ? 'nol' : $result).' Rupiah';
    }
}

if (! function_exists('format_date')) {
    /**
     * Format a date in Indonesian. Null-safe — returns the placeholder when empty.
     */
    function format_date($date, string $format = 'd M Y', string $placeholder = '-'): string
    {
        if (empty($date)) {
            return $placeholder;
        }

        try {
            return Carbon::parse($date)->translatedFormat($format);
        } catch (Throwable) {
            return $placeholder;
        }
    }
}

if (! function_exists('format_datetime')) {
    function format_datetime($date, string $placeholder = '-'): string
    {
        return format_date($date, 'd M Y H:i', $placeholder);
    }
}

if (! function_exists('wa_link')) {
    /**
     * Build a wa.me link from an Indonesian phone number. Null-safe.
     */
    function wa_link(?string $phone, ?string $text = null): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (! str_starts_with($digits, '62')) {
            $digits = '62'.$digits;
        }

        $url = 'https://wa.me/'.$digits;
        if ($text) {
            $url .= '?text='.rawurlencode($text);
        }

        return $url;
    }
}

if (! function_exists('setting')) {
    /**
     * Read a white-label branding setting (cached). Null-safe with default.
     */
    function setting(string $key, $default = null)
    {
        return app(SettingService::class)->get($key, $default);
    }
}

if (! function_exists('current_user')) {
    function current_user(): ?User
    {
        return Auth::user();
    }
}

if (! function_exists('percent')) {
    /**
     * Safe percentage that never divides by zero.
     */
    function percent(int|float|null $part, int|float|null $total, int $decimals = 0): float
    {
        $total = (float) ($total ?? 0);
        if ($total <= 0) {
            return 0.0;
        }

        return round(((float) ($part ?? 0) / $total) * 100, $decimals);
    }
}

if (! function_exists('initials')) {
    function initials(?string $name, int $max = 2): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '?';
        }

        $parts = preg_split('/\s+/', $name);
        $letters = array_map(static fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)), $parts);

        return implode('', array_slice($letters, 0, $max));
    }
}
