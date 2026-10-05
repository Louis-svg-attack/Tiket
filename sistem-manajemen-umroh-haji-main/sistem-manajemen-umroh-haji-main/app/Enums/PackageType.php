<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumHelpers;

enum PackageType: string
{
    use HasEnumHelpers;

    case Umroh = 'umroh';
    case Haji = 'haji';
    case HajiPlus = 'haji_plus';

    public function label(): string
    {
        return match ($this) {
            self::Umroh => 'Umroh',
            self::Haji => 'Haji',
            self::HajiPlus => 'Haji Plus',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Umroh => 'green',
            self::Haji => 'blue',
            self::HajiPlus => 'purple',
        };
    }
}
