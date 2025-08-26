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
        Schema::create('kengash_hulosasi', function (Blueprint $table) {
            $table->id();
            $table->string('kengash_hulosa_raqami')->nullable();
            $table->date('kengash_hulosa_sanasi')->nullable();
            $table->string('apz_raqami')->nullable();
            $table->date('apz_berilgan_sanasi')->nullable();
            $table->string('buyurtmachi')->nullable();
            $table->string('buyurtmachi_stir_pinfl')->nullable();
            $table->string('buyurtmachi_telefon')->nullable();
            $table->enum('bino_turi', ['турар', 'нотурар'])->nullable();
            $table->text('muammo_turi')->nullable();
            $table->string('loyihachi')->nullable();
            $table->string('loyihachi_stir_pinfl')->nullable();
            $table->string('loyihachi_telefon')->nullable();
            $table->text('loyiha_smeta_nomi')->nullable();
            $table->string('tuman')->nullable();
            $table->text('manzil')->nullable();
            $table->enum('status', [
                'Тўловдан озод этилган',
                'Мажбурий тўлов'
            ])->default('Мажбурий тўлов');
            $table->text('ozod_sababi')->nullable(); // Reason for exemption
            $table->string('qurilish_turi')->nullable();
            $table->string('shartnoma_raqami')->nullable();
            $table->date('shartnoma_sanasi')->nullable();
            $table->decimal('shartnoma_qiymati', 20, 2)->nullable(); // Increased precision
            $table->decimal('fakt_tulov', 20, 2)->default(0);
            $table->decimal('qarzdarlik', 20, 2)->default(0);
            $table->string('tic_apz_id')->nullable();
            $table->unsignedBigInteger('creator_user_id')->nullable();
            $table->unsignedBigInteger('updater_user_id')->nullable();
            $table->timestamps();

            $table->index(['kengash_hulosa_raqami']);
            $table->index(['apz_raqami']);
            $table->index(['buyurtmachi_stir_pinfl']);
            $table->index(['status']);
            $table->index(['tuman']);
        });

        Schema::create('kengash_hulosasi_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kengash_hulosasi_id');
            $table->string('file_name');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->date('file_date')->nullable();
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();

            $table->foreign('kengash_hulosasi_id')->references('id')->on('kengash_hulosasi')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kengash_hulosasi_files');
        Schema::dropIfExists('kengash_hulosasi');
    }
};
