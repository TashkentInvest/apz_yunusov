<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('art_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_id')->constrained('objects');

            $table->string('application_number', 50)->nullable();
            $table->date('application_date')->nullable();
            $table->string('art_number', 50)->nullable();
            $table->date('art_date')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('art_info');
    }
};
