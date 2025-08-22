<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('construction_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_uz', 100);
            $table->string('name_ru', 100)->nullable();
            $table->string('code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('construction_types');
    }
};
