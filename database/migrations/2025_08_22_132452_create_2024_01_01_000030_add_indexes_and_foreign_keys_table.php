<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Additional performance indexes
        Schema::table('contracts', function (Blueprint $table) {
            $table->index(['created_at', 'is_active']);
            $table->index(['total_amount']);
            $table->index(['contract_date', 'status_id']);
        });

        Schema::table('actual_payments', function (Blueprint $table) {
            $table->index(['payment_date', 'amount']);
            $table->index(['contract_id', 'year', 'quarter']);
        });

        Schema::table('payment_schedules', function (Blueprint $table) {
            $table->index(['is_active', 'year', 'quarter']);
            $table->index(['contract_id', 'is_active']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->index(['is_active', 'is_legal_entity']);
            $table->index(['company_name']);
            $table->index(['created_at']);
        });

        Schema::table('objects', function (Blueprint $table) {
            $table->index(['subject_id', 'is_active']);
            $table->index(['construction_volume']);
        });

        // Compound indexes for better query performance
        Schema::table('contracts', function (Blueprint $table) {
            $table->index(['subject_id', 'status_id', 'is_active'], 'idx_contracts_subject_status_active');
            $table->index(['object_id', 'contract_date'], 'idx_contracts_object_date');
        });
    }

    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropIndex(['created_at', 'is_active']);
            $table->dropIndex(['total_amount']);
            $table->dropIndex(['contract_date', 'status_id']);
            $table->dropIndex('idx_contracts_subject_status_active');
            $table->dropIndex('idx_contracts_object_date');
        });

        Schema::table('actual_payments', function (Blueprint $table) {
            $table->dropIndex(['payment_date', 'amount']);
            $table->dropIndex(['contract_id', 'year', 'quarter']);
        });

        Schema::table('payment_schedules', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'year', 'quarter']);
            $table->dropIndex(['contract_id', 'is_active']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'is_legal_entity']);
            $table->dropIndex(['company_name']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('objects', function (Blueprint $table) {
            $table->dropIndex(['subject_id', 'is_active']);
            $table->dropIndex(['construction_volume']);
        });
    }
};
