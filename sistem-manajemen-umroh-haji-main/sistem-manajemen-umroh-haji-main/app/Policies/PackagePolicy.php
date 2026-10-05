<?php

namespace App\Policies;

use App\Models\Package;
use App\Models\User;
use App\Policies\Concerns\ChecksBranchAccess;

class PackagePolicy
{
    use ChecksBranchAccess;

    public function viewAny(User $user): bool
    {
        // Staff manage; agents browse to register jamaah.
        return $user->isStaff() || $user->isAgent();
    }

    public function view(User $user, Package $package): bool
    {
        if ($user->isStaff() || $user->isAgent()) {
            return $this->sameBranch($user, $package->branch_id) || $user->isAgent();
        }

        return true; // pilgrims/guides can view package detail of their booking
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Package $package): bool
    {
        return $user->isStaff() && $this->sameBranch($user, $package->branch_id);
    }

    public function delete(User $user, Package $package): bool
    {
        return $user->isStaff() && $this->sameBranch($user, $package->branch_id);
    }
}
