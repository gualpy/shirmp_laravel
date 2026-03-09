<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feed_types', function (Blueprint $table): void {
            if (! Schema::hasColumn('feed_types', 'cost_per_kg')) {
                $table->decimal('cost_per_kg', 12, 4)->nullable()->after('protein_pct');
            }
        });

        Schema::create('operational_cost_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('cycle_id')->constrained('cycles')->cascadeOnDelete();
            $table->string('cost_type');
            $table->decimal('amount', 12, 2);
            $table->date('occurred_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['cycle_id', 'occurred_at']);
            $table->index(['tenant_id', 'cost_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_cost_entries');

        Schema::table('feed_types', function (Blueprint $table): void {
            if (Schema::hasColumn('feed_types', 'cost_per_kg')) {
                $table->dropColumn('cost_per_kg');
            }
        });
    }
};
