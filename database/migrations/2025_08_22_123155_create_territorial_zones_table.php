<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('territorial_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name_uz', 100);
            $table->string('name_ru', 100)->nullable();
            $table->string('code', 20)->nullable();
            $table->decimal('coefficient', 5, 2)->default(1.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('territorial_zones');
    }
};
