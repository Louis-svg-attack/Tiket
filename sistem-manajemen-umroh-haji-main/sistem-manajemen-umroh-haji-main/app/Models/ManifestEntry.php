<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManifestEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id', 'registration_id', 'room_number', 'room_type',
        'bus_number', 'seat_number', 'mahram_group', 'note',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
