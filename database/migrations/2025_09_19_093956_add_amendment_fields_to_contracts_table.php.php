<?php

// Migration: add_amendment_fields_to_contracts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAmendmentFieldsToContractsTable extends Migration
{
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Add amendment tracking fields if they don't exist
            if (!Schema::hasColumn('contracts', 'last_amendment_id')) {
                $table->foreignId('last_amendment_id')->nullable()->constrained('contract_amendments');
            }

            if (!Schema::hasColumn('contracts', 'amendment_count')) {
                $table->integer('amendment_count')->default(0);
            }

            // Add indexes for better performance
            $table->index(['contract_date', 'total_amount']);
            $table->index(['payment_type', 'is_active']);
        });
    }

    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['last_amendment_id']);
            $table->dropColumn(['last_amendment_id', 'amendment_count']);
            $table->dropIndex(['contract_date', 'total_amount']);
            $table->dropIndex(['payment_type', 'is_active']);
        });
    }
}

// Also update the PaymentSchedule table to add amendment_id if missing
class AddAmendmentIdToPaymentSchedulesTable extends Migration
{
    public function up()
    {
        Schema::table('payment_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_schedules', 'amendment_id')) {
                $table->foreignId('amendment_id')->nullable()->after('contract_id')->constrained('contract_amendments');
            }

            if (!Schema::hasColumn('payment_schedules', 'custom_percent')) {
                $table->decimal('custom_percent', 5, 2)->nullable()->after('plan_amount');
            }

            if (!Schema::hasColumn('payment_schedules', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('custom_percent');
            }

            // Rename plan_amount to quarter_amount for consistency
            if (Schema::hasColumn('payment_schedules', 'plan_amount') && !Schema::hasColumn('payment_schedules', 'quarter_amount')) {
                $table->renameColumn('plan_amount', 'quarter_amount');
            }
        });
    }

    public function down()
    {
        Schema::table('payment_schedules', function (Blueprint $table) {
            $table->dropForeign(['amendment_id']);
            $table->dropColumn(['amendment_id', 'custom_percent', 'is_active']);

            if (Schema::hasColumn('payment_schedules', 'quarter_amount')) {
                $table->renameColumn('quarter_amount', 'plan_amount');
            }
        });
    }
}
