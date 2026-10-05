<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\ManasikAttendance;
use App\Models\ManasikSchedule;
use Illuminate\Support\Facades\Auth;

class ManasikService
{
    /**
     * Persist attendance for a manasik schedule.
     *
     * @param  array<int|string, string>  $rows  [pilgrim_id => status]
     */
    public function saveAttendance(ManasikSchedule $schedule, array $rows): void
    {
        $userId = Auth::id();

        foreach ($rows as $pilgrimId => $status) {
            $value = AttendanceStatus::tryFromName($status) ?? AttendanceStatus::Absent;

            ManasikAttendance::updateOrCreate(
                ['manasik_schedule_id' => $schedule->id, 'pilgrim_id' => (int) $pilgrimId],
                ['status' => $value, 'recorded_by' => $userId],
            );
        }
    }
}
