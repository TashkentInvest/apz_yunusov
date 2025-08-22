<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts');

            $table->string('insurance_company', 200)->nullable();
            $table->string('company_inn', 9)->nullable();
            $table->string('document_name', 200)->nullable();
            $table->string('document_number', 50)->nullable();
            $table->date('document_date')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('insurance_policies');
    }
};
