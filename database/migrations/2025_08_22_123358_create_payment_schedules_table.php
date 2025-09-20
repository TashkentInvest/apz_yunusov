<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // PaymentSchedule jadvaliga amendment_id maydonini qo'shish
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->unsignedBigInteger('amendment_id')->nullable();
            $table->integer('year');
            $table->integer('quarter');
            $table->decimal('quarter_amount', 15, 2);
            $table->decimal('custom_percent', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['contract_id', 'year', 'quarter']);
            $table->index(['contract_id', 'amendment_id', 'is_active']);
        });


        // ContractAmendments jadvalini yaratish

        // Contracts jadvaliga qo'shimcha ma'lumotlar
        Schema::table('contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('contracts', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // ActualPayments jadvalidan amendment ma'lumotlarini olib tashlash
        Schema::table('actual_payments', function (Blueprint $table) {
            if (Schema::hasColumn('actual_payments', 'amendment_id')) {
                $table->dropForeign(['amendment_id']);
                $table->dropColumn(['amendment_id', 'amendment_notes']);
            }
        });

        // Contracts jadvalidan qo'shimcha maydonlarni olib tashlash
        Schema::table('contracts', function (Blueprint $table) {
            if (Schema::hasColumn('contracts', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
        });

        // ContractAmendments jadvalini o'chirish
        Schema::dropIfExists('contract_amendments');

        // PaymentSchedule jadvalidan amendment ma'lumotlarini olib tashlash
        Schema::table('payment_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('payment_schedules', 'amendment_id')) {
                $table->dropForeign(['amendment_id']);
                $table->dropColumn(['amendment_id', 'custom_percent', 'created_by']);
            }
        });
    }
};
