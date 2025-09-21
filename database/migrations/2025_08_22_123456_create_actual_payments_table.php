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
        // Create the actual_payments table with all columns
        Schema::create('actual_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->date('payment_date');
            $table->decimal('amount', 20, 2);
            $table->decimal('exchange_rate', 10, 4)->nullable();
            $table->string('currency', 3)->default('UZS');
            $table->integer('year');
            $table->integer('quarter'); // 0 for initial payment, 1-4 for quarters
            $table->boolean('is_initial_payment')->default(false);
            $table->foreignId('amendment_id')->nullable()->constrained('contract_amendments')->onDelete('set null');
            $table->string('payment_category', 50)->default('quarterly'); // 'initial', 'quarterly', 'final'
            $table->string('payment_number', 50)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['contract_id', 'year', 'quarter']);
            $table->index(['contract_id', 'payment_date']);
            $table->index(['contract_id', 'is_initial_payment']);
            $table->index(['contract_id', 'payment_category']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the entire actual_payments table
        Schema::dropIfExists('actual_payments');
    }
};