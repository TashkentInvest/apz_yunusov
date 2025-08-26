<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
    }

    public function down()
    {
        Schema::dropIfExists('districts');
    }
};
