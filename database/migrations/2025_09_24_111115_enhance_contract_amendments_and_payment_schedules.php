<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enhance contract_amendments table
        Schema::table('contract_amendments', function (Blueprint $table) {
            // Check and add sequential number if not exists
            if (!Schema::hasColumn('contract_amendments', 'sequential_number')) {
                $table->integer('sequential_number')->nullable()
                    ->comment('Auto-generated sequence number')->after('amendment_date');
            }

            // Check and add impact tracking fields
            if (!Schema::hasColumn('contract_amendments', 'impact_summary')) {
                $table->json('impact_summary')->nullable()
                    ->comment('Calculated impact of changes')->after('description');
            }

            if (!Schema::hasColumn('contract_amendments', 'applied_changes')) {
                $table->json('applied_changes')->nullable()
                    ->comment('Changes that were applied when approved')->after('impact_summary');
            }

            if (!Schema::hasColumn('contract_amendments', 'changes_summary')) {
                $table->text('changes_summary')->nullable()
                    ->comment('Human-readable summary of changes')->after('applied_changes');
            }

            // Check and add audit fields
            if (!Schema::hasColumn('contract_amendments', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            }

            if (!Schema::hasColumn('contract_amendments', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('updated_at');
            }

            // Check and add metadata fields
            if (!Schema::hasColumn('contract_amendments', 'amendment_type')) {
                $table->string('amendment_type', 50)->default('standard')
                    ->comment('Type of amendment: standard, emergency, correction')->after('deleted_at');
            }

            if (!Schema::hasColumn('contract_amendments', 'financial_impact')) {
                $table->decimal('financial_impact', 15, 2)->nullable()
                    ->comment('Net financial impact')->after('amendment_type');
            }

            if (!Schema::hasColumn('contract_amendments', 'schedule_impact_days')) {
                $table->integer('schedule_impact_days')->nullable()
                    ->comment('Schedule impact in days')->after('financial_impact');
            }

            // Check and add parent amendment relationship
            if (!Schema::hasColumn('contract_amendments', 'parent_amendment_id')) {
                $table->foreignId('parent_amendment_id')->nullable()
                    ->constrained('contract_amendments')->nullOnDelete()
                    ->comment('Reference to parent amendment if this is a sub-amendment')
                    ->after('schedule_impact_days');
            }
        });

        // Add indexes to contract_amendments if they don't exist
        $indexes = DB::select("SHOW INDEX FROM contract_amendments");
        $existingIndexes = collect($indexes)->pluck('Key_name')->toArray();

        Schema::table('contract_amendments', function (Blueprint $table) use ($existingIndexes) {
            if (!in_array('contract_amendments_amendment_number_index', $existingIndexes)) {
                $table->index('amendment_number');
            }
            if (!in_array('contract_amendments_amendment_date_index', $existingIndexes)) {
                $table->index('amendment_date');
            }
            if (!in_array('contract_amendments_contract_id_amendment_date_index', $existingIndexes)) {
                $table->index(['contract_id', 'amendment_date']);
            }
            if (!in_array('contract_amendments_contract_id_sequential_number_index', $existingIndexes)) {
                $table->index(['contract_id', 'sequential_number']);
            }
            if (!in_array('contract_amendments_is_approved_index', $existingIndexes)) {
                $table->index('is_approved');
            }
        });

        // Fix the approved_by foreign key constraint
        try {
            Schema::table('contract_amendments', function (Blueprint $table) {
                $table->dropForeign(['approved_by']);
                $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            });
        } catch (Exception $e) {
            // Foreign key might not exist or already be correct
        }

        // Enhance payment_schedules table only if columns don't exist
        Schema::table('payment_schedules', function (Blueprint $table) {
            // Check and add amendment_id
            if (!Schema::hasColumn('payment_schedules', 'amendment_id')) {
                $table->foreignId('amendment_id')->nullable()->after('contract_id')
                    ->constrained('contract_amendments')->nullOnDelete()
                    ->comment('Amendment that created this schedule');
            }

            // Check and add deactivation tracking fields
            if (!Schema::hasColumn('payment_schedules', 'deactivated_at')) {
                $table->timestamp('deactivated_at')->nullable()->after('is_active')
                    ->comment('When this schedule was deactivated');
            }

            if (!Schema::hasColumn('payment_schedules', 'deactivated_reason')) {
                $table->string('deactivated_reason')->nullable()->after('deactivated_at')
                    ->comment('Reason for deactivation');
            }

            if (!Schema::hasColumn('payment_schedules', 'amendment_impact')) {
                $table->text('amendment_impact')->nullable()->after('deactivated_reason')
                    ->comment('How this schedule was affected by amendments');
            }
        });

        // Add indexes to payment_schedules if they don't exist
        $paymentIndexes = DB::select("SHOW INDEX FROM payment_schedules");
        $existingPaymentIndexes = collect($paymentIndexes)->pluck('Key_name')->toArray();

        Schema::table('payment_schedules', function (Blueprint $table) use ($existingPaymentIndexes) {
            if (!in_array('payment_schedules_contract_id_amendment_id_index', $existingPaymentIndexes)) {
                $table->index(['contract_id', 'amendment_id']);
            }
            if (!in_array('payment_schedules_amendment_id_index', $existingPaymentIndexes)) {
                $table->index(['amendment_id']);
            }
            if (!in_array('payment_schedules_deactivated_at_index', $existingPaymentIndexes)) {
                $table->index('deactivated_at');
            }
        });

        // Update existing data: set sequential numbers for existing amendments (only if column was added)
        if (Schema::hasColumn('contract_amendments', 'sequential_number')) {
            DB::statement("
                UPDATE contract_amendments
                SET sequential_number = (
                    SELECT COUNT(*)
                    FROM (SELECT * FROM contract_amendments) as ca2
                    WHERE ca2.contract_id = contract_amendments.contract_id
                    AND ca2.created_at <= contract_amendments.created_at
                )
                WHERE sequential_number IS NULL
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from payment_schedules
        try {
            Schema::table('payment_schedules', function (Blueprint $table) {
                $table->dropIndex(['contract_id', 'amendment_id']);
            });
        } catch (Exception $e) {}

        try {
            Schema::table('payment_schedules', function (Blueprint $table) {
                $table->dropIndex(['amendment_id']);
            });
        } catch (Exception $e) {}

        try {
            Schema::table('payment_schedules', function (Blueprint $table) {
                $table->dropIndex(['deactivated_at']);
            });
        } catch (Exception $e) {}

        // Remove columns from payment_schedules if they exist
        Schema::table('payment_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('payment_schedules', 'amendment_id')) {
                $table->dropForeign(['amendment_id']);
                $table->dropColumn('amendment_id');
            }

            if (Schema::hasColumn('payment_schedules', 'deactivated_at')) {
                $table->dropColumn('deactivated_at');
            }

            if (Schema::hasColumn('payment_schedules', 'deactivated_reason')) {
                $table->dropColumn('deactivated_reason');
            }

            if (Schema::hasColumn('payment_schedules', 'amendment_impact')) {
                $table->dropColumn('amendment_impact');
            }
        });

        // Remove indexes from contract_amendments
        try {
            Schema::table('contract_amendments', function (Blueprint $table) {
                $table->dropIndex(['contract_id', 'amendment_date']);
                $table->dropIndex(['contract_id', 'sequential_number']);
                $table->dropIndex(['amendment_number']);
                $table->dropIndex(['amendment_date']);
                $table->dropIndex(['is_approved']);
            });
        } catch (Exception $e) {}

        // Remove columns from contract_amendments
        Schema::table('contract_amendments', function (Blueprint $table) {
            if (Schema::hasColumn('contract_amendments', 'parent_amendment_id')) {
                $table->dropForeign(['parent_amendment_id']);
                $table->dropColumn('parent_amendment_id');
            }

            if (Schema::hasColumn('contract_amendments', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }

            $columnsToRemove = [
                'sequential_number',
                'impact_summary',
                'applied_changes',
                'changes_summary',
                'deleted_at',
                'amendment_type',
                'financial_impact',
                'schedule_impact_days'
            ];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('contract_amendments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Restore original approved_by constraint
        try {
            Schema::table('contract_amendments', function (Blueprint $table) {
                $table->dropForeign(['approved_by']);
                $table->foreign('approved_by')->references('id')->on('users');
            });
        } catch (Exception $e) {}
    }
};
