<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cancellation_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('name_uz', 200);
            $table->string('name_ru', 200)->nullable();
            $table->enum('type', ['company_request', 'council_rejection', 'self_wish', 'our_proposal']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cancellation_reasons');
    }
};
