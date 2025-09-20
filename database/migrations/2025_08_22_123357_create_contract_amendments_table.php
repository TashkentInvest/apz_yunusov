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

            // O'zgartirishlar
            $table->decimal('new_total_amount', 15, 2)->nullable();
            $table->date('new_completion_date')->nullable();
            $table->decimal('new_initial_payment_percent', 5, 2)->nullable();
            $table->integer('new_quarters_count')->nullable();

            // Sabab va tavsif
            $table->text('reason');
            $table->text('description')->nullable();

            // Holat
            $table->boolean('is_approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            // Audit maydonlari
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Indexlar
            $table->index(['contract_id', 'is_approved']);
            $table->unique(['contract_id', 'amendment_number']);
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
