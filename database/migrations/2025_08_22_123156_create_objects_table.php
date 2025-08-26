<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        // Объектлар жадвали - қурилиш объектлари маълумотлари
        Schema::create('objects', function (Blueprint $table) {
            $table->id(); // Объект ID рақами
            $table->foreignId('subject_id')->constrained('subjects'); // Субъект маълумоти

            // Ариза маълумотлари
            $table->string('application_number', 50)->nullable(); // Ариза рақами
            $table->date('application_date')->nullable(); // Ариза санаси
            $table->string('permit_document_name', 300)->nullable(); // Рухсатнома ҳужжати номи

            // Рухсатнома маълумотлари
            $table->foreignId('permit_type_id')->nullable()->constrained('permit_types'); // Рухсатнома тури
            $table->foreignId('issuing_authority_id')->nullable()->constrained('issuing_authorities'); // Берувчи орган
            $table->date('permit_date')->nullable(); // Рухсатнома санаси
            $table->string('permit_number', 100)->nullable(); // Рухсатнома рақами

            // Объект жойлашуви
            $table->foreignId('district_id')->nullable()->constrained('districts'); // Туман
            $table->text('address')->nullable(); // Манзил
            $table->string('cadastre_number', 50)->nullable(); // Кадастр рақами
            $table->string('work_type', 200)->nullable(); // Иш тури

            // Қурилиш ҳажми маълумотлари
            $table->decimal('construction_volume', 12, 2)->nullable(); // Қурилиш ҳажми
            $table->decimal('above_permit_volume', 12, 2)->default(0); // Рухсатномадан ортиқ ҳажм
            $table->decimal('parking_volume', 12, 2)->default(0); // Автотураргоҳ ҳажми
            $table->decimal('technical_rooms_volume', 12, 2)->default(0); // Техник хоналар ҳажми
            $table->decimal('common_area_volume', 12, 2)->default(0); // Умумий майдонлар ҳажми

            $table->foreignId('construction_type_id')->nullable()->constrained('construction_types'); // Қурилиш тури
            $table->foreignId('object_type_id')->nullable()->constrained('object_types'); // Объект тури
            $table->foreignId('territorial_zone_id')->nullable()->constrained('territorial_zones'); // Ҳудудий зона
            $table->string('location_type', 100)->nullable(); // Жойлашув тури

            $table->text('additional_info')->nullable(); // Қўшимча маълумот
            $table->string('geolocation', 100)->nullable(); // Географик координаталар

            $table->boolean('is_active')->default(true); // Фаол ҳолати
            $table->timestamps(); // Яратилган ва янгиланган санаси

            $table->index('district_id'); // Туман бўйича индекс
            $table->index('cadastre_number'); // Кадастр рақами бўйича индекс
        });
    }

    public function down()
    {
        Schema::dropIfExists('objects');
    }
};
