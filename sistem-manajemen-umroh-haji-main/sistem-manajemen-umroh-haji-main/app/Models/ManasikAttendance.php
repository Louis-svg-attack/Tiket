<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManasikAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'manasik_schedule_id', 'pilgrim_id', 'status', 'note', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => AttendanceStatus::class,
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ManasikSchedule::class, 'manasik_schedule_id');
    }

    public function pilgrim(): BelongsTo
    {
        return $this->belongsTo(Pilgrim::class);
    }
}
