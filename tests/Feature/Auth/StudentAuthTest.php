<?php

namespace Tests\Feature\Auth;

use App\Models\Enrollment;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentAuthTest extends TestCase
{
    use RefreshDatabase;

    protected SchoolClass $class;

    protected function setUp(): void
    {
        parent::setUp();

        $school = School::create([
            'nama' => 'SD Negeri 1 Merdeka Nusantara',
            'jenjang' => 'SD',
        ]);

        $teacher = User::create([
            'school_id' => $school->id,
            'name' => 'Pak Guru Budi',
            'email' => 'guru@edugame.id',
            'password' => Hash::make('password123'),
            'role' => 'guru',
        ]);

        $this->class = SchoolClass::create([
            'school_id' => $school->id,
            'guru_id' => $teacher->id,
            'nama' => 'Kelas 5-A',
            'jenjang' => 'SD',
            'kode_gabung' => 'JUARA5',
            'leaderboard_visible' => true,
        ]);
    }

    public function test_student_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/masuk-siswa');

        $response->assertStatus(200);
    }

    public function test_student_can_render_dashboard_page(): void
    {
        $student = User::create([
            'school_id' => $this->class->school_id,
            'name' => 'Budi Dashboard',
            'username' => 'budi_dash',
            'pin_hash' => Hash::make('1234'),
            'role' => 'siswa',
        ]);

        Enrollment::create([
            'class_id' => $this->class->id,
            'student_id' => $student->id,
        ]);

        $response = $this->actingAs($student)->get('/siswa/dashboard');

        $response->assertStatus(200);
    }

    public function test_new_student_can_login_and_be_registered_to_class(): void
    {
        $response = $this->post('/masuk-siswa', [
            'kode_kelas' => 'JUARA5',
            'nama_panggilan' => 'Budi Baru',
            'pin' => '1234',
        ]);

        $response->assertRedirect('/siswa/dashboard');
        $this->assertAuthenticated();

        $student = User::where('name', 'Budi Baru')->where('role', 'siswa')->first();
        $this->assertNotNull($student);
        $this->assertTrue(Hash::check('1234', $student->pin_hash));

        $this->assertDatabaseHas('enrollments', [
            'class_id' => $this->class->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_existing_student_can_login_with_correct_pin(): void
    {
        $student = User::create([
            'school_id' => $this->class->school_id,
            'name' => 'Siti',
            'username' => 'siti_juara5',
            'pin_hash' => Hash::make('4321'),
            'role' => 'siswa',
        ]);

        Enrollment::create([
            'class_id' => $this->class->id,
            'student_id' => $student->id,
        ]);

        $response = $this->post('/masuk-siswa', [
            'kode_kelas' => 'JUARA5',
            'nama_panggilan' => 'Siti',
            'pin' => '4321',
        ]);

        $response->assertRedirect('/siswa/dashboard');
        $this->assertAuthenticatedAs($student);
    }

    public function test_existing_student_fails_login_with_incorrect_pin(): void
    {
        $student = User::create([
            'school_id' => $this->class->school_id,
            'name' => 'Siti',
            'username' => 'siti_juara5',
            'pin_hash' => Hash::make('4321'),
            'role' => 'siswa',
        ]);

        Enrollment::create([
            'class_id' => $this->class->id,
            'student_id' => $student->id,
        ]);

        $response = $this->post('/masuk-siswa', [
            'kode_kelas' => 'JUARA5',
            'nama_panggilan' => 'Siti',
            'pin' => '9999',
        ]);

        $response->assertSessionHasErrors('pin');
        $this->assertGuest();
    }

    public function test_student_cannot_login_with_invalid_class_code(): void
    {
        $response = $this->post('/masuk-siswa', [
            'kode_kelas' => 'SALAH9',
            'nama_panggilan' => 'Budi',
            'pin' => '1234',
        ]);

        $response->assertSessionHasErrors('kode_kelas');
        $this->assertGuest();
    }

    public function test_student_nickname_with_bad_words_is_rejected(): void
    {
        $response = $this->post('/masuk-siswa', [
            'kode_kelas' => 'JUARA5',
            'nama_panggilan' => 'Si Anjing Pintar',
            'pin' => '1234',
        ]);

        $response->assertSessionHasErrors('nama_panggilan');
        $this->assertGuest();
    }

    public function test_student_pin_must_be_exactly_four_digits(): void
    {
        $response = $this->post('/masuk-siswa', [
            'kode_kelas' => 'JUARA5',
            'nama_panggilan' => 'Budi',
            'pin' => '12', // Kurang dari 4 digit
        ]);

        $response->assertSessionHasErrors('pin');
        $this->assertGuest();
    }

    public function test_student_can_logout(): void
    {
        $student = User::create([
            'school_id' => $this->class->school_id,
            'name' => 'Budi',
            'username' => 'budi_123',
            'pin_hash' => Hash::make('1234'),
            'role' => 'siswa',
        ]);

        $response = $this->actingAs($student)->post('/siswa/logout');

        $response->assertRedirect('/masuk-siswa');
        $this->assertGuest();
    }
}
