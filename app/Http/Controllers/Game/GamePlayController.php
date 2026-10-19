<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Http\Requests\Game\SubmitAnswerRequest;
use App\Models\Enrollment;
use App\Models\Game;
use App\Models\XpLedger;
use App\Services\Game\QuizEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class GamePlayController extends Controller
{
    public function __construct(
        protected QuizEngineService $engine
    ) {}

    /**
     * Tampilkan halaman game Balapan Perkalian.
     */
    public function mathRacing(Request $request): Response
    {
        $student = Auth::user();

        // Hitung total XP dan level
        $totalXp = (int) XpLedger::where('user_id', $student->id)->sum('jumlah_xp');
        $level = max(1, (int) floor($totalXp / 150) + 1);

        $levels = [
            [
                'id' => 'tabel_2_5',
                'title' => 'Tabel Perkalian 2 - 5',
                'description' => 'Cocok untuk pemanasan dan pemantapan dasar perkalian.',
                'badge' => 'Pemula',
                'color' => 'emerald',
            ],
            [
                'id' => 'tabel_6_9',
                'title' => 'Tabel Perkalian 6 - 9',
                'description' => 'Tantangan perkalian angka besar yang sering menjebak.',
                'badge' => 'Menengah',
                'color' => 'blue',
            ],
            [
                'id' => 'pembagian',
                'title' => 'Pembagian Cepat',
                'description' => 'Latih kebalikan perkalian dengan pembagian bilangan bulat.',
                'badge' => 'Taktis',
                'color' => 'purple',
            ],
            [
                'id' => 'campuran',
                'title' => 'Campuran Kilat (Perkalian & Pembagian)',
                'description' => 'Adu kecepatan dan ketelitian tertinggi untuk sang juara!',
                'badge' => 'Master',
                'color' => 'amber',
            ],
        ];

        return Inertia::render('Student/Games/MathRacing', [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'username' => $student->username,
                'level' => $level,
                'xp' => $totalXp,
            ],
            'levels' => $levels,
        ]);
    }

    /**
     * Memulai sesi permainan baru dan menyiapkan soal tanpa membocorkan kunci jawaban.
     */
    public function startSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'game_slug' => ['nullable', 'string'],
            'level' => ['nullable', 'string'],
        ]);

        $gameSlug = $validated['game_slug'] ?? 'balapan_perkalian';
        $level = $validated['level'] ?? 'campuran';

        $sessionData = $this->engine->startSession($request->user(), $gameSlug, $level);

        return response()->json([
            'success' => true,
            'data' => $sessionData,
        ]);
    }

    /**
     * Validasi jawaban siswa dan hitung skor di server.
     */
    public function submitAnswer(SubmitAnswerRequest $request): JsonResponse
    {
        $result = $this->engine->validateAndRecordAnswer(
            (int) $request->session_id,
            $request->user()->id,
            (int) $request->question_id,
            (string) $request->jawaban_siswa,
            (int) $request->waktu_jawab_ms
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Selesaikan sesi permainan dan berikan XP serta evaluasi soal yang salah.
     */
    public function finishSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'integer', 'exists:game_sessions,id'],
        ]);

        $summary = $this->engine->finishSession((int) $validated['session_id'], $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
