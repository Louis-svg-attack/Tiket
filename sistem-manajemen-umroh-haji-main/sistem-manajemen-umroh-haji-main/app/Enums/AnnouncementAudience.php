<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumHelpers;

enum AnnouncementAudience: string
{
    use HasEnumHelpers;

    case All = 'all';
    case Staff = 'staff';
    case Agents = 'agent';
    case Pilgrims = 'pilgrim';
    case Guides = 'guide';

    public function label(): string
    {
        return match ($this) {
            self::All => 'Semua Pengguna',
            self::Staff => 'Staff & Admin',
            self::Agents => 'Agen',
            self::Pilgrims => 'Jamaah',
            self::Guides => 'Muthawif',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::All => 'purple',
            self::Staff => 'blue',
            self::Agents => 'amber',
            self::Pilgrims => 'green',
            self::Guides => 'teal',
        };
    }
}
