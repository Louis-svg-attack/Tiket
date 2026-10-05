<?php

namespace App\Policies;

use App\Models\Pilgrim;
use App\Models\User;
use App\Policies\Concerns\ChecksBranchAccess;

class PilgrimPolicy
{
    use ChecksBranchAccess;

    public function viewAny(User $user): bool
    {
        // Pilgrims have their own dashboard, not the list.
        return $user->isStaff() || $user->isAgent() || $user->isGuide();
    }

    public function view(User $user, Pilgrim $pilgrim): bool
    {
        return match (true) {
            $user->isStaff() => $this->sameBranch($user, $pilgrim->branch_id),
            $user->isAgent() => $pilgrim->agent?->user_id === $user->id,
            $user->isPilgrim() => $pilgrim->user_id === $user->id,
            $user->isGuide() => $pilgrim->registrations()
                ->where('guide_id', $user->guide?->id ?? -1)->exists(),
            default => false,
        };
    }

    public function create(User $user): bool
    {
        return $user->isStaff() || $user->isAgent();
    }

    public function update(User $user, Pilgrim $pilgrim): bool
    {
        return match (true) {
            $user->isStaff() => $this->sameBranch($user, $pilgrim->branch_id),
            $user->isAgent() => $pilgrim->agent?->user_id === $user->id,
            $user->isPilgrim() => $pilgrim->user_id === $user->id,
            default => false,
        };
    }

    public function delete(User $user, Pilgrim $pilgrim): bool
    {
        return $user->isStaff() && $this->sameBranch($user, $pilgrim->branch_id);
    }
}
