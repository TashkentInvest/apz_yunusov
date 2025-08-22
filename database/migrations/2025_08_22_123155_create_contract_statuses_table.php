<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contract_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name_uz', 100);
            $table->string('name_ru', 100)->nullable();
            $table->string('code', 20)->nullable();
            $table->string('color', 7)->nullable(); // hex color code
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contract_statuses');
    }
};
