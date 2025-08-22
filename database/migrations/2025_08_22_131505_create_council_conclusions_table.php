<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('council_conclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_id')->constrained('objects');

            $table->string('application_number', 50)->nullable();
            $table->date('application_date')->nullable();
            $table->string('conclusion_number', 50)->nullable();
            $table->date('conclusion_date')->nullable();
            $table->enum('status', ['approved', 'rejected', 'pending'])->default('pending');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('council_conclusions');
    }
};
