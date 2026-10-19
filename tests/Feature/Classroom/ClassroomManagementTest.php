<?php

namespace Tests\Feature\Classroom;

use App\Models\Enrollment;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClassroomManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;
    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'nama' => 'SMP Negeri 1 Merdeka',
            'jenjang' => 'SMP',
        ]);

        $this->teacher = User::create([
            'school_id' => $this->school->id,
            'name' => 'Ibu Guru Rini',
            'email' => 'rini@edugame.id',
            'password' => Hash::make('password123'),
            'role' => 'guru',
        ]);
    }

    public function test_teacher_can_view_classes_list(): void
    {
        SchoolClass::create([
            'school_id' => $this->school->id,
            'guru_id' => $this->teacher->id,
            'nama' => 'Kelas 7-B',
            'jenjang' => 'SMP',
            'kode_gabung' => 'SMP7B1',
            'leaderboard_visible' => true,
        ]);

        $response = $this->actingAs($this->teacher)->get('/guru/kelas');

        $response->assertStatus(200);
    }

    public function test_teacher_can_create_a_classroom_with_auto_generated_code(): void
    {
        $response = $this->actingAs($this->teacher)->post('/guru/kelas', [
            'nama' => 'Kelas 8-C',
            'jenjang' => 'SMP',
            'leaderboard_visible' => true,
        ]);

        $class = SchoolClass::where('nama', 'Kelas 8-C')->first();
        $this->assertNotNull($class);
        $this->assertEquals(6, strlen($class->kode_gabung));
        $this->assertEquals($this->teacher->id, $class->guru_id);

        // Pastikan karakter kode tidak mengandung O, 0, I, 1
        $this->assertDoesNotMatchRegularExpression('/[O0I1]/', $class->kode_gabung);

        $response->assertRedirect("/guru/kelas/{$class->id}");
    }

    public function test_teacher_can_view_class_details_and_enrolled_students(): void
    {
        $class = SchoolClass::create([
            'school_id' => $this->school->id,
            'guru_id' => $this->teacher->id,
            'nama' => 'Kelas 9-A',
            'jenjang' => 'SMP',
            'kode_gabung' => 'SMP9A1',
            'leaderboard_visible' => true,
        ]);

        $student = User::create([
            'school_id' => $this->school->id,
            'name' => 'Bintang',
            'username' => 'bintang_smp',
            'pin_hash' => Hash::make('1111'),
            'role' => 'siswa',
        ]);

        Enrollment::create([
            'class_id' => $class->id,
            'student_id' => $student->id,
        ]);

        $response = $this->actingAs($this->teacher)->get("/guru/kelas/{$class->id}");

        $response->assertStatus(200);
    }

    public function test_teacher_can_update_classroom_settings(): void
    {
        $class = SchoolClass::create([
            'school_id' => $this->school->id,
            'guru_id' => $this->teacher->id,
            'nama' => 'Kelas Awal',
            'jenjang' => 'SMP',
            'kode_gabung' => 'AWAL99',
            'leaderboard_visible' => true,
        ]);

        $response = $this->actingAs($this->teacher)->put("/guru/kelas/{$class->id}", [
            'nama' => 'Kelas Diperbarui',
            'jenjang' => 'SMP',
            'leaderboard_visible' => false,
        ]);

        $response->assertSessionHasNoErrors();
        $class->refresh();

        $this->assertEquals('Kelas Diperbarui', $class->nama);
        $this->assertFalse((bool) $class->leaderboard_visible);
    }

    public function test_teacher_can_delete_classroom(): void
    {
        $class = SchoolClass::create([
            'school_id' => $this->school->id,
            'guru_id' => $this->teacher->id,
            'nama' => 'Kelas Hapus',
            'jenjang' => 'SMP',
            'kode_gabung' => 'HAPUS1',
            'leaderboard_visible' => true,
        ]);

        $response = $this->actingAs($this->teacher)->delete("/guru/kelas/{$class->id}");

        $response->assertRedirect('/guru/kelas');
        $this->assertDatabaseMissing('classes', ['id' => $class->id]);
    }

    public function test_student_cannot_access_teacher_classroom_pages(): void
    {
        $student = User::create([
            'school_id' => $this->school->id,
            'name' => 'Siswa Test',
            'username' => 'siswa_test',
            'pin_hash' => Hash::make('1234'),
            'role' => 'siswa',
        ]);

        $response = $this->actingAs($student)->get('/guru/kelas');

        $response->assertRedirect('/siswa/dashboard');
    }
}
