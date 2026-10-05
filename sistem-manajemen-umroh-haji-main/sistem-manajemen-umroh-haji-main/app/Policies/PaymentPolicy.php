<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Policies\Concerns\ChecksBranchAccess;

class PaymentPolicy
{
    use ChecksBranchAccess;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Payment $payment): bool
    {
        $registration = $payment->registration;

        return match (true) {
            $user->isStaff() => $this->sameBranch($user, $payment->branch_id),
            $user->isAgent() => $registration?->agent?->user_id === $user->id,
            $user->isPilgrim() => $registration?->pilgrim?->user_id === $user->id,
            default => false,
        };
    }

    /** Recording money is staff-only. */
    public function record(User $user, Payment $payment): bool
    {
        return $user->isStaff() && $this->sameBranch($user, $payment->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Payment $payment): bool
    {
        return $this->record($user, $payment);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $this->record($user, $payment);
    }
}
