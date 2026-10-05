<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Policies\Concerns\ChecksBranchAccess;

class DocumentPolicy
{
    use ChecksBranchAccess;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Document $document): bool
    {
        $pilgrim = $document->pilgrim;
        if (! $pilgrim) {
            return $user->isStaff();
        }

        return match (true) {
            $user->isStaff() => $this->sameBranch($user, $pilgrim->branch_id),
            $user->isAgent() => $pilgrim->agent?->user_id === $user->id,
            $user->isPilgrim() => $pilgrim->user_id === $user->id,
            default => false,
        };
    }

    public function download(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }

    public function upload(User $user, Document $document): bool
    {
        $pilgrim = $document->pilgrim;
        if (! $pilgrim) {
            return $user->isStaff();
        }

        return match (true) {
            $user->isStaff() => $this->sameBranch($user, $pilgrim->branch_id),
            $user->isPilgrim() => $pilgrim->user_id === $user->id,
            $user->isAgent() => $pilgrim->agent?->user_id === $user->id,
            default => false,
        };
    }

    /** Only staff verify/reject documents. */
    public function verify(User $user, Document $document): bool
    {
        $pilgrim = $document->pilgrim;

        return $user->isStaff() && $this->sameBranch($user, $pilgrim?->branch_id);
    }

    public function update(User $user, Document $document): bool
    {
        return $this->upload($user, $document);
    }

    public function delete(User $user, Document $document): bool
    {
        return $this->verify($user, $document);
    }
}
