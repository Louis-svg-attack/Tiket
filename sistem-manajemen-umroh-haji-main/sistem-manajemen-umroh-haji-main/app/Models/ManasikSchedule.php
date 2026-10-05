<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManasikSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'package_id', 'guide_id', 'title', 'date',
        'start_time', 'end_time', 'location', 'description',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function guide(): BelongsTo
    {
        return $this->belongsTo(Guide::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(ManasikAttendance::class);
    }

    public function presentCount(): int
    {
        return $this->attendances->where('status', AttendanceStatus::Present)->count();
    }
}
