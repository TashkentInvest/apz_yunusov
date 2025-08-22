<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number', 50)->unique();
            $table->foreignId('object_id')->constrained('objects');
            $table->foreignId('subject_id')->constrained('subjects');

            // Шартнома асосий маълумотлари
            $table->date('contract_date');
            $table->date('completion_date')->nullable();
            $table->foreignId('status_id')->constrained('contract_statuses');

            // Ҳисоблаш маълумотлари
            $table->foreignId('base_amount_id')->constrained('base_calculation_amounts');
            $table->decimal('contract_volume', 12, 2);
            $table->decimal('coefficient', 5, 2)->default(1.00);
            $table->decimal('total_amount', 15, 2);
            $table->text('formula')->nullable();

            // Тўлов шартлари
            $table->enum('payment_type', ['full', 'installment'])->default('installment');
            $table->integer('initial_payment_percent')->default(20);
            $table->integer('construction_period_years')->default(2);
            $table->integer('quarters_count')->default(8);

            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status_id');
            $table->index(['contract_date', 'completion_date']);
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contracts');
    }
};
