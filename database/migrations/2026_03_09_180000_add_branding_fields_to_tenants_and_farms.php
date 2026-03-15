<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->string('company_display_name')->nullable()->after('name');
            $table->string('company_legal_name')->nullable()->after('company_display_name');
            $table->string('logo_path')->nullable()->after('company_legal_name');
            $table->string('company_address')->nullable()->after('logo_path');
            $table->string('company_phone')->nullable()->after('company_address');
            $table->string('company_email')->nullable()->after('company_phone');
            $table->text('report_footer_text')->nullable()->after('company_email');
        });

        Schema::table('farms', function (Blueprint $table): void {
            $table->string('company_display_name')->nullable()->after('name');
            $table->string('logo_path')->nullable()->after('company_display_name');
        });
    }

    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table): void {
            $table->dropColumn([
                'company_display_name',
                'logo_path',
            ]);
        });

        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropColumn([
                'company_display_name',
                'company_legal_name',
                'logo_path',
                'company_address',
                'company_phone',
                'company_email',
                'report_footer_text',
            ]);
        });
    }
};
