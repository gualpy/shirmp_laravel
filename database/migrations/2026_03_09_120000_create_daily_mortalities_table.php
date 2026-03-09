<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_mortalities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('cycle_id')->constrained('cycles')->cascadeOnDelete();
            $table->foreignId('pond_id')->constrained('ponds')->cascadeOnDelete();
            $table->date('recorded_at');
            $table->unsignedInteger('mortality_count');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('cycle_id');
            $table->index('pond_id');
            $table->index('recorded_at');
            $table->index(['tenant_id', 'cycle_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_mortalities');
    }
};
