<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE feed_entries ALTER COLUMN fed_at TYPE timestamp USING fed_at::timestamp');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE feed_entries ALTER COLUMN fed_at TYPE date USING fed_at::date');
    }
};
