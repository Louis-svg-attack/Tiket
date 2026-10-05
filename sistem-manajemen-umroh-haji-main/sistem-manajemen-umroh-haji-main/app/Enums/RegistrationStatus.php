<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumHelpers;

enum RegistrationStatus: string
{
    use HasEnumHelpers;

    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case DocumentComplete = 'document_complete';
    case Paid = 'paid';
    case Departed = 'departed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Confirmed => 'Dikonfirmasi',
            self::DocumentComplete => 'Dokumen Lengkap',
            self::Paid => 'Lunas',
            self::Departed => 'Berangkat',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Confirmed => 'blue',
            self::DocumentComplete => 'teal',
            self::Paid => 'green',
            self::Departed => 'purple',
            self::Cancelled => 'red',
        };
    }

    public function isActive(): bool
    {
        return $this !== self::Cancelled;
    }
}
