<?php

namespace Tests\Feature\Admin;

use App\Models\Game;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperadminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $teacher;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Super Administrator',
            'email' => 'admin@edugame.id',
            'role' => 'admin',
            'password' => Hash::make('password123'),
        ]);

        $this->teacher = User::factory()->create([
            'name' => 'Pak Guru Budi',
            'email' => 'budi@guru.sch.id',
            'role' => 'guru',
            'password' => Hash::make('password123'),
        ]);

        $this->student = User::factory()->create([
            'name' => 'Andi Pratama',
            'role' => 'siswa',
            'pin_hash' => Hash::make('1234'),
        ]);
    }

    public function test_non_admin_cannot_access_superadmin_dashboard(): void
    {
        // Guest
        $this->get('/admin/dashboard')->assertRedirect('/masuk-siswa');

        // Teacher
        $this->actingAs($this->teacher)
            ->get('/admin/dashboard')
            ->assertRedirect('/guru/dashboard');

        // Student
        $this->actingAs($this->student)
            ->get('/admin/dashboard')
            ->assertRedirect('/siswa/dashboard');
    }

    public function test_superadmin_can_access_dashboard_and_modules(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/dashboard')
            ->assertStatus(200);

        $this->actingAs($this->admin)
            ->get('/admin/sekolah')
            ->assertStatus(200);

        $this->actingAs($this->admin)
            ->get('/admin/pengguna')
            ->assertStatus(200);

        $this->actingAs($this->admin)
            ->get('/admin/game')
            ->assertStatus(200);
    }

    public function test_superadmin_can_create_and_manage_schools(): void
    {
        // 1. Create school
        $response = $this->actingAs($this->admin)->post('/admin/sekolah', [
            'nama' => 'SD Negeri Nusantara 01',
            'jenjang' => 'SD',
            'status_lisensi' => 'aktif',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('schools', [
            'nama' => 'SD Negeri Nusantara 01',
            'jenjang' => 'SD',
            'status_lisensi' => 'aktif',
        ]);

        $school = School::where('nama', 'SD Negeri Nusantara 01')->firstOrFail();

        // 2. Update school
        $updateResp = $this->actingAs($this->admin)->put("/admin/sekolah/{$school->id}", [
            'nama' => 'SD Negeri Nusantara 01 Unggulan',
            'jenjang' => 'SD',
            'status_lisensi' => 'aktif',
        ]);

        $updateResp->assertSessionHas('success');
        $this->assertDatabaseHas('schools', [
            'id' => $school->id,
            'nama' => 'SD Negeri Nusantara 01 Unggulan',
        ]);

        // 3. Delete school
        $delResp = $this->actingAs($this->admin)->delete("/admin/sekolah/{$school->id}");
        $delResp->assertSessionHas('success');
        $this->assertDatabaseMissing('schools', [
            'id' => $school->id,
        ]);
    }

    public function test_superadmin_can_create_new_teacher_and_admin_users(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/pengguna', [
            'name' => 'Siti Guru Baru',
            'email' => 'siti@sekolah.sch.id',
            'role' => 'guru',
            'password' => 'rahasia123',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'siti@sekolah.sch.id',
            'role' => 'guru',
        ]);
    }

    public function test_superadmin_can_reset_student_pin(): void
    {
        $response = $this->actingAs($this->admin)->post("/admin/pengguna/{$this->student->id}/reset-pin", [
            'new_pin' => '4321',
        ]);

        $response->assertSessionHas('success');
        $this->student->refresh();
        $this->assertTrue(Hash::check('4321', $this->student->pin_hash));
    }

    public function test_superadmin_can_reset_user_password(): void
    {
        $response = $this->actingAs($this->admin)->post("/admin/pengguna/{$this->teacher->id}/reset-password", [
            'new_password' => 'PassBaru2026!',
        ]);

        $response->assertSessionHas('success');
        $this->teacher->refresh();
        $this->assertTrue(Hash::check('PassBaru2026!', $this->teacher->password));
    }

    public function test_superadmin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->admin)->delete("/admin/pengguna/{$this->admin->id}");
        $response->assertSessionHasErrors(['delete']);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_superadmin_can_update_game_engine_configuration(): void
    {
        $game = Game::create([
            'kode_slug' => 'balapan_perkalian',
            'nama' => 'Balapan Perkalian SD',
            'jenjang' => 'SD',
            'konfigurasi_json' => [
                'nyawa' => 3,
                'timer_per_soal' => 15,
                'poin_dasar' => 100,
                'bonus_kecepatan_maks' => 50,
                'bonus_streak_maks' => 50,
            ],
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/game/{$game->id}", [
            'nama' => 'Balapan Perkalian Pro',
            'jenjang' => 'SD',
            'konfigurasi' => [
                'nyawa' => 5,
                'timer_per_soal' => 20,
                'poin_dasar' => 150,
                'bonus_kecepatan_maks' => 80,
                'bonus_streak_maks' => 60,
            ],
        ]);

        $response->assertSessionHas('success');
        $game->refresh();
        $this->assertEquals('Balapan Perkalian Pro', $game->nama);
        $this->assertEquals(5, $game->konfigurasi_json['nyawa']);
        $this->assertEquals(20, $game->konfigurasi_json['timer_per_soal']);
        $this->assertEquals(150, $game->konfigurasi_json['poin_dasar']);
    }

    public function test_make_superadmin_artisan_command(): void
    {
        $this->artisan('make:superadmin', [
            '--name' => 'Admin Baru',
            '--email' => 'adminbaru@edugame.id',
            '--password' => 'supersecret123',
        ])->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'adminbaru@edugame.id',
            'role' => 'admin',
        ]);
    }
}
