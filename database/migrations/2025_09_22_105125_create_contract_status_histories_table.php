<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contract_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->foreignId('old_status_id')->constrained('contract_statuses');
            $table->foreignId('new_status_id')->constrained('contract_statuses');
            $table->foreignId('changed_by')->constrained('users');
            $table->text('reason')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();

            // Indexes for performance
            $table->index('contract_id');
            $table->index('changed_at');
            $table->index('changed_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contract_status_histories');
    }
};
