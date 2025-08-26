<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contract_statuses', function (Blueprint $table) {
            $table->id(); // Ҳолат ID рақами
            $table->string('name_uz', 100); // Ҳолат номи ўзбек тилида
            $table->string('name_ru', 100)->nullable(); // Ҳолат номи рус тилида
            $table->string('code', 20)->nullable(); // Ҳолат коди
            $table->string('color', 7)->nullable(); // Ранг коди (hex формат)
            $table->boolean('is_active')->default(true); // Фаол ҳолати
            $table->timestamps(); // Яратилган ва янгиланган санаси
        });
    }

    public function down()
    {
        Schema::dropIfExists('contract_statuses');
    }
};
