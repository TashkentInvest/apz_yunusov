<?php

// database/migrations/2024_01_01_000001_create_org_forms_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('org_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name_uz', 100);
            $table->string('name_ru', 100)->nullable();
            $table->string('code', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('org_forms');
    }
};
