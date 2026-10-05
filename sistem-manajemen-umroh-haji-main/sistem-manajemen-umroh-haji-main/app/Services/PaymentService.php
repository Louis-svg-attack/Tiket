<?php

namespace App\Services;

use App\Enums\PaymentType;
use App\Enums\RegistrationStatus;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Generate a bill schedule (optional DP + N monthly installments) for a
     * registration. Skips entirely when the registration already has bills.
     *
     * @return int number of bills created
     */
    public function generateForRegistration(Registration $registration, int $installments, ?float $dpAmount = null, ?Carbon $firstDue = null): int
    {
        if ($registration->payments()->exists()) {
            return 0;
        }

        $installments = max(1, $installments);
        $total = (float) $registration->total_price;
        $dp = $dpAmount !== null ? min(max(0, (float) $dpAmount), $total) : 0.0;
        $firstDue = $firstDue ?? Carbon::today()->addMonth();

        return DB::transaction(function () use ($registration, $installments, $dp, $total, $firstDue) {
            $created = 0;

            if ($dp > 0) {
                $this->makeBill($registration, PaymentType::DownPayment, $dp, Carbon::today());
                $created++;
            }

            $remaining = max(0, $total - $dp);
            $perInstallment = round($remaining / $installments, 2);

            for ($i = 0; $i < $installments; $i++) {
                // Last installment absorbs any rounding remainder.
                $amount = $i === $installments - 1
                    ? round($remaining - $perInstallment * ($installments - 1), 2)
                    : $perInstallment;

                if ($amount <= 0) {
                    continue;
                }

                $this->makeBill($registration, PaymentType::Installment, $amount, $firstDue->copy()->addMonths($i));
                $created++;
            }

            return $created;
        });
    }

    /**
     * Bulk-generate bills for every active registration of a package that has
     * no bills yet.
     *
     * @param  array{installments?: int, dp_amount?: float|null, first_due?: Carbon|null}  $opts
     * @return int number of registrations processed
     */
    public function generateMassal(Package $package, array $opts = []): int
    {
        $installments = (int) ($opts['installments'] ?? 1);
        $dpAmount = $opts['dp_amount'] ?? null;
        $firstDue = $opts['first_due'] ?? null;

        return DB::transaction(function () use ($package, $installments, $dpAmount, $firstDue) {
            $processed = 0;

            $registrations = $package->registrations()
                ->where('status', '!=', RegistrationStatus::Cancelled->value)
                ->whereDoesntHave('payments')
                ->get();

            foreach ($registrations as $registration) {
                $this->generateForRegistration($registration, $installments, $dpAmount, $firstDue);
                $processed++;
            }

            return $processed;
        });
    }

    /**
     * Record a payment against a bill under a row lock. The recorded amount is
     * capped at the outstanding billed amount and the bill status resynced.
     *
     * @param  array{method?: ?string, bank_account_id?: ?int, paid_at?: ?Carbon, note?: ?string}  $meta
     */
    public function record(Payment $payment, float $amount, array $meta = []): Payment
    {
        return DB::transaction(function () use ($payment, $amount, $meta) {
            $locked = Payment::whereKey($payment->getKey())->lockForUpdate()->first();

            $newPaid = (float) $locked->paid_amount + max(0, $amount);
            $locked->paid_amount = min($newPaid, (float) $locked->amount);
            $locked->paid_at = $meta['paid_at'] ?? Carbon::today();
            $locked->method = $meta['method'] ?? $locked->method;
            $locked->bank_account_id = $meta['bank_account_id'] ?? $locked->bank_account_id;
            $locked->recorded_by = Auth::id();

            if (! empty($meta['note'])) {
                $locked->note = $meta['note'];
            }

            $locked->syncStatus();
            $locked->save();

            return $locked;
        });
    }

    private function makeBill(Registration $registration, PaymentType $type, float $amount, Carbon $dueDate): Payment
    {
        return Payment::create([
            'branch_id' => $registration->branch_id,
            'registration_id' => $registration->id,
            'code' => 'INV-'.strtoupper(Str::random(6)),
            'type' => $type,
            'amount' => $amount,
            'paid_amount' => 0,
            'due_date' => $dueDate->toDateString(),
        ]);
    }
}
