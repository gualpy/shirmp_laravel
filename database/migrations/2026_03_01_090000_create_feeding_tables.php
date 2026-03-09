<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->decimal('protein_pct', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
            $table->unique(['tenant_id', 'name'], 'feed_types_tenant_name_unique');
        });

        Schema::create('feed_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('cycle_id')->constrained('cycles')->cascadeOnDelete();
            $table->foreignId('feed_type_id')->constrained('feed_types')->cascadeOnDelete();
            $table->date('fed_at');
            $table->decimal('amount_kg', 12, 3);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['cycle_id', 'fed_at'], 'feed_entries_cycle_fed_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_entries');
        Schema::dropIfExists('feed_types');
    }
};
