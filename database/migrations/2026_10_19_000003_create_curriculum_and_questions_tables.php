<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('jenjang', ['SD', 'SMP', 'SMA', 'SMK'])->default('SD');
            $table->timestamps();
        });

        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('nama');
            $table->text('capaian_pembelajaran')->nullable();
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('topics')->cascadeOnDelete();
            $table->enum('tipe', ['pilihan_ganda', 'isian_singkat', 'benar_salah']);
            $table->text('teks');
            $table->json('opsi_json')->nullable();
            $table->text('jawaban');
            $table->text('penjelasan');
            $table->enum('kesulitan', ['mudah', 'sedang', 'sulit'])->default('sedang');
            $table->unsignedInteger('versi')->default(1);
            $table->timestamps();

            $table->index(['topic_id', 'kesulitan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
        Schema::dropIfExists('topics');
        Schema::dropIfExists('subjects');
    }
};
