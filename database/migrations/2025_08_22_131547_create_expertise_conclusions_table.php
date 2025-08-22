<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('expertise_conclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_id')->constrained('objects');

            $table->string('organization_name', 200)->nullable();
            $table->string('conclusion_number', 50)->nullable();
            $table->date('conclusion_date')->nullable();
            $table->string('transparent_at_number', 50)->nullable();
            $table->date('transparent_at_date')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('expertise_conclusions');
    }
};

