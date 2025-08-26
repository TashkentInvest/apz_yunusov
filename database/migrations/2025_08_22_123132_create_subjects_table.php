<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        // Субъектлар жадвали - юридик ва жисмоний шахслар маълумотлари
        Schema::create('subjects', function (Blueprint $table) {
            $table->id(); // Субъект ID рақами
            $table->boolean('is_legal_entity')->default(true); // Юридик шахс эканлиги

            // Юридик шахс маълумотлари
            $table->foreignId('org_form_id')->nullable()->constrained('org_forms'); // Ташкилий шакл
            $table->string('company_name', 300)->nullable(); // Корхона номи
            $table->string('inn', 9)->nullable(); // ИНН рақами
            $table->boolean('is_resident')->default(true); // Резидент эканлиги
            $table->string('country_code', 3)->default('UZ'); // Давлат коди
            $table->string('oked', 10)->nullable(); // ОКЭД коди
            $table->string('bank_name', 200)->nullable(); // Банк номи
            $table->string('bank_code', 10)->nullable(); // Банк коди
            $table->string('bank_account', 30)->nullable(); // Ҳисоб рақами
            $table->text('legal_address')->nullable(); // Юридик манзил

            // Жисмоний шахс маълумотлари
            $table->string('document_type', 50)->nullable(); // Ҳужжат тури
            $table->string('document_series', 10)->nullable(); // Ҳужжат серияси
            $table->string('document_number', 20)->nullable(); // Ҳужжат рақами
            $table->string('issued_by', 200)->nullable(); // Ким томонидан берилган
            $table->date('issued_date')->nullable(); // Берилган санаси
            $table->string('pinfl', 14)->nullable(); // ПИНФЛ рақами

            // Умумий маълумотлар
            $table->string('phone', 20)->nullable(); // Телефон рақами
            $table->string('email', 100)->nullable(); // Электрон почта
            $table->text('physical_address')->nullable(); // Яшаш манзили
            $table->boolean('is_active')->default(true); // Фаол ҳолати
            $table->timestamps(); // Яратилган ва янгиланган санаси

            $table->index('inn'); // ИНН бўйича индекс
            $table->index('pinfl'); // ПИНФЛ бўйича индекс
            $table->index('is_legal_entity'); // Юридик шахс белгиси бўйича индекс
        });
    }

    public function down()
    {
        Schema::dropIfExists('subjects');
    }
};
