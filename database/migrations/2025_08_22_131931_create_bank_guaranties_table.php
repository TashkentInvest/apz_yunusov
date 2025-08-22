<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('bank_guarantees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts');

            $table->string('bank_name', 200)->nullable();
            $table->string('bank_inn', 9)->nullable();
            $table->string('document_number', 50)->nullable();
            $table->date('document_date')->nullable();
            $table->string('document_name', 200)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_guaranties');
    }
};
