<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_subscriptions', function (Blueprint $table): void {
            if (! Schema::hasColumn('tenant_subscriptions', 'last_verified_at')) {
                $table->dateTime('last_verified_at')->nullable()->after('license_key');
            }
            if (! Schema::hasColumn('tenant_subscriptions', 'offline_grace_days')) {
                $table->integer('offline_grace_days')->default(7)->after('last_verified_at');
            }
            if (! Schema::hasColumn('tenant_subscriptions', 'offline_mode_enabled')) {
                $table->boolean('offline_mode_enabled')->default(false)->after('offline_grace_days');
            }
            if (! Schema::hasColumn('tenant_subscriptions', 'verification_source')) {
                $table->string('verification_source')->default('cloud')->after('offline_mode_enabled');
            }
        });

        Schema::create('license_activations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('tenant_subscriptions')->cascadeOnDelete();
            $table->dateTime('activated_at');
            $table->string('machine_fingerprint')->nullable();
            $table->foreignId('activated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'subscription_id', 'activated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_activations');

        Schema::table('tenant_subscriptions', function (Blueprint $table): void {
            if (Schema::hasColumn('tenant_subscriptions', 'verification_source')) {
                $table->dropColumn('verification_source');
            }
            if (Schema::hasColumn('tenant_subscriptions', 'offline_mode_enabled')) {
                $table->dropColumn('offline_mode_enabled');
            }
            if (Schema::hasColumn('tenant_subscriptions', 'offline_grace_days')) {
                $table->dropColumn('offline_grace_days');
            }
            if (Schema::hasColumn('tenant_subscriptions', 'last_verified_at')) {
                $table->dropColumn('last_verified_at');
            }
        });
    }
};

