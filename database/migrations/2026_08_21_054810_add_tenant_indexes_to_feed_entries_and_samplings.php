<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('feed_entries', function (Blueprint $table) {
            $table->index(['tenant_id', 'fed_at'], 'feed_entries_tenant_fed_at_idx');
        });

        Schema::table('samplings', function (Blueprint $table) {
            $table->index(['tenant_id', 'sampled_at'], 'samplings_tenant_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed_entries', function (Blueprint $table) {
            $table->dropIndex('feed_entries_tenant_fed_at_idx');
        });

        Schema::table('samplings', function (Blueprint $table) {
            $table->dropIndex('samplings_tenant_date_idx');
        });
    }
};
