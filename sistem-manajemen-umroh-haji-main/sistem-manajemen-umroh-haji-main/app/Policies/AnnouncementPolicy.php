<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use App\Policies\Concerns\ChecksBranchAccess;

class AnnouncementPolicy
{
    use ChecksBranchAccess;

    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Announcement $announcement): bool
    {
        return $user->isStaff() && $this->sameBranch($user, $announcement->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->isStaff() && $this->sameBranch($user, $announcement->branch_id);
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $this->update($user, $announcement);
    }
}
