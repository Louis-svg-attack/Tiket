<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;
use App\Policies\Concerns\ChecksBranchAccess;

class RegistrationPolicy
{
    use ChecksBranchAccess;

    public function viewAny(User $user): bool
    {
        return true; // every role sees a scoped list
    }

    public function view(User $user, Registration $registration): bool
    {
        return match (true) {
            $user->isStaff() => $this->sameBranch($user, $registration->branch_id),
            $user->isAgent() => $registration->agent?->user_id === $user->id,
            $user->isGuide() => $registration->guide?->user_id === $user->id,
            $user->isPilgrim() => $registration->pilgrim?->user_id === $user->id,
            default => false,
        };
    }

    public function create(User $user): bool
    {
        return $user->isStaff() || $user->isAgent();
    }

    public function update(User $user, Registration $registration): bool
    {
        return match (true) {
            $user->isStaff() => $this->sameBranch($user, $registration->branch_id),
            $user->isAgent() => $registration->agent?->user_id === $user->id,
            default => false,
        };
    }

    public function delete(User $user, Registration $registration): bool
    {
        return $user->isStaff() && $this->sameBranch($user, $registration->branch_id);
    }
}
