<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixQuarterAmountPrecisionInPaymentSchedulesTable extends Migration
{
    public function up()
    {
        Schema::table('payment_schedules', function (Blueprint $table) {
            $table->decimal('quarter_amount', 15, 2)->change();
        });
    }

    public function down()
    {
        Schema::table('payment_schedules', function (Blueprint $table) {
            $table->bigInteger('quarter_amount')->change();
        });
    }
}
