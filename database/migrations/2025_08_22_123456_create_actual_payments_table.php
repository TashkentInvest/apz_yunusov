<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('actual_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts');
            $table->string('payment_number', 50)->nullable();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);

            $table->integer('year');
            $table->integer('quarter');

            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['contract_id', 'payment_date']);
            $table->index(['year', 'quarter']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('actual_payments');
    }
};
