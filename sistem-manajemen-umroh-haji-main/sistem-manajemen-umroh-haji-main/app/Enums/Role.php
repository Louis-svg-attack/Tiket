<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumHelpers;

enum Role: string
{
    use HasEnumHelpers;

    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Agent = 'agent';
    case Pilgrim = 'pilgrim';
    case Guide = 'guide';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin Cabang',
            self::Agent => 'Agen / Marketing',
            self::Pilgrim => 'Jamaah',
            self::Guide => 'Muthawif / Pembimbing',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::SuperAdmin => 'purple',
            self::Admin => 'blue',
            self::Agent => 'amber',
            self::Pilgrim => 'green',
            self::Guide => 'teal',
        };
    }

    /** Staff roles manage the back office. */
    public function isStaff(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin], true);
    }
}
