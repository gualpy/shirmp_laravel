<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('farm_id')->nullable()->constrained('farms')->nullOnDelete();
            $table->string('code');
            $table->boolean('is_active')->default(true);
            $table->json('params_json')->nullable();
            $table->string('severity')->default('warning');
            $table->timestamps();

            $table->unique(['tenant_id', 'code'], 'alert_rules_tenant_code_unique');
            $table->index(['tenant_id', 'farm_id', 'is_active']);
        });

        Schema::create('alert_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->foreignId('cycle_id')->constrained('cycles')->cascadeOnDelete();
            $table->string('rule_code');
            $table->string('severity');
            $table->string('title');
            $table->text('message');
            $table->dateTime('detected_at');
            $table->json('context_json')->nullable();
            $table->boolean('is_acknowledged')->default(false);
            $table->foreignId('acknowledged_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'farm_id', 'cycle_id']);
            $table->index(['tenant_id', 'severity', 'is_acknowledged']);
            $table->index(['cycle_id', 'rule_code', 'detected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_events');
        Schema::dropIfExists('alert_rules');
    }
};
