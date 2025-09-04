<?php
// Create Migration: create_payment_histories_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->string('action'); // 'created', 'updated', 'deleted'
            $table->string('table_name'); // 'payment_schedules', 'actual_payments', 'contracts'
            $table->unsignedBigInteger('record_id'); // ID of the record being tracked
            $table->json('old_values')->nullable(); // Previous values
            $table->json('new_values')->nullable(); // New values
            $table->text('description')->nullable(); // Human readable description
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who made the change
            $table->timestamps();

            $table->index(['contract_id', 'created_at']);
            $table->index(['table_name', 'record_id']);
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_histories');
    }
};
