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
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('author_id')->nullable()->after('topic_id')->constrained('users')->nullOnDelete();
            $table->string('status_moderasi', 20)->default('approved')->after('versi')->index();
            $table->text('moderation_notes')->nullable()->after('status_moderasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->dropColumn(['author_id', 'status_moderasi', 'moderation_notes']);
        });
    }
};
