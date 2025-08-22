<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contract_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts');
            $table->foreignId('cancellation_reason_id')->constrained('cancellation_reasons');
            $table->date('cancellation_date');

            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->date('refund_date')->nullable();

            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contract_cancellations');
    }
};
