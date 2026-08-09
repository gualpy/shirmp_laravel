<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_payments', function (Blueprint $table): void {
            $table->string('provider_session_id')->nullable()->unique()->after('provider_reference');
        });
    }

    public function down(): void
    {
        Schema::table('billing_payments', function (Blueprint $table): void {
            $table->dropColumn('provider_session_id');
        });
    }
};
