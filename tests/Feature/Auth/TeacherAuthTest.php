<?php

namespace Tests\Feature\Auth;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login-guru');

        $response->assertStatus(200);
    }

    public function test_teacher_register_screen_can_be_rendered(): void
    {
        $response = $this->get('/daftar-guru');

        $response->assertStatus(200);
    }

    public function test_teacher_can_register_with_valid_details(): void
    {
        $response = $this->post('/daftar-guru', [
            'name' => 'Ibu Guru Ani',
            'school_name' => 'SMP Negeri 2 Merdeka',
            'email' => 'ani@edugame.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/guru/dashboard');
        $this->assertAuthenticated();

        $teacher = User::where('email', 'ani@edugame.id')->first();
        $this->assertNotNull($teacher);
        $this->assertEquals('guru', $teacher->role);

        $this->assertDatabaseHas('schools', [
            'nama' => 'SMP Negeri 2 Merdeka',
        ]);
    }

    public function test_teacher_can_login_with_correct_credentials(): void
    {
        $school = School::create(['nama' => 'SD 1']);
        $teacher = User::create([
            'school_id' => $school->id,
            'name' => 'Pak Joko',
            'email' => 'joko@edugame.id',
            'password' => Hash::make('secretpass'),
            'role' => 'guru',
        ]);

        $response = $this->post('/login-guru', [
            'email' => 'joko@edugame.id',
            'password' => 'secretpass',
        ]);

        $response->assertRedirect('/guru/dashboard');
        $this->assertAuthenticatedAs($teacher);
    }

    public function test_teacher_cannot_login_with_incorrect_password(): void
    {
        $school = School::create(['nama' => 'SD 1']);
        User::create([
            'school_id' => $school->id,
            'name' => 'Pak Joko',
            'email' => 'joko@edugame.id',
            'password' => Hash::make('secretpass'),
            'role' => 'guru',
        ]);

        $response = $this->post('/login-guru', [
            'email' => 'joko@edugame.id',
            'password' => 'passwordsalah',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_teacher_can_logout(): void
    {
        $school = School::create(['nama' => 'SD 1']);
        $teacher = User::create([
            'school_id' => $school->id,
            'name' => 'Pak Joko',
            'email' => 'joko@edugame.id',
            'password' => Hash::make('secretpass'),
            'role' => 'guru',
        ]);

        $response = $this->actingAs($teacher)->post('/guru/logout');

        $response->assertRedirect('/login-guru');
        $this->assertGuest();
    }
}
