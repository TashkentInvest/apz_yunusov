<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contract_amendments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts');
            $table->integer('amendment_number');
            $table->date('amendment_date');
            $table->text('reason')->nullable();

            // Ўзгарган маълумотлар
            $table->decimal('old_volume', 12, 2)->nullable();
            $table->decimal('new_volume', 12, 2)->nullable();
            $table->decimal('old_coefficient', 5, 2)->nullable();
            $table->decimal('new_coefficient', 5, 2)->nullable();
            $table->decimal('old_amount', 15, 2)->nullable();
            $table->decimal('new_amount', 15, 2)->nullable();
            $table->foreignId('old_base_amount_id')->nullable()->constrained('base_calculation_amounts');
            $table->foreignId('new_base_amount_id')->nullable()->constrained('base_calculation_amounts');

            // Банк реквизитлари ўзгариши
            $table->text('bank_changes')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['contract_id', 'amendment_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('contract_amendments');
    }
};
