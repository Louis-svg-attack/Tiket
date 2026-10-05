<?php

namespace App\Policies;

use App\Models\EquipmentDistribution;
use App\Models\User;
use App\Policies\Concerns\ChecksBranchAccess;

class EquipmentPolicy
{
    use ChecksBranchAccess;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, EquipmentDistribution $equipment): bool
    {
        $registration = $equipment->registration;

        return match (true) {
            $user->isStaff() => $this->sameBranch($user, $registration?->branch_id),
            $user->isPilgrim() => $registration?->pilgrim?->user_id === $user->id,
            default => false,
        };
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, EquipmentDistribution $equipment): bool
    {
        return $user->isStaff() && $this->sameBranch($user, $equipment->registration?->branch_id);
    }

    public function delete(User $user, EquipmentDistribution $equipment): bool
    {
        return $this->update($user, $equipment);
    }
}
