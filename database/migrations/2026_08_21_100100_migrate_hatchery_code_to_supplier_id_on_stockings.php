<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stockings', function (Blueprint $table): void {
            $table->foreignId('supplier_id')->nullable()->after('cycle_id')->constrained('suppliers')->nullOnDelete();
        });

        $rows = DB::table('stockings')
            ->whereNotNull('hatchery_code')
            ->where('hatchery_code', '!=', '')
            ->select('id', 'tenant_id', 'hatchery_code')
            ->get();

        $supplierIds = [];

        foreach ($rows as $row) {
            $cacheKey = $row->tenant_id.'|'.$row->hatchery_code;

            if (! isset($supplierIds[$cacheKey])) {
                $existing = DB::table('suppliers')
                    ->where('tenant_id', $row->tenant_id)
                    ->where('name', $row->hatchery_code)
                    ->first();

                $supplierIds[$cacheKey] = $existing !== null
                    ? $existing->id
                    : DB::table('suppliers')->insertGetId([
                        'tenant_id' => $row->tenant_id,
                        'name' => $row->hatchery_code,
                        'type' => 'hatchery',
                        'code' => $row->hatchery_code,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            DB::table('stockings')->where('id', $row->id)->update(['supplier_id' => $supplierIds[$cacheKey]]);
        }

        Schema::table('stockings', function (Blueprint $table): void {
            $table->dropColumn('hatchery_code');
        });
    }

    public function down(): void
    {
        Schema::table('stockings', function (Blueprint $table): void {
            $table->string('hatchery_code')->nullable()->after('cycle_id');
        });

        DB::table('stockings')
            ->join('suppliers', 'suppliers.id', '=', 'stockings.supplier_id')
            ->update(['stockings.hatchery_code' => DB::raw('suppliers.name')]);

        Schema::table('stockings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('supplier_id');
        });
    }
};
