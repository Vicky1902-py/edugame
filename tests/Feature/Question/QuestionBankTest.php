<?php

namespace Tests\Feature\Question;

use App\Models\Question;
use App\Models\School;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class QuestionBankTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;
    protected Subject $subject;
    protected Topic $topic;

    protected function setUp(): void
    {
        parent::setUp();

        $school = School::create([
            'nama' => 'SD Negeri 1 Merdeka',
            'jenjang' => 'SD',
        ]);

        $this->teacher = User::create([
            'school_id' => $school->id,
            'name' => 'Pak Guru Matematika',
            'email' => 'guru@edugame.id',
            'password' => Hash::make('password123'),
            'role' => 'guru',
        ]);

        $this->subject = Subject::create([
            'nama' => 'Matematika SD',
            'jenjang' => 'SD',
        ]);

        $this->topic = Topic::create([
            'subject_id' => $this->subject->id,
            'nama' => 'Perkalian Dasar',
        ]);
    }

    public function test_teacher_can_view_question_bank_page(): void
    {
        Question::create([
            'topic_id' => $this->topic->id,
            'tipe' => 'pilihan_ganda',
            'teks' => 'Berapa 5 x 5?',
            'opsi_json' => ['A' => '20', 'B' => '25', 'C' => '30', 'D' => '35'],
            'jawaban' => 'B',
            'penjelasan' => '5 x 5 = 25',
            'kesulitan' => 'mudah',
        ]);

        $response = $this->actingAs($this->teacher)->get('/guru/soal');

        $response->assertStatus(200);
    }

    public function test_teacher_can_create_multiple_choice_question(): void
    {
        $response = $this->actingAs($this->teacher)->post('/guru/soal', [
            'topic_id' => $this->topic->id,
            'tipe' => 'pilihan_ganda',
            'teks' => 'Berapa 9 x 9?',
            'opsi' => ['A' => '72', 'B' => '81', 'C' => '90', 'D' => '99'],
            'jawaban' => 'B',
            'penjelasan' => '9 dikali 9 sama dengan 81.',
            'kesulitan' => 'sedang',
        ]);

        $response->assertRedirect('/guru/soal');

        $question = Question::where('teks', 'Berapa 9 x 9?')->first();
        $this->assertNotNull($question);
        $this->assertEquals('pilihan_ganda', $question->tipe);
        $this->assertEquals('B', $question->jawaban);
        $this->assertEquals('81', $question->opsi_json['B']);
        $this->assertEquals(1, $question->versi);
    }

    public function test_teacher_can_create_true_false_question(): void
    {
        $response = $this->actingAs($this->teacher)->post('/guru/soal', [
            'topic_id' => $this->topic->id,
            'tipe' => 'benar_salah',
            'teks' => 'Angka 7 adalah bilangan prima.',
            'jawaban' => 'Benar',
            'penjelasan' => '7 hanya memiliki dua faktor, yaitu 1 dan 7.',
            'kesulitan' => 'mudah',
        ]);

        $response->assertRedirect('/guru/soal');

        $question = Question::where('teks', 'Angka 7 adalah bilangan prima.')->first();
        $this->assertNotNull($question);
        $this->assertEquals('benar_salah', $question->tipe);
        $this->assertEquals('Benar', $question->jawaban);
        $this->assertEquals(['Benar', 'Salah'], $question->opsi_json);
    }

    public function test_teacher_can_create_fill_in_the_blank_question(): void
    {
        $response = $this->actingAs($this->teacher)->post('/guru/soal', [
            'topic_id' => $this->topic->id,
            'tipe' => 'isian_singkat',
            'teks' => 'Hasil dari 12 dibagi 3 adalah...',
            'jawaban' => '4',
            'penjelasan' => '12 : 3 = 4',
            'kesulitan' => 'mudah',
        ]);

        $response->assertRedirect('/guru/soal');

        $question = Question::where('teks', 'Hasil dari 12 dibagi 3 adalah...')->first();
        $this->assertNotNull($question);
        $this->assertEquals('isian_singkat', $question->tipe);
        $this->assertEquals('4', $question->jawaban);
        $this->assertNull($question->opsi_json);
    }

    public function test_teacher_can_update_existing_question_and_version_increments(): void
    {
        $question = Question::create([
            'topic_id' => $this->topic->id,
            'tipe' => 'isian_singkat',
            'teks' => 'Teks Awal Soal',
            'jawaban' => 'Jawaban Awal',
            'penjelasan' => 'Penjelasan awal',
            'kesulitan' => 'mudah',
            'versi' => 1,
        ]);

        $response = $this->actingAs($this->teacher)->put("/guru/soal/{$question->id}", [
            'topic_id' => $this->topic->id,
            'tipe' => 'isian_singkat',
            'teks' => 'Teks Soal yang Telah Diperbarui',
            'jawaban' => 'Jawaban Baru',
            'penjelasan' => 'Penjelasan baru',
            'kesulitan' => 'sedang',
        ]);

        $response->assertRedirect('/guru/soal');

        $question->refresh();
        $this->assertEquals('Teks Soal yang Telah Diperbarui', $question->teks);
        $this->assertEquals('Jawaban Baru', $question->jawaban);
        $this->assertEquals(2, $question->versi);
    }

    public function test_teacher_can_delete_question(): void
    {
        $question = Question::create([
            'topic_id' => $this->topic->id,
            'tipe' => 'isian_singkat',
            'teks' => 'Soal untuk Dihapus',
            'jawaban' => 'Hapus',
            'penjelasan' => 'Penjelasan',
            'kesulitan' => 'mudah',
        ]);

        $response = $this->actingAs($this->teacher)->delete("/guru/soal/{$question->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
    }

    public function test_teacher_can_download_csv_template(): void
    {
        $response = $this->actingAs($this->teacher)->get('/guru/soal/template-csv');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_teacher_can_import_questions_from_valid_csv(): void
    {
        $csvContent = "tipe,teks,opsi_a,opsi_b,opsi_c,opsi_d,kunci_jawaban,kesulitan,penjelasan,topik\n"
                    . "pilihan_ganda,Berapa 6 x 7?,40,42,44,48,B,sedang,6 x 7 = 42,Perkalian Dasar\n"
                    . "benar_salah,Air membeku pada suhu 0 derajat celcius,,,,,Benar,mudah,Titik beku air adalah 0 C,Sains Fisika\n"
                    . "isian_singkat,Lambang kimia dari oksigen adalah...,,,,,O,mudah,Oksigen dilambangkan O,Kimia Dasar\n";

        $file = UploadedFile::fake()->createWithContent('soal.csv', $csvContent);

        $response = $this->actingAs($this->teacher)->post('/guru/soal/import-csv', [
            'file_csv' => $file,
            'default_topic_id' => $this->topic->id,
        ]);

        $response->assertRedirect('/guru/soal');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('questions', ['teks' => 'Berapa 6 x 7?']);
        $this->assertDatabaseHas('questions', ['teks' => 'Air membeku pada suhu 0 derajat celcius']);
        $this->assertDatabaseHas('questions', ['teks' => 'Lambang kimia dari oksigen adalah...']);
    }

    public function test_invalid_csv_row_does_not_break_import_of_valid_rows(): void
    {
        $csvContent = "tipe,teks,opsi_a,opsi_b,opsi_c,opsi_d,kunci_jawaban,kesulitan,penjelasan,topik\n"
                    . "pilihan_ganda,,opsi1,opsi2,,,B,sedang,,Perkalian Dasar\n"
                    . "isian_singkat,Ibu kota Jawa Tengah adalah...,,,,,Semarang,mudah,,Geografi\n";

        $file = UploadedFile::fake()->createWithContent('soal_campuran.csv', $csvContent);

        $response = $this->actingAs($this->teacher)->post('/guru/soal/import-csv', [
            'file_csv' => $file,
            'default_topic_id' => $this->topic->id,
        ]);

        $response->assertRedirect('/guru/soal');
        $this->assertDatabaseHas('questions', ['teks' => 'Ibu kota Jawa Tengah adalah...']);
    }

    public function test_student_cannot_access_question_bank(): void
    {
        $student = User::create([
            'school_id' => $this->teacher->school_id,
            'name' => 'Siswa Murid',
            'username' => 'siswa_murid',
            'pin_hash' => Hash::make('1234'),
            'role' => 'siswa',
        ]);

        $response = $this->actingAs($student)->get('/guru/soal');

        $response->assertRedirect('/siswa/dashboard');
    }
}
