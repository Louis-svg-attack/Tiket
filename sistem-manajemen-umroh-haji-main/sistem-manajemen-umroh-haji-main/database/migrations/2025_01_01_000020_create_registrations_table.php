<?php

use App\Enums\RegistrationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('pilgrim_id')->constrained('pilgrims')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('guide_id')->nullable()->constrained('guides')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('status')->default(RegistrationStatus::Pending->value)->index();
            $table->decimal('total_price', 15, 2)->default(0);
            $table->date('registered_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['pilgrim_id', 'package_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
