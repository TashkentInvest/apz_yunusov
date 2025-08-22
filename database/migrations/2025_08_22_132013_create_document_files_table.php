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
       Schema::create('document_files', function (Blueprint $table) {
            $table->id();
            $table->string('related_table', 50); // objects, contracts, etc.
            $table->unsignedBigInteger('related_id');
            $table->string('file_type', 50); // 'project_docs', 'permit', 'art', etc.

            $table->string('original_name', 300);
            $table->string('file_path', 500);
            $table->bigInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();

            $table->timestamp('uploaded_at')->useCurrent();
            $table->unsignedBigInteger('uploaded_by')->nullable();

            $table->index(['related_table', 'related_id']);
            $table->index('file_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_files');
    }
};
