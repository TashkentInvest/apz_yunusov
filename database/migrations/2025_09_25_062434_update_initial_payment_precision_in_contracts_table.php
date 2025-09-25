<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Temporarily disable strict mode
        DB::statement("SET sql_mode = ''");

        // Fix invalid datetime values
        DB::statement("UPDATE contracts SET created_at = NOW() WHERE created_at = '0000-00-00 00:00:00' OR created_at IS NULL");
        DB::statement("UPDATE contracts SET updated_at = NOW() WHERE updated_at = '0000-00-00 00:00:00' OR updated_at IS NULL");

        // Re-enable strict mode
        DB::statement("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

        // Now modify the columns
        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('initial_payment_percent', 12, 8)->nullable()->change();
            $table->decimal('initial_payment_amount', 20, 8)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('initial_payment_percent', 10, 2)->nullable()->change();
            $table->decimal('initial_payment_amount', 20, 2)->nullable()->change();
        });
    }
};
