<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id(); // Туман ID рақами
            $table->string('name_uz', 100); // Туман номи ўзбек тилида
            $table->string('name_ru', 100)->nullable(); // Туман номи рус тилида
            $table->string('code', 10)->nullable(); // Туман коди
            $table->boolean('is_active')->default(true); // Фаол ҳолати
            $table->timestamps(); // Яратилган ва янгиланган санаси
        });

        DB::table('districts')->insert([
            ['id' => 1, 'name_uz' => 'Oltoy tumani', 'name_ru' => 'Учтепинский', 'is_active' => true],
            ['id' => 2, 'name_uz' => 'Bektemir tumani', 'name_ru' => 'Бектемирский', 'is_active' => true],
            ['id' => 3, 'name_uz' => 'Chilonzor tumani', 'name_ru' => 'Чиланзарский', 'is_active' => true],
            ['id' => 4, 'name_uz' => 'Yashnobod tumani', 'name_ru' => 'Яшнабадский', 'is_active' => true],
            ['id' => 5, 'name_uz' => 'Yakkasaroy tumani', 'name_ru' => 'Яккасарайский', 'is_active' => true],
            ['id' => 6, 'name_uz' => 'Sergeli tumani', 'name_ru' => 'Сергелийский', 'is_active' => true],
            ['id' => 7, 'name_uz' => 'Yunusobod tumani', 'name_ru' => 'Юнусабадский', 'is_active' => true],
            ['id' => 8, 'name_uz' => 'Olmazar tumani', 'name_ru' => 'Олмазарский', 'is_active' => true],
            ['id' => 9, 'name_uz' => 'Mirzo Ulugbek tumani', 'name_ru' => 'Мирзо Улугбекский', 'is_active' => true],
            ['id' => 10, 'name_uz' => 'Shayxontohur tumani', 'name_ru' => 'Шайхантахурский', 'is_active' => true],
            ['id' => 11, 'name_uz' => 'Mirobod tumani', 'name_ru' => 'Мирабадский', 'is_active' => true],
            ['id' => 12, 'name_uz' => 'Yangihayot tumani', 'name_ru' => 'Янгихаётский', 'is_active' => true]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('districts');
    }
};
