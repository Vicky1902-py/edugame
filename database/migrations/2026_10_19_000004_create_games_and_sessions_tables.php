<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('kode_slug')->unique();
            $table->string('nama');
            $table->enum('jenjang', ['SD', 'SMP', 'SMA', 'SMK'])->default('SD');
            $table->json('konfigurasi_json')->nullable();
            $table->timestamps();
        });

        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->json('topic_ids');
            $table->timestamp('tenggat')->nullable();
            $table->timestamps();
        });

        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->timestamp('mulai');
            $table->timestamp('selesai')->nullable();
            $table->integer('skor')->default(0);
            $table->decimal('akurasi', 5, 2)->default(0);
            $table->integer('total_benar')->default(0);
            $table->integer('total_soal')->default(0);
            $table->timestamps();

            $table->index(['student_id', 'created_at']);
            $table->index(['class_id', 'created_at']);
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('game_sessions')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->text('jawaban_siswa');
            $table->boolean('benar');
            $table->integer('waktu_ms');
            $table->integer('poin_didapat')->default(0);
            $table->timestamps();

            $table->index(['question_id', 'benar'], 'idx_answers_question_benar');
            $table->index('session_id', 'idx_answers_session');
            $table->index('created_at', 'idx_answers_created_at');
        });

        Schema::create('progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained('topics')->cascadeOnDelete();
            $table->decimal('tingkat_penguasaan', 5, 2)->default(0);
            $table->timestamp('terakhir_main')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'topic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress');
        Schema::dropIfExists('answers');
        Schema::dropIfExists('game_sessions');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('games');
    }
};
