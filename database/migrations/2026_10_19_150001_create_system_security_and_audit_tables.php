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
        // 1. Log Login Pengguna
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email_attempted')->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('status', 20)->default('success'); // success, failed, locked
            $table->string('failure_reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 2. Log Impersonasi
        Schema::create('impersonation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('impersonator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('impersonated_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        // 3. Pengaturan Sistem Global (Key-Value)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('group', 50)->default('general')->index();
            $table->boolean('autoload')->default(true)->index();
            $table->timestamps();
        });

        // 4. Feature Flags Modul
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('feature_key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->json('rules_json')->nullable();
            $table->timestamps();
        });

        // 5. XP Ledger: Tambahkan kolom audit Superadmin jika diperlukan
        if (!Schema::hasColumn('xp_ledger', 'causer_id')) {
            Schema::table('xp_ledger', function (Blueprint $table) {
                $table->foreignId('causer_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
                $table->integer('balance_after')->default(0)->after('jumlah_xp');
                $table->string('reason')->nullable()->after('sumber');
            });
        }

        // 6. Data Requests (Kepatuhan UU Pelindungan Data Pribadi - PDP)
        Schema::create('data_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('request_type', 20); // export, delete
            $table->string('status', 20)->default('pending')->index(); // pending, processing, completed, rejected
            $table->timestamp('deadline_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 7. Word Blacklist (Moderasi kata terlarang nickname anak)
        Schema::create('word_blacklists', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
            $table->string('severity', 20)->default('block'); // block, warning
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 8. User Reports (Antrean laporan pelanggaran/kecurangan)
        Schema::create('user_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('report_type'); // cheating, bad_nickname, inappropriate_content
            $table->text('description')->nullable();
            $table->string('status', 20)->default('open')->index(); // open, investigating, resolved, dismissed
            $table->text('admin_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reports');
        Schema::dropIfExists('word_blacklists');
        Schema::dropIfExists('data_requests');
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('impersonation_logs');
        Schema::dropIfExists('login_logs');
    }
};
