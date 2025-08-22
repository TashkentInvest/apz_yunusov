<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('base_calculation_amounts', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 15, 2);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_current')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('effective_from');
            $table->index('is_current');
        });
    }

    public function down()
    {
        Schema::dropIfExists('base_calculation_amounts');
    }
};
