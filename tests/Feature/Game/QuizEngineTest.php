<?php

namespace Tests\Feature\Game;

use App\Models\Enrollment;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\Question;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use App\Models\XpLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class QuizEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected SchoolClass $class;
    protected Game $game;
    protected Topic $topic;
    protected Question $q1;
    protected Question $q2;

    protected function setUp(): void
    {
        parent::setUp();

        $school = School::create([
            'nama' => 'SD Negeri Juara',
            'jenjang' => 'SD',
        ]);

        $teacher = User::create([
            'school_id' => $school->id,
            'name' => 'Ibu Guru Ani',
            'email' => 'ani@edugame.id',
            'password' => Hash::make('password123'),
            'role' => 'guru',
        ]);

        $this->class = SchoolClass::create([
            'school_id' => $school->id,
            'guru_id' => $teacher->id,
            'nama' => 'Kelas 4A',
            'jenjang' => 'SD',
            'kode_gabung' => 'JUARA4',
            'leaderboard_visible' => true,
        ]);

        $this->student = User::create([
            'school_id' => $school->id,
            'name' => 'Budi Santoso',
            'username' => 'JUARA4_budi',
            'pin_hash' => Hash::make('1234'),
            'role' => 'siswa',
        ]);

        Enrollment::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
        ]);

        $this->game = Game::create([
            'nama' => 'Balapan Perkalian',
            'kode_slug' => 'balapan_perkalian',
            'deskripsi' => 'Adu cepat perkalian di lintasan 3 jalur',
            'jenjang' => 'SD',
            'aktif' => true,
        ]);

        $subject = Subject::create([
            'nama' => 'Matematika SD',
            'jenjang' => 'SD',
        ]);

        $this->topic = Topic::create([
            'subject_id' => $subject->id,
            'nama' => 'Perkalian 2-5',
        ]);

        $this->q1 = Question::create([
            'topic_id' => $this->topic->id,
            'tipe' => 'pilihan_ganda',
            'teks' => 'Berapa 4 x 5?',
            'opsi_json' => ['A' => '15', 'B' => '20', 'C' => '25', 'D' => '30'],
            'jawaban' => 'B',
            'penjelasan' => '4 x 5 = 20 karena 5 + 5 + 5 + 5 = 20',
            'kesulitan' => 'mudah',
            'versi' => 1,
        ]);

        $this->q2 = Question::create([
            'topic_id' => $this->topic->id,
            'tipe' => 'isian_singkat',
            'teks' => 'Berapa 3 x 7?',
            'opsi_json' => null,
            'jawaban' => '21',
            'penjelasan' => '3 x 7 = 21',
            'kesulitan' => 'sedang',
            'versi' => 1,
        ]);
    }

    /**
     * Uji 1: Siswa dapat memulai sesi game dan kunci jawaban TIDAK bocor ke klien.
     */
    public function test_student_can_start_game_session_and_payload_is_sanitized(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson('/api/game/session/start', [
                'game_slug' => 'balapan_perkalian',
                'level' => 'tabel_2_5',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'session_id',
                    'game_name',
                    'level',
                    'time_per_question',
                    'initial_lives',
                    'total_questions',
                    'questions' => [
                        '*' => ['id', 'teks', 'tipe', 'opsi', 'kesulitan']
                    ],
                ],
            ]);

        // Verifikasi bahwa kunci jawaban ('jawaban') dan 'penjelasan' TIDAK ADA pada data soal yang dikirim ke klien
        $questions = $response->json('data.questions');
        $this->assertNotEmpty($questions);
        foreach ($questions as $q) {
            $this->assertArrayNotHasKey('jawaban', $q, 'Kunci jawaban tidak boleh dikirim ke klien!');
            $this->assertArrayNotHasKey('penjelasan', $q, 'Penjelasan tidak boleh bocor sebelum dijawab!');
        }
    }

    /**
     * Uji 2 (KRITERIA UTAMA): Skor TIDAK BISA dimanipulasi dari klien.
     * Klien mencoba menyuntikkan skor palsu 9999 pada jawaban salah.
     */
    public function test_score_cannot_be_manipulated_by_client_proof(): void
    {
        // 1. Buat sesi game
        $session = GameSession::create([
            'student_id' => $this->student->id,
            'game_id' => $this->game->id,
            'class_id' => $this->class->id,
            'mulai' => now(),
            'skor' => 0,
            'akurasi' => 0,
            'total_benar' => 0,
            'total_soal' => 0,
        ]);

        // 2. Klien mencoba mengirim jawaban SALAH dan mencoba menyisipkan payload skor manipulatif (skor: 9999)
        $response = $this->actingAs($this->student)
            ->postJson('/api/game/session/submit-answer', [
                'session_id' => $session->id,
                'question_id' => $this->q1->id,
                'jawaban_siswa' => 'A', // Jawaban salah (kunci: B)
                'waktu_jawab_ms' => 2000,
                'skor' => 9999, // Parameter manipulatif dari klien
                'poin_didapat' => 9999,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.benar', false)
            ->assertJsonPath('data.poin_didapat', 0)
            ->assertJsonPath('data.total_skor', 0);

        // 3. Verifikasi integritas di database: skor sesi tetap 0, bukan 9999
        $session->refresh();
        $this->assertEquals(0, $session->skor, 'Skor di database tidak boleh terpengaruh input klien!');
        $this->assertEquals(0, $session->total_benar);
        $this->assertEquals(1, $session->total_soal);

        $this->assertDatabaseHas('answers', [
            'session_id' => $session->id,
            'question_id' => $this->q1->id,
            'benar' => false,
            'poin_didapat' => 0,
        ]);
    }

    /**
     * Uji 3: Perhitungan bonus kecepatan dan bonus streak dihitung di server.
     */
    public function test_speed_bonus_and_streak_bonus_are_calculated_correctly_on_server(): void
    {
        $session = GameSession::create([
            'student_id' => $this->student->id,
            'game_id' => $this->game->id,
            'class_id' => $this->class->id,
            'mulai' => now(),
            'skor' => 0,
            'akurasi' => 0,
            'total_benar' => 0,
            'total_soal' => 0,
        ]);

        // Jawaban 1: Benar dalam 3000 ms (sisa 12.000 ms dari 15.000 ms = bonus kecepatan: round(12000/15000 * 50) = 40 poin)
        // Skor: 100 dasar + 40 kecepatan + 0 streak = 140 poin
        $res1 = $this->actingAs($this->student)
            ->postJson('/api/game/session/submit-answer', [
                'session_id' => $session->id,
                'question_id' => $this->q1->id,
                'jawaban_siswa' => 'B',
                'waktu_jawab_ms' => 3000,
            ]);

        $res1->assertOk()
            ->assertJsonPath('data.benar', true)
            ->assertJsonPath('data.skor_dasar', 100)
            ->assertJsonPath('data.bonus_kecepatan', 40)
            ->assertJsonPath('data.bonus_streak', 0)
            ->assertJsonPath('data.poin_didapat', 140)
            ->assertJsonPath('data.streak', 1)
            ->assertJsonPath('data.total_skor', 140);

        // Jawaban 2: Benar dalam 0 ms (bonus kecepatan maks: 50 poin, streak ke-2: +10 poin)
        // Skor: 100 dasar + 50 kecepatan + 10 streak = 160 poin
        // Total sesi: 140 + 160 = 300 poin
        $res2 = $this->actingAs($this->student)
            ->postJson('/api/game/session/submit-answer', [
                'session_id' => $session->id,
                'question_id' => $this->q2->id,
                'jawaban_siswa' => '21',
                'waktu_jawab_ms' => 0,
            ]);

        $res2->assertOk()
            ->assertJsonPath('data.benar', true)
            ->assertJsonPath('data.skor_dasar', 100)
            ->assertJsonPath('data.bonus_kecepatan', 50)
            ->assertJsonPath('data.bonus_streak', 10)
            ->assertJsonPath('data.poin_didapat', 160)
            ->assertJsonPath('data.streak', 2)
            ->assertJsonPath('data.total_skor', 300);

        $session->refresh();
        $this->assertEquals(300, $session->skor);
        $this->assertEquals(2, $session->total_benar);
    }

    /**
     * Uji 4: Selesaikan sesi, peroleh hadiah XP, pembaruan progres topik, dan daftar evaluasi soal salah.
     */
    public function test_finish_session_awards_xp_updates_progress_and_returns_incorrect_reviews(): void
    {
        $session = GameSession::create([
            'student_id' => $this->student->id,
            'game_id' => $this->game->id,
            'class_id' => $this->class->id,
            'mulai' => now(),
            'skor' => 0,
            'akurasi' => 0,
            'total_benar' => 0,
            'total_soal' => 0,
        ]);

        // Jawab q1 benar
        $this->actingAs($this->student)->postJson('/api/game/session/submit-answer', [
            'session_id' => $session->id,
            'question_id' => $this->q1->id,
            'jawaban_siswa' => 'B',
            'waktu_jawab_ms' => 5000,
        ]);

        // Jawab q2 salah
        $this->actingAs($this->student)->postJson('/api/game/session/submit-answer', [
            'session_id' => $session->id,
            'question_id' => $this->q2->id,
            'jawaban_siswa' => '99',
            'waktu_jawab_ms' => 8000,
        ]);

        // Selesaikan sesi
        $response = $this->actingAs($this->student)
            ->postJson('/api/game/session/finish', [
                'session_id' => $session->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_soal', 2)
            ->assertJsonPath('data.total_benar', 1)
            ->assertJsonPath('data.total_salah', 1);

        $this->assertEquals(50, $response->json('data.akurasi'));

        // Verifikasi XP tercatat di xp_ledger
        $this->assertDatabaseHas('xp_ledger', [
            'user_id' => $this->student->id,
            'sumber' => "game_session:{$session->id}",
        ]);

        // Verifikasi daftar soal yang salah dikembalikan dengan kunci dan pembahasan lengkap
        $wrongReviews = $response->json('data.soal_salah');
        $this->assertCount(1, $wrongReviews);
        $this->assertEquals($this->q2->id, $wrongReviews[0]['question_id']);
        $this->assertEquals('99', $wrongReviews[0]['jawaban_siswa']);
        $this->assertEquals('21', $wrongReviews[0]['kunci_jawaban']);
        $this->assertEquals('3 x 7 = 21', $wrongReviews[0]['penjelasan']);
    }

    /**
     * Uji 5: Tamu tanpa autentikasi tidak dapat mengakses game atau API sesi.
     */
    public function test_guest_cannot_access_game_or_session_api(): void
    {
        $this->get('/siswa/game/balapan-perkalian')
            ->assertRedirect(route('student.login'));

        $this->postJson('/api/game/session/start', [
            'game_slug' => 'balapan_perkalian',
        ])->assertUnauthorized();
    }
}
