<?php

namespace App\Policies;

use App\Models\User;

/**
 * Shared policy for global master data (hotels, airlines, bank accounts).
 * Managed by staff only.
 */
class MasterDataPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, $model = null): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, $model = null): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, $model = null): bool
    {
        return $user->isStaff();
    }
}
