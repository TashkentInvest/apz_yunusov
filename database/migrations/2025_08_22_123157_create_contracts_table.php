<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Шартномалар жадвали - асосий контракт маълумотлари
        Schema::create('contracts', function (Blueprint $table) {
            $table->id(); // Шартнома ID рақами
            $table->string('contract_number', 50)->unique(); // Шартнома рақами (ноёб)
            $table->foreignId('object_id')->constrained('objects'); // Объект маълумоти
            $table->foreignId('subject_id')->constrained('subjects'); // Субъект маълумоти

            // Шартнома асосий маълумотлари
            $table->date('contract_date'); // Шартнома санаси
            $table->date('completion_date')->nullable(); // Якунланиш санаси
            $table->foreignId('status_id')->constrained('contract_statuses'); // Шартнома ҳолати

            // Молиявий ҳисоблаш маълумотлари
            $table->foreignId('base_amount_id')->constrained('base_calculation_amounts'); // Асос ҳисоблаш миқдори
            $table->decimal('contract_volume', 12, 2); // Шартнома ҳажми
            $table->decimal('coefficient', 5, 2)->default(1.00); // Коэффициент
            $table->decimal('total_amount', 15, 2); // Жами сумма
            $table->text('formula')->nullable(); // Ҳисоблаш формуласи

            // Тўлов шартлари
            $table->enum('payment_type', ['full', 'installment'])->default('installment'); // Тўлов тури
            $table->integer('initial_payment_percent')->default(20); // Бошланғич тўлов фоизи
            $table->integer('construction_period_years')->default(2); // Қурилиш муддати (йил)
            $table->integer('quarters_count')->default(8); // Чорак йиллар сони

            $table->boolean('is_active')->default(true); // Фаол ҳолати
            $table->unsignedBigInteger('created_by')->nullable(); // Ким томонидан яратилган
            $table->timestamps(); // Яратилган ва янгиланган санаси
            $table->softDeletes(); // Юмшоқ ўчириш

            // Индекслар тезлик учун
            $table->index('status_id'); // Статус бўйича индекс
            $table->index(['contract_date', 'completion_date']); // Санасалар бўйича индекс
            $table->index('is_active'); // Фаол ҳолат бўйича индекс

 $table->foreignId('last_amendment_id')->nullable();
            $table->integer('amendment_count')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('contracts');
    }
};
