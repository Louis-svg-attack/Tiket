<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumHelpers;

enum EquipmentStatus: string
{
    use HasEnumHelpers;

    case Pending = 'pending';
    case Prepared = 'prepared';
    case HandedOver = 'handed_over';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Belum Disiapkan',
            self::Prepared => 'Disiapkan',
            self::HandedOver => 'Sudah Diserahkan',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Prepared => 'amber',
            self::HandedOver => 'green',
        };
    }
}
