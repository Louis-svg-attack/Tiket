<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Central authority for multi-branch data isolation.
 *
 * - super_admin: may see every branch (and optionally focus on one via session)
 * - everyone else: locked to their own branch_id
 */
class BranchContext
{
    private const SESSION_KEY = 'active_branch_id';

    public function user(): ?User
    {
        return Auth::user();
    }

    public function isCrossBranch(): bool
    {
        return (bool) $this->user()?->isSuperAdmin();
    }

    /**
     * The branch ids the current user is allowed to read.
     * Returns null when the user may access ALL branches (super_admin, no focus).
     *
     * @return array<int, int>|null
     */
    public function allowedBranchIds(): ?array
    {
        $user = $this->user();
        if (! $user) {
            return [];
        }

        if ($user->isSuperAdmin()) {
            $focus = $this->focusedBranchId();

            return $focus ? [$focus] : null; // null = all branches
        }

        return $user->branch_id ? [$user->branch_id] : [-1]; // -1 = match nothing
    }

    /** super_admin can temporarily focus on one branch (stored in session). */
    public function focusedBranchId(): ?int
    {
        if (! $this->isCrossBranch()) {
            return $this->user()?->branch_id;
        }

        $id = Session::get(self::SESSION_KEY);

        return $id ? (int) $id : null;
    }

    public function setFocus(?int $branchId): void
    {
        if ($branchId) {
            Session::put(self::SESSION_KEY, $branchId);
        } else {
            Session::forget(self::SESSION_KEY);
        }
    }

    /** The branch id new records created by this user should belong to. */
    public function currentBranchId(): ?int
    {
        return $this->focusedBranchId() ?? $this->user()?->branch_id;
    }

    public function currentBranch(): ?Branch
    {
        $id = $this->currentBranchId();

        return $id ? Branch::find($id) : null;
    }

    /**
     * Apply branch isolation to any query that owns a branch_id column.
     */
    public function scope(Builder $query, string $column = 'branch_id'): Builder
    {
        $ids = $this->allowedBranchIds();

        if ($ids === null) {
            return $query; // all branches
        }

        return $query->whereIn($column, $ids);
    }

    /** Branches the user may pick from in dropdowns. */
    public function selectableBranches()
    {
        $query = Branch::query()->where('is_active', true)->orderBy('name');

        if (! $this->isCrossBranch()) {
            $id = $this->user()?->branch_id;
            $query->where('id', $id ?? -1);
        }

        return $query->get();
    }

    public function canAccessBranch(?int $branchId): bool
    {
        if ($branchId === null) {
            return true;
        }

        $ids = $this->allowedBranchIds();

        return $ids === null || in_array($branchId, $ids, true);
    }
}
