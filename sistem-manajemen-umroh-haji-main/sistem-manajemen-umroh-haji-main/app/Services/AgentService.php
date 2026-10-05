<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Agent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AgentService
{
    public function __construct(
        private readonly BranchContext $branchContext,
        private readonly AccountProvisioner $provisioner,
    ) {}

    /**
     * @return array{agent: Agent, password: ?string}
     */
    public function createAgent(array $data, bool $createAccount): array
    {
        return DB::transaction(function () use ($data, $createAccount) {
            $accountEmail = $data['account_email'] ?? null;
            unset($data['account_email']);

            $data['branch_id'] = $data['branch_id'] ?? $this->branchContext->currentBranchId();

            if (empty($data['code'])) {
                $data['code'] = 'AG-'.strtoupper(Str::random(5));
            }

            $agent = Agent::create($data);
            $password = null;

            if ($createAccount) {
                $result = $this->provisioner->provision(
                    Role::Agent,
                    $agent->name,
                    $accountEmail ?: $agent->email,
                    $agent->branch_id,
                    $agent->phone,
                );

                $agent->update(['user_id' => $result['user']->id]);
                $password = $result['password'];
            }

            return ['agent' => $agent, 'password' => $password];
        });
    }

    /**
     * @return array{agent: Agent, password: ?string}
     */
    public function updateAgent(Agent $agent, array $data, bool $createAccount): array
    {
        return DB::transaction(function () use ($agent, $data, $createAccount) {
            $accountEmail = $data['account_email'] ?? null;
            unset($data['account_email']);

            $agent->update($data);
            $password = null;

            // Only provision a login account if requested and one doesn't exist yet.
            if ($createAccount && ! $agent->user_id) {
                $result = $this->provisioner->provision(
                    Role::Agent,
                    $agent->name,
                    $accountEmail ?: $agent->email,
                    $agent->branch_id,
                    $agent->phone,
                );

                $agent->update(['user_id' => $result['user']->id]);
                $password = $result['password'];
            }

            return ['agent' => $agent, 'password' => $password];
        });
    }
}
