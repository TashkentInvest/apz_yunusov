<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts');
            $table->foreignId('amendment_id')->nullable()->constrained('contract_amendments');

            $table->integer('year');
            $table->integer('quarter'); // 1-4
            $table->decimal('quarter_amount', 15, 2);
            $table->decimal('custom_percent', 5, 2)->nullable(); // Manual тавсив учун

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['contract_id', 'amendment_id', 'year', 'quarter']);
            $table->index(['year', 'quarter']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_schedules');
    }
};
