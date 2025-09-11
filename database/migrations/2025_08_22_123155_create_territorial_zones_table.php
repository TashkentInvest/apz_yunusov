<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('territorial_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name_uz');
            $table->string('name_ru');
            $table->decimal('coefficient', 3, 2);
            $table->string('color', 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('territorial_zones')->insert([
            ['id' => 1, 'name_uz' => '1-zona (Markaziy hududlar)', 'name_ru' => '1-зона (Центральные районы)', 'coefficient' => 1.40, 'color' => '#dc2626', 'is_active' => true],
            ['id' => 2, 'name_uz' => '2-zona (Shahar markazi yaqini)', 'name_ru' => '2-зона (Близко к центру)', 'coefficient' => 1.25, 'color' => '#ea580c', 'is_active' => true],
            ['id' => 3, 'name_uz' => '3-zona (Oddiy hududlar)', 'name_ru' => '3-зона (Обычные районы)', 'coefficient' => 1.00, 'color' => '#ca8a04', 'is_active' => true],
            ['id' => 4, 'name_uz' => '4-zona (Chekka hududlar)', 'name_ru' => '4-зона (Окраинные районы)', 'coefficient' => 0.75, 'color' => '#16a34a', 'is_active' => true],
            ['id' => 5, 'name_uz' => '5-zona (Uzoq hududlar)', 'name_ru' => '5-зона (Удаленные районы)', 'coefficient' => 0.50, 'color' => '#0891b2', 'is_active' => true]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('territorial_zones');
    }
};
