<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the payment_schedules table with all columns
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->foreignId('amendment_id')->nullable()->constrained('contract_amendments')->onDelete('cascade');
            $table->integer('year');
            $table->integer('quarter'); // 0 for initial payment, 1-4 for quarters
            $table->decimal('quarter_amount', 20, 2);
            $table->boolean('is_initial_payment')->default(false);
            $table->decimal('custom_percent', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['contract_id', 'year', 'quarter']);
            $table->index(['contract_id', 'is_active']);
            $table->index(['contract_id', 'amendment_id', 'is_active'], 'ps_contract_amendment_active_index');
            $table->index(['contract_id', 'is_initial_payment'], 'ps_contract_initial_payment_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the entire payment_schedules table
        Schema::dropIfExists('payment_schedules');
    }
};