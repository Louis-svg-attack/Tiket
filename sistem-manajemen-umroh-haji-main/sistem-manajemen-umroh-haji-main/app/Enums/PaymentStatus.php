<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumHelpers;

enum PaymentStatus: string
{
    use HasEnumHelpers;

    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Belum Bayar',
            self::Partial => 'Sebagian',
            self::Paid => 'Lunas',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Unpaid => 'red',
            self::Partial => 'amber',
            self::Paid => 'green',
        };
    }
}
