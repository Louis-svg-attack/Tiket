<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Pilgrim;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PilgrimService
{
    public function __construct(
        private readonly BranchContext $branchContext,
        private readonly AccessContext $access,
        private readonly AccountProvisioner $provisioner,
    ) {}

    /**
     * @return array{pilgrim: Pilgrim, password: ?string}
     */
    public function createPilgrim(array $data, bool $createAccount): array
    {
        return DB::transaction(function () use ($data, $createAccount) {
            $accountEmail = $data['account_email'] ?? null;
            unset($data['account_email']);

            $data['branch_id'] = $data['branch_id'] ?? $this->branchContext->currentBranchId();

            if (empty($data['code'])) {
                $data['code'] = 'JM-'.strtoupper(Str::random(5));
            }

            // An agent can only register pilgrims under their own referral.
            if (Auth::user()?->isAgent()) {
                $data['agent_id'] = $this->access->agentId();
            }

            $pilgrim = Pilgrim::create($data);
            $password = null;

            if ($createAccount) {
                $result = $this->provisioner->provision(
                    Role::Pilgrim,
                    $pilgrim->name,
                    $accountEmail ?: $pilgrim->email,
                    $pilgrim->branch_id,
                    $pilgrim->phone,
                );

                $pilgrim->update(['user_id' => $result['user']->id]);
                $password = $result['password'];
            }

            return ['pilgrim' => $pilgrim, 'password' => $password];
        });
    }

    /**
     * @return array{pilgrim: Pilgrim, password: ?string}
     */
    public function updatePilgrim(Pilgrim $pilgrim, array $data, bool $createAccount): array
    {
        return DB::transaction(function () use ($pilgrim, $data, $createAccount) {
            $accountEmail = $data['account_email'] ?? null;
            unset($data['account_email']);

            // An agent can only keep pilgrims under their own referral.
            if (Auth::user()?->isAgent()) {
                $data['agent_id'] = $this->access->agentId();
            }

            $pilgrim->update($data);
            $password = null;

            // Only provision a login account if requested and one doesn't exist yet.
            if ($createAccount && ! $pilgrim->user_id) {
                $result = $this->provisioner->provision(
                    Role::Pilgrim,
                    $pilgrim->name,
                    $accountEmail ?: $pilgrim->email,
                    $pilgrim->branch_id,
                    $pilgrim->phone,
                );

                $pilgrim->update(['user_id' => $result['user']->id]);
                $password = $result['password'];
            }

            return ['pilgrim' => $pilgrim, 'password' => $password];
        });
    }
}
