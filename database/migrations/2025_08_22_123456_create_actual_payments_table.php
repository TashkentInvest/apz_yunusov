<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Амалий тўловлар - ҳақиқий тўловлар маълумоти

        Schema::table('actual_payments', function (Blueprint $table) {
            $table->boolean('is_initial_payment')->default(false)->after('quarter');
            $table->foreignId('amendment_id')->nullable()->after('is_initial_payment')->constrained('contract_amendments')->onDelete('set null');
            $table->string('payment_category', 50)->default('quarterly')->after('amendment_id'); // 'initial', 'quarterly', 'final'
            $table->decimal('exchange_rate', 10, 4)->nullable()->after('amount');
            $table->string('currency', 3)->default('UZS')->after('exchange_rate');
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users');

            $table->index(['contract_id', 'is_initial_payment']);
            $table->index(['contract_id', 'payment_category']);
            $table->index(['contract_id', 'year', 'quarter']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('actual_payments');
    }
};
