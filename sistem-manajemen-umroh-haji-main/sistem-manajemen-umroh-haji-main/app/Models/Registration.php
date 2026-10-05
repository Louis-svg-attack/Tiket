<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'pilgrim_id', 'package_id', 'agent_id', 'guide_id',
        'code', 'status', 'total_price', 'registered_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
            'total_price' => 'decimal:2',
            'registered_at' => 'date',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function pilgrim(): BelongsTo
    {
        return $this->belongsTo(Pilgrim::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function guide(): BelongsTo
    {
        return $this->belongsTo(Guide::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }

    public function manifestEntry(): HasOne
    {
        return $this->hasOne(ManifestEntry::class);
    }

    public function equipmentDistributions(): HasMany
    {
        return $this->hasMany(EquipmentDistribution::class);
    }

    // Money helpers (null-safe) -------------------------------------------

    public function totalPaid(): float
    {
        return (float) $this->payments->sum('paid_amount');
    }

    public function totalBilled(): float
    {
        $billed = (float) $this->payments->sum('amount');

        return $billed > 0 ? $billed : (float) $this->total_price;
    }

    public function outstanding(): float
    {
        return max(0, (float) $this->total_price - $this->totalPaid());
    }

    public function paymentStatus(): PaymentStatus
    {
        $paid = $this->totalPaid();
        if ($paid <= 0) {
            return PaymentStatus::Unpaid;
        }

        return $paid >= (float) $this->total_price ? PaymentStatus::Paid : PaymentStatus::Partial;
    }
}
