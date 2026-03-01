<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('water_quality_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('cycle_id')->constrained('cycles')->cascadeOnDelete();
            $table->foreignId('pond_id')->constrained('ponds')->cascadeOnDelete();
            $table->dateTime('measured_at');
            $table->decimal('dissolved_oxygen_mg_l', 6, 2)->nullable();
            $table->decimal('ph', 4, 2)->nullable();
            $table->decimal('temp_c', 5, 2)->nullable();
            $table->decimal('salinity_ppt', 6, 2)->nullable();
            $table->decimal('alkalinity_mg_l', 8, 2)->nullable();
            $table->decimal('ammonia_mg_l', 8, 3)->nullable();
            $table->decimal('nitrite_mg_l', 8, 3)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('measured_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'cycle_id', 'measured_at']);
            $table->index(['tenant_id', 'pond_id', 'measured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('water_quality_entries');
    }
};

