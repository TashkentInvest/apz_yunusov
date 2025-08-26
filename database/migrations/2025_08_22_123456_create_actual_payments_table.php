<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Амалий тўловлар - ҳақиқий тўловлар маълумоти

        Schema::create('actual_payments', function (Blueprint $table) {
            $table->id(); // Тўлов ID рақами
            $table->foreignId('contract_id')->constrained('contracts'); // Шартнома маълумоти
            $table->string('payment_number', 50)->nullable(); // Тўлов рақами
            $table->date('payment_date'); // Тўлов санаси
            $table->decimal('amount', 15, 2); // Тўлов суммаси

            $table->integer('year'); // Йил
            $table->integer('quarter'); // Чорак йил

            $table->text('notes')->nullable(); // Изоҳлар
            $table->unsignedBigInteger('created_by')->nullable(); // Ким томонидан яратилган
            $table->timestamps(); // Яратилган ва янгиланган санаси

            // Тизимлаштириш учун индекслар
            $table->index(['contract_id', 'payment_date']); // Шартнома ва сана бўйича
            $table->index(['year', 'quarter']); // Вақт бўйича индекс
        });
    }

    public function down()
    {
        Schema::dropIfExists('actual_payments');
    }
};
