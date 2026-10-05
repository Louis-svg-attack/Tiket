<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pilgrim extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'user_id', 'agent_id', 'code', 'name', 'gender', 'nik',
        'birth_place', 'birth_date', 'address', 'city', 'phone', 'email',
        'passport_number', 'passport_issued_at', 'passport_expired_at', 'passport_issued_place',
        'mahram_name', 'mahram_relation', 'emergency_contact_name', 'emergency_contact_phone',
        'health_notes', 'blood_type', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'birth_date' => 'date',
            'passport_issued_at' => 'date',
            'passport_expired_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(ManasikAttendance::class);
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Registration::class);
    }

    public function age(): ?int
    {
        return $this->birth_date?->age;
    }

    public function passportExpiringSoon(int $months = 7): bool
    {
        if (! $this->passport_expired_at) {
            return false;
        }

        return $this->passport_expired_at->lessThan(now()->addMonths($months));
    }
}
