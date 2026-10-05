<?php

namespace App\Services;

use App\Models\Pilgrim;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Decides which pilgrims / registrations / packages each role may touch.
 *
 * Built on top of BranchContext (branch isolation) and then narrowed further
 * by role: agents see only their referrals, pilgrims see only themselves,
 * guides see only the jamaah they mentor.
 *
 * Every method is null-safe and returns an empty result for users with no
 * linked profile (e.g. an agent account whose Agent row was deleted).
 */
class AccessContext
{
    public function __construct(private readonly BranchContext $branches) {}

    public function user(): ?User
    {
        return Auth::user();
    }

    public function agentId(): ?int
    {
        return $this->user()?->agent?->id;
    }

    public function guideId(): ?int
    {
        return $this->user()?->guide?->id;
    }

    public function pilgrimId(): ?int
    {
        return $this->user()?->pilgrim?->id;
    }

    /**
     * Registration query scoped to what the current user may see.
     */
    public function registrations(): Builder
    {
        $query = Registration::query();
        $user = $this->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        $this->branches->scope($query);

        return match (true) {
            $user->isStaff() => $query,
            $user->isAgent() => $query->where('agent_id', $this->agentId() ?? -1),
            $user->isGuide() => $query->where('guide_id', $this->guideId() ?? -1),
            $user->isPilgrim() => $query->where('pilgrim_id', $this->pilgrimId() ?? -1),
            default => $query->whereRaw('1 = 0'),
        };
    }

    /**
     * Pilgrim query scoped to what the current user may see.
     */
    public function pilgrims(): Builder
    {
        $query = Pilgrim::query();
        $user = $this->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        $this->branches->scope($query);

        return match (true) {
            $user->isStaff() => $query,
            $user->isAgent() => $query->where('agent_id', $this->agentId() ?? -1),
            $user->isPilgrim() => $query->where('id', $this->pilgrimId() ?? -1),
            $user->isGuide() => $query->whereIn(
                'id',
                Registration::query()
                    ->where('guide_id', $this->guideId() ?? -1)
                    ->select('pilgrim_id')
            ),
            default => $query->whereRaw('1 = 0'),
        };
    }

    /** Can the current user read this specific registration? */
    public function canViewRegistration(Registration $registration): bool
    {
        return $this->registrations()->whereKey($registration->getKey())->exists();
    }

    /** Can the current user read this specific pilgrim? */
    public function canViewPilgrim(Pilgrim $pilgrim): bool
    {
        return $this->pilgrims()->whereKey($pilgrim->getKey())->exists();
    }
}
