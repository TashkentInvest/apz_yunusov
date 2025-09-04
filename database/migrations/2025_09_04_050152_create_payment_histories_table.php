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
            $table->string('action');
            $table->string('table_name');
            $table->unsignedBigInteger('record_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // ✅ not cascade
            $table->timestamps(); // Keep timestamps for consistency

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
