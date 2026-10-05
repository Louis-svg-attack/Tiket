<?php

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('type')->default(PaymentType::Installment->value)->index();
            $table->decimal('amount', 15, 2)->default(0);       // billed amount
            $table->decimal('paid_amount', 15, 2)->default(0);  // received so far
            $table->string('status')->default(PaymentStatus::Unpaid->value)->index();
            $table->date('due_date')->nullable()->index();
            $table->date('paid_at')->nullable();
            $table->string('method')->nullable();               // transfer|cash|edc
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('proof_path')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
