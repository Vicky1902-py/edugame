<?php

namespace App\Services\Game;

use App\Models\Answer;
use App\Models\Enrollment;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\Progress;
use App\Models\Question;
use App\Models\Topic;
use App\Models\User;
use App\Models\XpLedger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizEngineService
{
    /**
     * Mulai sesi permainan baru dan siapkan soal tanpa membocorkan kunci jawaban.
     *
     * @throws ValidationException
     */
    public function startSession(User $student, string $gameSlug, ?string $level = null): array
    {
        $game = Game::where('kode_slug', $gameSlug)->first();
        if (!$game) {
            throw ValidationException::withMessages([
                'game' => ['Permainan tidak ditemukan.'],
            ]);
        }

        // Ambil kelas aktif siswa
        $activeClassId = session('active_class_id');
        if (!$activeClassId) {
            $enrollment = Enrollment::where('student_id', $student->id)->latest()->first();
            $activeClassId = $enrollment?->class_id;
        }

        // 1. Buat sesi game baru
        $session = GameSession::create([
            'student_id' => $student->id,
            'game_id' => $game->id,
            'class_id' => $activeClassId,
            'mulai' => now(),
            'skor' => 0,
            'akurasi' => 0,
            'total_benar' => 0,
            'total_soal' => 0,
        ]);

        // 2. Ambil soal sesuai level permainan
        $questions = $this->fetchQuestionsForLevel($gameSlug, $level, 10);

        // 3. Sanitasi soal (HAPUS kunci jawaban dan pembahasan agar klien tidak bisa mencurangi)
        $clientQuestions = $questions->map(function ($q) {
            return [
                'id' => $q->id,
                'teks' => $q->teks,
                'tipe' => $q->tipe,
                'opsi' => $q->opsi_json,
                'kesulitan' => $q->kesulitan,
            ];
        });

        return [
            'session_id' => $session->id,
            'game_name' => $game->nama,
            'level' => $level ?? 'standar',
            'time_per_question' => 15, // 15 detik per soal
            'initial_lives' => 3,       // 3 nyawa
            'total_questions' => $clientQuestions->count(),
            'questions' => $clientQuestions,
        ];
    }

    /**
     * Ambil soal berdasarkan level permainan.
     */
    protected function fetchQuestionsForLevel(string $gameSlug, ?string $level, int $limit = 10): Collection
    {
        $query = Question::query();

        if ($gameSlug === 'balapan_perkalian') {
            // Filter berdasarkan topik atau kata kunci level
            switch ($level) {
                case 'tabel_2_5':
                    $query->where(function ($q) {
                        $q->where('teks', 'like', '%x 2%')
                          ->orWhere('teks', 'like', '%x 3%')
                          ->orWhere('teks', 'like', '%x 4%')
                          ->orWhere('teks', 'like', '%x 5%')
                          ->orWhereHas('topic', fn ($t) => $t->where('nama', 'like', '%2-5%'));
                    });
                    break;
                case 'tabel_6_9':
                    $query->where(function ($q) {
                        $q->where('teks', 'like', '%x 6%')
                          ->orWhere('teks', 'like', '%x 7%')
                          ->orWhere('teks', 'like', '%x 8%')
                          ->orWhere('teks', 'like', '%x 9%')
                          ->orWhereHas('topic', fn ($t) => $t->where('nama', 'like', '%6-9%'));
                    });
                    break;
                case 'pembagian':
                    $query->where(function ($q) {
                        $q->where('teks', 'like', '%bagi%')
                          ->orWhere('teks', 'like', '%:%')
                          ->orWhereHas('topic', fn ($t) => $t->where('nama', 'like', '%pembagian%'));
                    });
                    break;
                case 'campuran':
                default:
                    // Perkalian dan pembagian campuran
                    $query->where(function ($q) {
                        $q->where('teks', 'like', '%x%')
                          ->orWhere('teks', 'like', '%:%')
                          ->orWhereHas('topic', fn ($t) => $t->where('nama', 'like', '%perkalian%'));
                    });
                    break;
            }
        }

        $results = $query->inRandomOrder()->limit($limit)->get();

        // Fallback jika soal spesifik kurang dari limit: ambil soal acak yang tersedia
        if ($results->count() < $limit) {
            $fallback = Question::whereNotIn('id', $results->pluck('id'))
                ->inRandomOrder()
                ->limit($limit - $results->count())
                ->get();
            $results = $results->merge($fallback);
        }

        return $results;
    }

    /**
     * Validasi jawaban siswa dan hitung skor di server.
     * Klien TIDAK BOLEH menentukan skor.
     *
     * @throws ValidationException
     */
    public function validateAndRecordAnswer(
        int $sessionId,
        int $studentId,
        int $questionId,
        string $studentAnswer,
        int $timeMs
    ): array {
        $session = GameSession::where('id', $sessionId)
            ->where('student_id', $studentId)
            ->first();

        if (!$session) {
            throw ValidationException::withMessages([
                'session' => ['Sesi permainan tidak valid.'],
            ]);
        }

        if ($session->selesai !== null) {
            throw ValidationException::withMessages([
                'session' => ['Sesi permainan ini telah selesai.'],
            ]);
        }

        $question = Question::findOrFail($questionId);

        // 1. Evaluasi kebenaran jawaban
        $isCorrect = $this->checkAnswerCorrectness($question, $studentAnswer);

        // 2. Hitung streak sebelumnya dalam sesi ini
        $previousAnswers = Answer::where('session_id', $sessionId)
            ->orderBy('id', 'desc')
            ->get();

        $currentStreak = 0;
        if ($isCorrect) {
            $currentStreak = 1;
            foreach ($previousAnswers as $prev) {
                if ($prev->benar) {
                    $currentStreak++;
                } else {
                    break;
                }
            }
        }

        // 3. Perhitungan Skor
        $baseScore = $isCorrect ? 100 : 0;
        $speedBonus = 0;
        $streakBonus = 0;

        if ($isCorrect) {
            // Bonus kecepatan: makin cepat dari 15.000 ms, makin besar poinnya (maks 50 poin)
            $clampedTime = max(0, min(15000, $timeMs));
            $speedBonus = (int) round(((15000 - $clampedTime) / 15000) * 50);

            // Bonus streak: +10 poin per streak berturut-turut (maks 50 poin)
            $streakBonus = min(50, ($currentStreak - 1) * 10);
        }

        $pointsEarned = $baseScore + $speedBonus + $streakBonus;

        // 4. Simpan ke tabel answers (Sumber utama analitik)
        Answer::create([
            'session_id' => $sessionId,
            'question_id' => $questionId,
            'jawaban_siswa' => $studentAnswer,
            'benar' => $isCorrect,
            'waktu_ms' => $timeMs,
            'poin_didapat' => $pointsEarned,
        ]);

        // 5. Perbarui agregat di game_sessions
        $session->increment('skor', $pointsEarned);
        $session->increment('total_soal', 1);
        if ($isCorrect) {
            $session->increment('total_benar', 1);
        }

        return [
            'benar' => $isCorrect,
            'kunci_jawaban' => $question->jawaban,
            'penjelasan' => $question->penjelasan,
            'poin_didapat' => $pointsEarned,
            'skor_dasar' => $baseScore,
            'bonus_kecepatan' => $speedBonus,
            'bonus_streak' => $streakBonus,
            'streak' => $currentStreak,
            'total_skor' => (int) $session->fresh()->skor,
        ];
    }

    /**
     * Periksa apakah jawaban siswa cocok dengan kunci jawaban.
     */
    protected function checkAnswerCorrectness(Question $question, string $studentAnswer): bool
    {
        $cleanStudent = trim(strtolower($studentAnswer));
        $cleanKey = trim(strtolower($question->jawaban));

        if ($question->tipe === 'pilihan_ganda') {
            // Cocokkan huruf opsi (A, B, C, D)
            if (strtoupper($cleanStudent) === strtoupper($cleanKey)) {
                return true;
            }

            // Atau cocokkan teks opsi jika siswa mengirim teksnya
            if (is_array($question->opsi_json)) {
                $expectedKey = strtoupper($question->jawaban);
                $expectedText = trim(strtolower($question->opsi_json[$expectedKey] ?? ''));
                if ($expectedText !== '' && $cleanStudent === $expectedText) {
                    return true;
                }
            }

            return false;
        }

        if ($question->tipe === 'benar_salah') {
            $normalizedStudent = in_array($cleanStudent, ['benar', 'true', 'b', '1']) ? 'benar' : 'salah';
            $normalizedKey = in_array($cleanKey, ['benar', 'true', 'b', '1']) ? 'benar' : 'salah';
            return $normalizedStudent === $normalizedKey;
        }

        // isian_singkat
        return $cleanStudent === $cleanKey;
    }

    /**
     * Selesaikan sesi permainan, hitung statistik final, beri XP, dan siapkan evaluasi soal salah.
     *
     * @throws ValidationException
     */
    public function finishSession(int $sessionId, int $studentId): array
    {
        $session = GameSession::where('id', $sessionId)
            ->where('student_id', $studentId)
            ->with(['answers.question.topic', 'game'])
            ->first();

        if (!$session) {
            throw ValidationException::withMessages([
                'session' => ['Sesi permainan tidak ditemukan.'],
            ]);
        }

        // Hitung statistik final dari tabel answers
        $answers = $session->answers;
        $totalQuestions = $answers->count();
        $totalCorrect = $answers->where('benar', true)->count();
        $accuracy = $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100, 1) : 0;
        $finalScore = (int) $answers->sum('poin_didapat');

        // Tandai sesi selesai
        $session->update([
            'selesai' => now(),
            'skor' => $finalScore,
            'total_soal' => $totalQuestions,
            'total_benar' => $totalCorrect,
            'akurasi' => $accuracy,
        ]);

        // Hitung reward XP (Minimal 20 XP, dihitung dari skor / 10 + bonus akurasi)
        $accuracyBonusXp = $accuracy >= 90 ? 50 : ($accuracy >= 70 ? 25 : 10);
        $xpEarned = max(20, (int) round($finalScore / 10) + $accuracyBonusXp);

        // Catat ke ledger XP
        XpLedger::create([
            'user_id' => $studentId,
            'jumlah_xp' => $xpEarned,
            'sumber' => "game_session:{$sessionId}",
        ]);

        // Perbarui tingkat penguasaan topik (Progress)
        $topicGroups = $answers->groupBy('question.topic_id');
        foreach ($topicGroups as $topicId => $topicAnswers) {
            if (!$topicId) continue;
            $topicCorrect = $topicAnswers->where('benar', true)->count();
            $topicTotal = $topicAnswers->count();
            $masteryRate = round(($topicCorrect / $topicTotal) * 100, 1);

            $progress = Progress::firstOrNew([
                'student_id' => $studentId,
                'topic_id' => $topicId,
            ]);

            // Exponential moving average untuk mastery rate (70% lama, 30% baru)
            $newMastery = $progress->exists
                ? round(($progress->tingkat_penguasaan * 0.7) + ($masteryRate * 0.3), 1)
                : $masteryRate;

            $progress->tingkat_penguasaan = $newMastery;
            $progress->terakhir_main = now();
            $progress->save();
        }

        // Tentukan posisi podium balapan (Juara 1, 2, atau 3)
        $podiumRank = 3;
        if ($accuracy >= 80) {
            $podiumRank = 1;
        } elseif ($accuracy >= 60) {
            $podiumRank = 2;
        }

        // Kumpulkan daftar soal yang dijawab salah beserta pembahasan lengkapnya
        $incorrectReviews = $answers->where('benar', false)->map(function ($ans) {
            $q = $ans->question;
            return [
                'question_id' => $q->id,
                'teks' => $q->teks,
                'tipe' => $q->tipe,
                'opsi' => $q->opsi_json,
                'jawaban_siswa' => $ans->jawaban_siswa,
                'kunci_jawaban' => $q->jawaban,
                'penjelasan' => $q->penjelasan,
            ];
        })->values();

        return [
            'session_id' => $session->id,
            'game_name' => $session->game->nama,
            'skor_akhir' => $finalScore,
            'total_soal' => $totalQuestions,
            'total_benar' => $totalCorrect,
            'total_salah' => $totalQuestions - $totalCorrect,
            'akurasi' => $accuracy,
            'podium_rank' => $podiumRank,
            'xp_didapat' => $xpEarned,
            'soal_salah' => $incorrectReviews,
        ];
    }
}
