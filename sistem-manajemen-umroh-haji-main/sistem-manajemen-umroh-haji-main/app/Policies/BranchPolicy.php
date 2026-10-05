<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Branch $branch): bool
    {
        return $user->isSuperAdmin() || $user->branch_id === $branch->id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Branch $branch): bool
    {
        return $user->isSuperAdmin() && ! $branch->is_head_office;
    }
}
