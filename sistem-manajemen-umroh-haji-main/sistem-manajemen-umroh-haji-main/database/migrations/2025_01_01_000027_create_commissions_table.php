<?php

use App\Enums\CommissionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->decimal('base_amount', 15, 2)->default(0);
            $table->decimal('rate', 5, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status')->default(CommissionStatus::Pending->value)->index();
            $table->date('paid_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique('registration_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
