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
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->integer('amendment_number'); // Qo'shimcha kelishuv raqami (1, 2, 3...)
            $table->date('amendment_date'); // Qo'shimcha kelishuv sanasi
            $table->text('reason'); // O'zgarish sababi

            // Eski qiymatlar
            $table->decimal('old_volume', 12, 2)->nullable(); // Eski hajm
            $table->decimal('old_coefficient', 5, 4)->nullable(); // Eski koeffitsient
            $table->decimal('old_amount', 15, 2)->nullable(); // Eski summa
            $table->foreignId('old_base_amount_id')->nullable()->constrained('base_calculation_amounts');

            // Yangi qiymatlar
            $table->decimal('new_volume', 12, 2)->nullable(); // Yangi hajm
            $table->decimal('new_coefficient', 5, 4)->nullable(); // Yangi koeffitsient
            $table->decimal('new_amount', 15, 2)->nullable(); // Yangi summa
            $table->foreignId('new_base_amount_id')->nullable()->constrained('base_calculation_amounts');

            // Qo'shimcha ma'lumotlar
            $table->text('bank_changes')->nullable(); // Bank rekvizitlari o'zgarishi
            $table->boolean('is_active')->default(true); // Faol holati
            $table->unsignedBigInteger('created_by')->nullable(); // Kim tomonidan yaratilgan
            $table->timestamps();

            // Indekslar
            $table->unique(['contract_id', 'amendment_number']); // Bir shartnoma uchun bir xil raqamli kelishuv bo'lmasligi
            $table->index(['contract_id', 'is_active']);
            $table->index('amendment_date');
        });

    }

    public function down()
    {
        Schema::dropIfExists('contract_amendments');
    }
};
