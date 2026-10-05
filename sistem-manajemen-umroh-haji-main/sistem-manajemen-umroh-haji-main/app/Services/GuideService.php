<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Guide;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GuideService
{
    public function __construct(
        private readonly BranchContext $branchContext,
        private readonly AccountProvisioner $provisioner,
    ) {}

    /**
     * @return array{guide: Guide, password: ?string}
     */
    public function createGuide(array $data, bool $createAccount): array
    {
        return DB::transaction(function () use ($data, $createAccount) {
            $accountEmail = $data['account_email'] ?? null;
            unset($data['account_email']);

            $data['branch_id'] = $data['branch_id'] ?? $this->branchContext->currentBranchId();

            if (empty($data['code'])) {
                $data['code'] = 'GD-'.strtoupper(Str::random(5));
            }

            $guide = Guide::create($data);
            $password = null;

            if ($createAccount) {
                $result = $this->provisioner->provision(
                    Role::Guide,
                    $guide->name,
                    $accountEmail ?: $guide->email,
                    $guide->branch_id,
                    $guide->phone,
                );

                $guide->update(['user_id' => $result['user']->id]);
                $password = $result['password'];
            }

            return ['guide' => $guide, 'password' => $password];
        });
    }

    /**
     * @return array{guide: Guide, password: ?string}
     */
    public function updateGuide(Guide $guide, array $data, bool $createAccount): array
    {
        return DB::transaction(function () use ($guide, $data, $createAccount) {
            $accountEmail = $data['account_email'] ?? null;
            unset($data['account_email']);

            $guide->update($data);
            $password = null;

            // Only provision a login account if requested and one doesn't exist yet.
            if ($createAccount && ! $guide->user_id) {
                $result = $this->provisioner->provision(
                    Role::Guide,
                    $guide->name,
                    $accountEmail ?: $guide->email,
                    $guide->branch_id,
                    $guide->phone,
                );

                $guide->update(['user_id' => $result['user']->id]);
                $password = $result['password'];
            }

            return ['guide' => $guide, 'password' => $password];
        });
    }
}
