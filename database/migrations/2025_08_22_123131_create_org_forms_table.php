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
            $table->id(); // Ташкилий шакл ID рақами
            $table->string('name_uz', 100); // Ташкилий шакл номи ўзбек тилида
            $table->string('name_ru', 100)->nullable(); // Ташкилий шакл номи рус тилида
            $table->string('code', 10)->nullable(); // Ташкилий шакл коди
            $table->boolean('is_active')->default(true); // Фаол ҳолати
            $table->timestamps(); // Яратилган ва янгиланган санаси
        });
    }

    public function down()
    {
        Schema::dropIfExists('org_forms');
    }
};
