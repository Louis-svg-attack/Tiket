<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, User $target): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdmin() && $user->branch_id === $target->branch_id;
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, User $target): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admins manage non-superadmin users in their own branch.
        return $user->isAdmin()
            && ! $target->isSuperAdmin()
            && $user->branch_id === $target->branch_id;
    }

    public function delete(User $user, User $target): bool
    {
        return $user->id !== $target->id && $this->update($user, $target) && ! $target->isSuperAdmin();
    }
}
