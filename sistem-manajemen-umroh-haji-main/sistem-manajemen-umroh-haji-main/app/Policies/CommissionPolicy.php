<?php

namespace App\Policies;

use App\Models\Commission;
use App\Models\User;
use App\Policies\Concerns\ChecksBranchAccess;

class CommissionPolicy
{
    use ChecksBranchAccess;

    public function viewAny(User $user): bool
    {
        return $user->isStaff() || $user->isAgent();
    }

    public function view(User $user, Commission $commission): bool
    {
        return match (true) {
            $user->isStaff() => $this->sameBranch($user, $commission->branch_id),
            $user->isAgent() => $commission->agent?->user_id === $user->id,
            default => false,
        };
    }

    /** Approving / paying commissions is staff-only. */
    public function manage(User $user, Commission $commission): bool
    {
        return $user->isStaff() && $this->sameBranch($user, $commission->branch_id);
    }

    public function update(User $user, Commission $commission): bool
    {
        return $this->manage($user, $commission);
    }

    public function delete(User $user, Commission $commission): bool
    {
        return $this->manage($user, $commission);
    }
}
