<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->string('feeding_strategy')->default('biomass_percentage');
            $table->decimal('feeding_pct_small', 5, 2)->default(3.00);
            $table->decimal('feeding_pct_medium', 5, 2)->default(2.50);
            $table->decimal('feeding_pct_large', 5, 2)->default(2.00);
            $table->boolean('allow_post_close_adjustments')->default(false);
            $table->string('unit_system')->default('metric');
            $table->unsignedTinyInteger('decimals_precision')->default(2);
            $table->timestamps();
        });

        Schema::create('farm_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('farm_id')->unique()->constrained('farms')->cascadeOnDelete();
            $table->string('feeding_strategy')->nullable();
            $table->decimal('feeding_pct_small', 5, 2)->nullable();
            $table->decimal('feeding_pct_medium', 5, 2)->nullable();
            $table->decimal('feeding_pct_large', 5, 2)->nullable();
            $table->boolean('allow_post_close_adjustments')->nullable();
            $table->string('unit_system')->nullable();
            $table->unsignedTinyInteger('decimals_precision')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'farm_id']);
        });

        Schema::create('feeding_growth_tables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('farm_id')->nullable()->constrained('farms')->nullOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'farm_id', 'is_active']);
        });

        Schema::create('feeding_growth_table_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('table_id')->constrained('feeding_growth_tables')->cascadeOnDelete();
            $table->unsignedInteger('day_from');
            $table->unsignedInteger('day_to');
            $table->decimal('pp_from_grams', 10, 2)->nullable();
            $table->decimal('pp_to_grams', 10, 2)->nullable();
            $table->decimal('feed_pct', 5, 2);
            $table->timestamps();

            $table->index(['table_id', 'day_from', 'day_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeding_growth_table_rows');
        Schema::dropIfExists('feeding_growth_tables');
        Schema::dropIfExists('farm_settings');
        Schema::dropIfExists('tenant_settings');
    }
};
