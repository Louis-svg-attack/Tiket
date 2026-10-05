<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumHelpers;

enum AttendanceStatus: string
{
    use HasEnumHelpers;

    case Present = 'present';
    case Absent = 'absent';
    case Excused = 'excused';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Hadir',
            self::Absent => 'Tidak Hadir',
            self::Excused => 'Izin',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Present => 'green',
            self::Absent => 'red',
            self::Excused => 'amber',
        };
    }
}
