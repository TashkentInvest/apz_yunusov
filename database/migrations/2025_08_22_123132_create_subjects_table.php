<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_legal_entity')->default(true);

            // Юридик шахс учун
            $table->foreignId('org_form_id')->nullable()->constrained('org_forms');
            $table->string('company_name', 300)->nullable();
            $table->string('inn', 9)->nullable();
            $table->boolean('is_resident')->default(true);
            $table->string('country_code', 3)->default('UZ');
            $table->string('oked', 10)->nullable();
            $table->string('bank_name', 200)->nullable();
            $table->string('bank_code', 10)->nullable();
            $table->string('bank_account', 30)->nullable();
            $table->text('legal_address')->nullable();

            // Жисмоний шахс учун
            $table->string('document_type', 50)->nullable();
            $table->string('document_series', 10)->nullable();
            $table->string('document_number', 20)->nullable();
            $table->string('issued_by', 200)->nullable();
            $table->date('issued_date')->nullable();
            $table->string('pinfl', 14)->nullable();

            // Умумий майдонлар
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('physical_address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('inn');
            $table->index('pinfl');
            $table->index('is_legal_entity');
        });
    }

    public function down()
    {
        Schema::dropIfExists('subjects');
    }
};
