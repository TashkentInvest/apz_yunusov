<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
            Schema::create('construction_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_uz');
            $table->string('name_ru');
            $table->decimal('coefficient', 3, 1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('construction_types')->insert([
            ['id' => 1, 'name_uz' => 'Yangi kapital qurilish', 'name_ru' => 'Новое капитальное строительство', 'coefficient' => 1.0, 'is_active' => true],
            ['id' => 2, 'name_uz' => 'Obyektni rekonstruksiya qilish', 'name_ru' => 'Реконструкция объекта', 'coefficient' => 1.0, 'is_active' => true],
            ['id' => 3, 'name_uz' => 'Ekspertiza talab etilmaydigan rekonstruksiya', 'name_ru' => 'Реконструкция без экспертизы', 'coefficient' => 0.0, 'is_active' => true],
            ['id' => 4, 'name_uz' => 'Hajm o\'zgarmaydigan rekonstruksiya', 'name_ru' => 'Реконструкция без изменения объема', 'coefficient' => 0.0, 'is_active' => true]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('construction_types');
    }
};
