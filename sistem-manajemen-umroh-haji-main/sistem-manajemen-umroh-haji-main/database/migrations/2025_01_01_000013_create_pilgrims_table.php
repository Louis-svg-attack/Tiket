<?php

use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pilgrims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('gender')->default(Gender::Male->value)->index();
            $table->string('nik', 32)->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            // Passport
            $table->string('passport_number')->nullable();
            $table->date('passport_issued_at')->nullable();
            $table->date('passport_expired_at')->nullable();
            $table->string('passport_issued_place')->nullable();
            // Mahram & emergency
            $table->string('mahram_name')->nullable();
            $table->string('mahram_relation')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            // Health
            $table->text('health_notes')->nullable();
            $table->string('blood_type', 5)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pilgrims');
    }
};
