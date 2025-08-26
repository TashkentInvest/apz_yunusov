<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        // Шартнома қўшимча битимлари - ўзгартириш ва қўшимчалар
        Schema::create('contract_amendments', function (Blueprint $table) {
            $table->id(); // Қўшимча битим ID рақами
            $table->foreignId('contract_id')->constrained('contracts'); // Шартнома маълумоти
            $table->integer('amendment_number'); // Қўшимча битим рақами
            $table->date('amendment_date'); // Қўшимча битим санаси
            $table->text('reason')->nullable(); // Ўзгартириш сабаби

            // Ўзгарган қийматлар (эски ва янги)
            $table->decimal('old_volume', 12, 2)->nullable(); // Эски ҳажм
            $table->decimal('new_volume', 12, 2)->nullable(); // Янги ҳажм
            $table->decimal('old_coefficient', 5, 2)->nullable(); // Эски коэффициент
            $table->decimal('new_coefficient', 5, 2)->nullable(); // Янги коэффициент
            $table->decimal('old_amount', 15, 2)->nullable(); // Эски сумма
            $table->decimal('new_amount', 15, 2)->nullable(); // Янги сумма
            $table->foreignId('old_base_amount_id')->nullable()->constrained('base_calculation_amounts'); // Эски асос миқдор
            $table->foreignId('new_base_amount_id')->nullable()->constrained('base_calculation_amounts'); // Янги асос миқдор

            // Банк реквизитлари ўзгариши
            $table->text('bank_changes')->nullable(); // Банк маълумотлари ўзгариши

            $table->boolean('is_active')->default(true); // Фаол ҳолати
            $table->unsignedBigInteger('created_by')->nullable(); // Ким томонидан яратилган
            $table->timestamps(); // Яратилган ва янгиланган санаси

            // Ноёб калит - бир шартнома учун бир рақамли қўшимча битим
            $table->unique(['contract_id', 'amendment_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('contract_amendments');
    }
};
