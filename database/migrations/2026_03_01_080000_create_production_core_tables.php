<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('ponds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->string('code');
            $table->string('name')->nullable();
            $table->decimal('area_ha', 10, 2);
            $table->decimal('avg_depth_m', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code'], 'ponds_tenant_id_code_unique');
        });

        Schema::create('cycles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('pond_id')->constrained('ponds')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['pond_id', 'status'], 'cycles_pond_status_idx');
        });

        Schema::create('stockings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('cycle_id')->constrained('cycles')->cascadeOnDelete();
            $table->date('stocked_at');
            $table->unsignedInteger('pl_qty');
            $table->string('hatchery_code')->nullable();
            $table->string('batch_code')->nullable();
            $table->decimal('initial_pp_grams', 10, 2)->nullable();
            $table->decimal('density_pl_m2', 10, 4);
            $table->decimal('density_pl_ha', 10, 2);
            $table->timestamps();

            $table->unique('cycle_id', 'stockings_cycle_id_unique');
        });

        Schema::create('samplings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('cycle_id')->constrained('cycles')->cascadeOnDelete();
            $table->date('sampled_at');
            $table->decimal('pp_grams', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['cycle_id', 'sampled_at'], 'samplings_cycle_date_idx');
        });

        Schema::create('harvests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('cycle_id')->constrained('cycles')->cascadeOnDelete();
            $table->date('harvested_at');
            $table->string('type')->default('partial');
            $table->decimal('total_lbs', 12, 2);
            $table->decimal('avg_pp_grams', 10, 2)->nullable();
            $table->string('guide_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['cycle_id', 'type'], 'harvests_cycle_type_idx');
            $table->index(['cycle_id', 'harvested_at'], 'harvests_cycle_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvests');
        Schema::dropIfExists('samplings');
        Schema::dropIfExists('stockings');
        Schema::dropIfExists('cycles');
        Schema::dropIfExists('ponds');
        Schema::dropIfExists('farms');
    }
};
