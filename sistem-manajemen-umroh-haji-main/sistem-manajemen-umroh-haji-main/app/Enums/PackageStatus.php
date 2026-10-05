<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumHelpers;

enum PackageStatus: string
{
    use HasEnumHelpers;

    case Draft = 'draft';
    case Open = 'open';
    case Closed = 'closed';
    case Departed = 'departed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Open => 'Dibuka',
            self::Closed => 'Ditutup',
            self::Departed => 'Berangkat',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Open => 'green',
            self::Closed => 'amber',
            self::Departed => 'blue',
        };
    }
}
