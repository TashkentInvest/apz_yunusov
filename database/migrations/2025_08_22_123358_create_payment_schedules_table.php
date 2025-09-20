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
            
        Schema::table('payment_schedules', function (Blueprint $table) {
            $table->foreignId('amendment_id')->nullable()->after('contract_id')->constrained('contract_amendments')->onDelete('cascade');
            $table->boolean('is_initial_payment')->default(false)->after('quarter_amount');
            $table->decimal('custom_percent', 5, 2)->nullable()->after('is_initial_payment');
            
            $table->index(['contract_id', 'amendment_id', 'is_active']);
            $table->index(['contract_id', 'is_initial_payment']);
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
