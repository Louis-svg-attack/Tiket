<?php

namespace App\Models;

use App\Enums\Role;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'branch_id',
        'phone',
        'avatar_path',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
        ];
    }

    // ---------------------------------------------------------------------
    // Relations
    // ---------------------------------------------------------------------

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function pilgrim(): HasOne
    {
        return $this->hasOne(Pilgrim::class);
    }

    public function agent(): HasOne
    {
        return $this->hasOne(Agent::class);
    }

    public function guide(): HasOne
    {
        return $this->hasOne(Guide::class);
    }

    // ---------------------------------------------------------------------
    // Role helpers (null-safe)
    // ---------------------------------------------------------------------

    public function hasRole(Role|string $role): bool
    {
        return $this->role === ($role instanceof Role ? $role : Role::tryFrom((string) $role));
    }

    /**
     * Send the password reset notification (branded, Indonesian).
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === Role::SuperAdmin;
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isStaff(): bool
    {
        return $this->role?->isStaff() ?? false;
    }

    public function isAgent(): bool
    {
        return $this->role === Role::Agent;
    }

    public function isPilgrim(): bool
    {
        return $this->role === Role::Pilgrim;
    }

    public function isGuide(): bool
    {
        return $this->role === Role::Guide;
    }

    public function roleLabel(): string
    {
        return $this->role?->label() ?? '-';
    }
}
