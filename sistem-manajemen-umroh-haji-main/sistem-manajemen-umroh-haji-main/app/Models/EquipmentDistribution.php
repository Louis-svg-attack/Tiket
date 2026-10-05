<?php

namespace App\Models;

use App\Enums\EquipmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id', 'item', 'size', 'quantity', 'status',
        'handed_at', 'handed_by', 'note',
    ];

    protected function casts(): array
    {
        return [
            'status' => EquipmentStatus::class,
            'quantity' => 'integer',
            'handed_at' => 'date',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function handedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handed_by');
    }
}
