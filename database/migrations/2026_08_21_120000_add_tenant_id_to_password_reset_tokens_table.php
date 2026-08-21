<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('password_reset_tokens')->truncate();

        Schema::table('password_reset_tokens', function ($table): void {
            $table->dropPrimary('email');
        });

        DB::statement('ALTER TABLE password_reset_tokens ADD COLUMN tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE password_reset_tokens ADD PRIMARY KEY (tenant_id, email)');
    }

    public function down(): void
    {
        DB::table('password_reset_tokens')->truncate();

        Schema::table('password_reset_tokens', function ($table): void {
            $table->dropPrimary(['tenant_id', 'email']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('password_reset_tokens', function ($table): void {
            $table->primary('email');
        });
    }
};
