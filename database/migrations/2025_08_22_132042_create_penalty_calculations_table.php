<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penalty_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts');

            $table->date('scheduled_date');
            $table->decimal('scheduled_amount', 15, 2);
            $table->decimal('unpaid_amount', 15, 2);
            $table->integer('overdue_days');
            $table->decimal('penalty_rate', 5, 4)->default(0.0001); // 0.01%
            $table->decimal('penalty_amount', 15, 2);
            $table->decimal('max_penalty_percent', 5, 2)->default(15.00);

            $table->timestamp('calculated_at')->useCurrent();

            $table->index(['contract_id', 'scheduled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalty_calculations');
    }
};
