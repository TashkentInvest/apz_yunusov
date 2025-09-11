<?php

// Migration for base_calculation_amounts table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('base_calculation_amounts', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 15, 2);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_current')->default(true);
            $table->timestamps();
        });

        // Insert real base amounts
        DB::table('base_calculation_amounts')->insert([
            [
                'id' => 1,
                'amount' => 340000,
                'effective_from' => '2023-12-01',
                'effective_to' => '2024-10-01',
                'is_active' => true,
                'is_current' => false,

                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'amount' => 375000,
                'effective_from' => '2024-10-01',
                'effective_to' => '2025-08-01',
                'is_active' => true,
                'is_current' => false,

                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'amount' => 412000,
                'effective_from' => '2025-08-01',
                'effective_to' => '2026-12-31',
                'is_active' => true,
                'is_current' => true,

                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('base_calculation_amounts');
    }
};
