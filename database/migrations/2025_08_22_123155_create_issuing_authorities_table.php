<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('issuing_authorities', function (Blueprint $table) {
            $table->id();
            $table->string('name_uz', 200);
            $table->string('name_ru', 200)->nullable();
            $table->string('code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('issuing_authorities');
    }
};
