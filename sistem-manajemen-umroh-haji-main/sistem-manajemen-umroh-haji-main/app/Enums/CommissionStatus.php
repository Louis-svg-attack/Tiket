<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumHelpers;

enum CommissionStatus: string
{
    use HasEnumHelpers;

    case Pending = 'pending';
    case Approved = 'approved';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Approved => 'Disetujui',
            self::Paid => 'Dibayar',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Approved => 'blue',
            self::Paid => 'green',
            self::Cancelled => 'red',
        };
    }
}
