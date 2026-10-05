<?php

use App\Enums\PackageStatus;
use App\Enums\PackageType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type')->default(PackageType::Umroh->value)->index();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('down_payment', 15, 2)->default(0);
            $table->unsignedInteger('quota')->default(0);
            $table->unsignedSmallInteger('duration_days')->default(9);
            $table->date('departure_date')->nullable()->index();
            $table->string('departure_city')->nullable();
            $table->foreignId('hotel_makkah_id')->nullable()->constrained('hotels')->nullOnDelete();
            $table->foreignId('hotel_madinah_id')->nullable()->constrained('hotels')->nullOnDelete();
            $table->foreignId('airline_id')->nullable()->constrained('airlines')->nullOnDelete();
            $table->json('facilities_included')->nullable();
            $table->json('facilities_excluded')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default(PackageStatus::Draft->value)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
