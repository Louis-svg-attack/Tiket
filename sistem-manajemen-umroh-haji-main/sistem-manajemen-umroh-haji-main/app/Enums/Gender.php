<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumHelpers;

enum Gender: string
{
    use HasEnumHelpers;

    case Male = 'male';
    case Female = 'female';

    public function label(): string
    {
        return match ($this) {
            self::Male => 'Laki-laki',
            self::Female => 'Perempuan',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Male => 'blue',
            self::Female => 'pink',
        };
    }
}
