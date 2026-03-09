<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_settings', function (Blueprint $table): void {
            $table->decimal('default_target_pp_grams', 10, 2)->nullable()->after('decimals_precision');
            $table->decimal('default_sale_price_per_lb', 10, 2)->nullable()->after('default_target_pp_grams');
            $table->decimal('default_feed_cost_factor_per_kg_gain', 10, 2)->nullable()->after('default_sale_price_per_lb');
        });

        Schema::table('farm_settings', function (Blueprint $table): void {
            $table->decimal('default_target_pp_grams', 10, 2)->nullable()->after('decimals_precision');
            $table->decimal('default_sale_price_per_lb', 10, 2)->nullable()->after('default_target_pp_grams');
            $table->decimal('default_feed_cost_factor_per_kg_gain', 10, 2)->nullable()->after('default_sale_price_per_lb');
        });

        Schema::create('survival_estimates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cycle_id')->constrained('cycles')->cascadeOnDelete();
            $table->date('estimated_at');
            $table->decimal('survival_pct', 5, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'cycle_id', 'estimated_at'], 'survival_estimates_tenant_cycle_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survival_estimates');

        Schema::table('farm_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'default_target_pp_grams',
                'default_sale_price_per_lb',
                'default_feed_cost_factor_per_kg_gain',
            ]);
        });

        Schema::table('tenant_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'default_target_pp_grams',
                'default_sale_price_per_lb',
                'default_feed_cost_factor_per_kg_gain',
            ]);
        });
    }
};
