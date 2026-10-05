<?php

namespace App\Services;

use App\Enums\CommissionStatus;
use App\Enums\RegistrationStatus;
use App\Models\Commission;
use App\Models\Package;
use App\Models\Pilgrim;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegistrationService
{
    public function __construct(private readonly AccessContext $access) {}

    /**
     * Book a pilgrim onto a package. Quota is re-checked under a row lock to
     * prevent over-booking under concurrent requests. When the pilgrim has an
     * agent, a pending commission is created from the package price.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \RuntimeException when the package is full.
     */
    public function book(array $data): Registration
    {
        return DB::transaction(function () use ($data) {
            $package = Package::whereKey($data['package_id'])->lockForUpdate()->first();

            if (! $package) {
                throw new \RuntimeException('Paket tidak ditemukan.');
            }

            if ($package->remainingQuota() <= 0) {
                throw new \RuntimeException('Kuota paket penuh.');
            }

            $pilgrim = Pilgrim::find($data['pilgrim_id']);
            $agentId = $pilgrim?->agent_id ?? $this->access->agentId();

            $registration = Registration::create([
                'branch_id' => $package->branch_id,
                'pilgrim_id' => $data['pilgrim_id'],
                'package_id' => $package->id,
                'agent_id' => $agentId,
                'guide_id' => $data['guide_id'] ?? null,
                'code' => 'REG-'.strtoupper(Str::random(6)),
                'status' => RegistrationStatus::Pending,
                'total_price' => $package->price,
                'registered_at' => $data['registered_at'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
            ]);

            if ($agentId && $registration->agent) {
                $rate = (float) ($registration->agent->commission_rate ?? 0);
                $base = (float) $registration->total_price;

                Commission::create([
                    'branch_id' => $registration->branch_id,
                    'agent_id' => $agentId,
                    'registration_id' => $registration->id,
                    'base_amount' => $base,
                    'rate' => $rate,
                    'amount' => round($base * $rate / 100, 2),
                    'status' => CommissionStatus::Pending,
                ]);
            }

            return $registration;
        });
    }

    public function updateStatus(Registration $registration, RegistrationStatus $status): Registration
    {
        $registration->update(['status' => $status]);

        return $registration;
    }
}
