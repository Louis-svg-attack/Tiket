<?php

use App\Enums\AttendanceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manasik_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manasik_schedule_id')->constrained('manasik_schedules')->cascadeOnDelete();
            $table->foreignId('pilgrim_id')->constrained('pilgrims')->cascadeOnDelete();
            $table->string('status')->default(AttendanceStatus::Absent->value)->index();
            $table->text('note')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['manasik_schedule_id', 'pilgrim_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manasik_attendances');
    }
};
