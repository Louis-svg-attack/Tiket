<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumHelpers;

enum PaymentType: string
{
    use HasEnumHelpers;

    case DownPayment = 'dp';
    case Installment = 'installment';
    case Settlement = 'settlement';

    public function label(): string
    {
        return match ($this) {
            self::DownPayment => 'Uang Muka (DP)',
            self::Installment => 'Cicilan',
            self::Settlement => 'Pelunasan',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::DownPayment => 'blue',
            self::Installment => 'amber',
            self::Settlement => 'green',
        };
    }
}
