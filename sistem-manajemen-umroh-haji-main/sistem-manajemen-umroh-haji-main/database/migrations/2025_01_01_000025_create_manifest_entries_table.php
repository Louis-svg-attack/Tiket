<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manifest_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->string('room_number')->nullable();
            $table->string('room_type')->nullable(); // quad|triple|double
            $table->string('bus_number')->nullable();
            $table->string('seat_number')->nullable();
            $table->string('mahram_group')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['package_id', 'registration_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manifest_entries');
    }
};
