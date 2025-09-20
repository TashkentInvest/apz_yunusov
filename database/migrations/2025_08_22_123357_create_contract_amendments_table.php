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
        Schema::create('contract_amendments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->string('amendment_number', 50);
            $table->date('amendment_date');
            $table->decimal('new_total_amount', 20, 2)->nullable();
            $table->date('new_completion_date')->nullable();
            $table->decimal('new_initial_payment_percent', 5, 2)->nullable();
            $table->integer('new_quarters_count')->nullable();
            $table->text('reason');
            $table->text('description')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->unique(['contract_id', 'amendment_number']);
            $table->index(['contract_id', 'is_approved']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_amendments');
    }
};
