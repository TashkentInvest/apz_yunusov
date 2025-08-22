<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects');

            // Ариза маълумотлари
            $table->string('application_number', 50)->nullable();
            $table->date('application_date')->nullable();
            $table->string('permit_document_name', 300)->nullable();

            // Рухсатнома маълумотлари
            $table->foreignId('permit_type_id')->nullable()->constrained('permit_types');
            $table->foreignId('issuing_authority_id')->nullable()->constrained('issuing_authorities');
            $table->date('permit_date')->nullable();
            $table->string('permit_number', 100)->nullable();

            // Объект жойлашуви
            $table->foreignId('district_id')->nullable()->constrained('districts');
            $table->text('address')->nullable();
            $table->string('cadastre_number', 50)->nullable();
            $table->string('work_type', 200)->nullable();

            // Лойиҳа ҳажми
            $table->decimal('construction_volume', 12, 2)->nullable();
            $table->decimal('above_permit_volume', 12, 2)->default(0);
            $table->decimal('parking_volume', 12, 2)->default(0);
            $table->decimal('technical_rooms_volume', 12, 2)->default(0);
            $table->decimal('common_area_volume', 12, 2)->default(0);

            $table->foreignId('construction_type_id')->nullable()->constrained('construction_types');
            $table->foreignId('object_type_id')->nullable()->constrained('object_types');
            $table->foreignId('territorial_zone_id')->nullable()->constrained('territorial_zones');
            $table->string('location_type', 100)->nullable();

            $table->text('additional_info')->nullable();
            $table->string('geolocation', 100)->nullable(); // координаталар

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('district_id');
            $table->index('cadastre_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('objects');
    }
};
