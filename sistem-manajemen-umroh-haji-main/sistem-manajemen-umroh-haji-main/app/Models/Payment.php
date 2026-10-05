<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'registration_id', 'code', 'type', 'amount', 'paid_amount',
        'status', 'due_date', 'paid_at', 'method', 'bank_account_id',
        'recorded_by', 'proof_path', 'note',
    ];

    protected function casts(): array
    {
        return [
            'type' => PaymentType::class,
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'date',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /** Recompute status from amount/paid_amount. */
    public function syncStatus(): void
    {
        $paid = (float) $this->paid_amount;
        $amount = (float) $this->amount;

        $this->status = match (true) {
            $paid <= 0 => PaymentStatus::Unpaid,
            $paid >= $amount => PaymentStatus::Paid,
            default => PaymentStatus::Partial,
        };
    }

    public function outstanding(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }
}
