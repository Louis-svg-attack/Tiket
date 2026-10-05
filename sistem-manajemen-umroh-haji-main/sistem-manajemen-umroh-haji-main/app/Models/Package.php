<?php

namespace App\Models;

use App\Enums\PackageStatus;
use App\Enums\PackageType;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'name', 'code', 'type', 'price', 'down_payment', 'quota',
        'duration_days', 'departure_date', 'departure_city',
        'hotel_makkah_id', 'hotel_madinah_id', 'airline_id',
        'facilities_included', 'facilities_excluded', 'description', 'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => PackageType::class,
            'status' => PackageStatus::class,
            'price' => 'decimal:2',
            'down_payment' => 'decimal:2',
            'quota' => 'integer',
            'duration_days' => 'integer',
            'departure_date' => 'date',
            'facilities_included' => 'array',
            'facilities_excluded' => 'array',
        ];
    }

    // Relations -----------------------------------------------------------

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function hotelMakkah(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'hotel_makkah_id');
    }

    public function hotelMadinah(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'hotel_madinah_id');
    }

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function manasikSchedules(): HasMany
    {
        return $this->hasMany(ManasikSchedule::class);
    }

    // Computed (null-safe) -------------------------------------------------

    public function activeRegistrationsCount(): int
    {
        return $this->registrations()
            ->where('status', '!=', RegistrationStatus::Cancelled->value)
            ->count();
    }

    public function remainingQuota(): int
    {
        return max(0, (int) $this->quota - $this->activeRegistrationsCount());
    }

    public function isFull(): bool
    {
        return $this->remainingQuota() <= 0;
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', PackageStatus::Open->value);
    }
}
