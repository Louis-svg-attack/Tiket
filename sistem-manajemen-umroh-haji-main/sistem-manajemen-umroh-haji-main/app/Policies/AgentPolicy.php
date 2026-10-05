<?php

namespace App\Policies;

use App\Models\Agent;
use App\Models\User;
use App\Policies\Concerns\ChecksBranchAccess;

class AgentPolicy
{
    use ChecksBranchAccess;

    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Agent $agent): bool
    {
        if ($user->isStaff()) {
            return $this->sameBranch($user, $agent->branch_id);
        }

        return $user->isAgent() && $agent->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Agent $agent): bool
    {
        return $user->isStaff() && $this->sameBranch($user, $agent->branch_id);
    }

    public function delete(User $user, Agent $agent): bool
    {
        return $user->isStaff() && $this->sameBranch($user, $agent->branch_id);
    }
}
