<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('object_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_uz');
            $table->string('name_ru');
            $table->decimal('coefficient', 3, 1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('object_types')->insert([
            ['id' => 1, 'name_uz' => 'Ijtimoiy infratuzilma va turizm obyektlari', 'name_ru' => 'Объекты социальной инфраструктуры и туризма', 'coefficient' => 0.5, 'is_active' => true],
            ['id' => 2, 'name_uz' => 'Davlat ulushi 50% dan ortiq', 'name_ru' => 'Доля государства свыше 50%', 'coefficient' => 0.5, 'is_active' => true],
            ['id' => 3, 'name_uz' => 'Ishlab chiqarish korxonalari', 'name_ru' => 'Производственные предприятия', 'coefficient' => 0.5, 'is_active' => true],
            ['id' => 4, 'name_uz' => 'Omborxonalar', 'name_ru' => 'Складские помещения', 'coefficient' => 0.5, 'is_active' => true],
            ['id' => 5, 'name_uz' => 'Mazkur bo\'limning 1-5 qatorlarida ko\'rsatilmagan Boshqa obyektlar', 'name_ru' => 'Прочие объекты', 'coefficient' => 1.0, 'is_active' => true]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('object_types');
    }
};
