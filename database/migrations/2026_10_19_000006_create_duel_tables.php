<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duel_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 4)->unique()->index();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('created_by_student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_teacher_session')->default(false);
            $table->enum('status', ['waiting', 'in_progress', 'completed', 'expired'])->default('waiting');
            $table->integer('current_question_index')->default(0);
            $table->json('question_ids');
            $table->timestamp('question_started_at')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->index(['code', 'class_id', 'status'], 'idx_duel_rooms_lookup');
        });

        Schema::create('duel_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('duel_rooms')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->integer('score')->default(0);
            $table->integer('streak')->default(0);
            $table->timestamp('last_active_at')->nullable();
            $table->boolean('is_connected')->default(true);
            $table->timestamp('joined_at');
            $table->timestamps();

            $table->unique(['room_id', 'student_id']);
        });

        Schema::create('duel_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('duel_rooms')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['room_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duel_events');
        Schema::dropIfExists('duel_players');
        Schema::dropIfExists('duel_rooms');
    }
};
