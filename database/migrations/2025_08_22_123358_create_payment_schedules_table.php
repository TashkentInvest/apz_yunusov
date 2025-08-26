<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Тўлов жадвали - режалаштирилган тўловлар
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id(); // Тўлов жадвали ID рақами
            $table->foreignId('contract_id')->constrained('contracts'); // Шартнома маълумоти
            $table->foreignId('amendment_id')->nullable()->constrained('contract_amendments'); // Қўшимча битим

            $table->integer('year'); // Йил
            $table->integer('quarter'); // Чорак йил (1-4)
            $table->decimal('quarter_amount', 15, 2); // Чорак йил суммаси
            $table->decimal('custom_percent', 5, 2)->nullable(); // Қўлда киритилган фоиз

            $table->boolean('is_active')->default(true); // Фаол ҳолати
            $table->timestamps(); // Яратилган ва янгиланган санаси

            // Ноёб калит - бир шартнома учун бир чорак йилда битта ёзув
            $table->unique(['contract_id', 'amendment_id', 'year', 'quarter']);
            $table->index(['year', 'quarter']); // Вақт бўйича индекс
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_schedules');
    }
};
