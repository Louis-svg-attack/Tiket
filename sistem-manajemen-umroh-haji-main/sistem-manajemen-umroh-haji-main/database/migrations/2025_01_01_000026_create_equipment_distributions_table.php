<?php

use App\Enums\EquipmentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->string('item');        // Koper | Seragam | Ihram | Tas Paspor
            $table->string('size')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('status')->default(EquipmentStatus::Pending->value)->index();
            $table->date('handed_at')->nullable();
            $table->foreignId('handed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_distributions');
    }
};
