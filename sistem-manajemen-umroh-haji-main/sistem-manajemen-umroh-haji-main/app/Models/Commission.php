<?php

namespace App\Models;

use App\Enums\CommissionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id', 'agent_id', 'registration_id', 'base_amount', 'rate',
        'amount', 'status', 'paid_at', 'note',
    ];

    protected function casts(): array
    {
        return [
            'status' => CommissionStatus::class,
            'base_amount' => 'decimal:2',
            'rate' => 'decimal:2',
            'amount' => 'decimal:2',
            'paid_at' => 'date',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
