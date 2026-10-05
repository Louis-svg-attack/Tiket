<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumHelpers;

enum DocumentStatus: string
{
    use HasEnumHelpers;

    case Pending = 'pending';
    case Uploaded = 'uploaded';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Belum Diunggah',
            self::Uploaded => 'Sudah Diunggah',
            self::Verified => 'Terverifikasi',
            self::Rejected => 'Ditolak',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Uploaded => 'amber',
            self::Verified => 'green',
            self::Rejected => 'red',
        };
    }
}
