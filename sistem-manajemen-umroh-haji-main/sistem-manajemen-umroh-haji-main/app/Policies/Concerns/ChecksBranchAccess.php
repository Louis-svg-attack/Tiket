<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksBranchAccess
{
    /**
     * True when the user may operate on a record belonging to $branchId.
     * super_admin = any branch; everyone else = own branch only.
     */
    protected function sameBranch(User $user, ?int $branchId): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($branchId === null) {
            return true;
        }

        return $user->branch_id === $branchId;
    }
}
