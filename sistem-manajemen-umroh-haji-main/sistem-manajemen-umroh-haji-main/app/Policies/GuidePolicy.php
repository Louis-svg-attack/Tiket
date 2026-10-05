<?php

namespace App\Policies;

use App\Models\Guide;
use App\Models\User;
use App\Policies\Concerns\ChecksBranchAccess;

class GuidePolicy
{
    use ChecksBranchAccess;

    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Guide $guide): bool
    {
        if ($user->isStaff()) {
            return $this->sameBranch($user, $guide->branch_id);
        }

        return $user->isGuide() && $guide->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Guide $guide): bool
    {
        return $user->isStaff() && $this->sameBranch($user, $guide->branch_id);
    }

    public function delete(User $user, Guide $guide): bool
    {
        return $user->isStaff() && $this->sameBranch($user, $guide->branch_id);
    }
}
