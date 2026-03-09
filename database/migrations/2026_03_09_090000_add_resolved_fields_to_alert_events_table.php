<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alert_events', function (Blueprint $table): void {
            $table->foreignId('resolved_by_user_id')->nullable()->after('acknowledged_at')->constrained('users')->nullOnDelete();
            $table->dateTime('resolved_at')->nullable()->after('resolved_by_user_id');
            $table->index(['tenant_id', 'resolved_at']);
        });
    }

    public function down(): void
    {
        Schema::table('alert_events', function (Blueprint $table): void {
            $table->dropIndex(['tenant_id', 'resolved_at']);
            $table->dropConstrainedForeignId('resolved_by_user_id');
            $table->dropColumn('resolved_at');
        });
    }
};
