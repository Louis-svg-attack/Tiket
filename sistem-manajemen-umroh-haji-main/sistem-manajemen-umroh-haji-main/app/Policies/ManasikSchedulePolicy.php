<?php

namespace App\Policies;

use App\Models\ManasikSchedule;
use App\Models\User;
use App\Policies\Concerns\ChecksBranchAccess;

class ManasikSchedulePolicy
{
    use ChecksBranchAccess;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ManasikSchedule $schedule): bool
    {
        return match (true) {
            $user->isStaff() => $this->sameBranch($user, $schedule->branch_id),
            $user->isGuide() => $schedule->guide?->user_id === $user->id,
            default => $schedule->branch_id === $user->branch_id || $user->isPilgrim(),
        };
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, ManasikSchedule $schedule): bool
    {
        return $user->isStaff() && $this->sameBranch($user, $schedule->branch_id);
    }

    public function delete(User $user, ManasikSchedule $schedule): bool
    {
        return $user->isStaff() && $this->sameBranch($user, $schedule->branch_id);
    }

    /** Staff and the assigned guide may record attendance. */
    public function recordAttendance(User $user, ManasikSchedule $schedule): bool
    {
        if ($user->isStaff()) {
            return $this->sameBranch($user, $schedule->branch_id);
        }

        return $user->isGuide() && $schedule->guide?->user_id === $user->id;
    }
}
