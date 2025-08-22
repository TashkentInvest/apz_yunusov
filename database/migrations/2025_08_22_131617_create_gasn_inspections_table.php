<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('gasn_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_id')->constrained('objects');

            $table->string('application_number', 50)->nullable();
            $table->date('application_date')->nullable();
            $table->string('copy_number', 50)->nullable();
            $table->date('copy_date')->nullable();
            $table->decimal('volume', 12, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gasn_inspections');
    }
};
