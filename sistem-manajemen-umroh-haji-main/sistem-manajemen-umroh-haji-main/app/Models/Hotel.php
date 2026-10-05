<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'city', 'star', 'distance_to_masjid', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'star' => 'integer',
            'distance_to_masjid' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
