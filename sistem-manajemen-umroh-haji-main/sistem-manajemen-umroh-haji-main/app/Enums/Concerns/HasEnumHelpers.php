<?php

namespace App\Enums\Concerns;

/**
 * Shared helpers for backed string enums.
 *
 * Every enum in this app exposes:
 *  - label()       human readable Indonesian label
 *  - badgeColor()  tailwind colour key used by <x-badge>
 *  - options()     [value => label] map for <select> dropdowns
 */
trait HasEnumHelpers
{
    /** @return array<string, string> value => label */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }

    /** @return array<int, string> raw backing values */
    public static function values(): array
    {
        return array_map(static fn ($case) => $case->value, self::cases());
    }

    /** Safe resolver that never throws on bad/null input. */
    public static function tryFromName(?string $value): ?static
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom($value);
    }

    /** Resolve a label from a possibly-null raw value without throwing. */
    public static function labelFor(?string $value, string $default = '-'): string
    {
        return self::tryFromName($value)?->label() ?? $default;
    }

    /** Resolve a badge colour from a possibly-null raw value without throwing. */
    public static function colorFor(?string $value, string $default = 'gray'): string
    {
        return self::tryFromName($value)?->badgeColor() ?? $default;
    }
}
