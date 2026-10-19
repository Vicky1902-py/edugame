<?php

namespace Tests\Feature\Admin;

use App\Models\LoginLog;
use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperadminM2bTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'superadmin']);
        Role::firstOrCreate(['name' => 'guru']);
        Role::firstOrCreate(['name' => 'siswa']);
    }

    public function test_superadmin_bypasses_all_permission_checks_via_gate_before(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $superadmin->assignRole('superadmin');

        $guru = User::factory()->create([
            'role' => 'guru',
            'status' => 'active',
        ]);
        $guru->assignRole('guru');

        $this->assertTrue(Gate::forUser($superadmin)->allows('edit_any_restricted_setting'));
        $this->assertTrue(Gate::forUser($superadmin)->allows('delete_financial_ledger'));

        $this->assertFalse(Gate::forUser($guru)->allows('edit_any_restricted_setting'));
        $this->assertFalse(Gate::forUser($guru)->allows('delete_financial_ledger'));
    }

    public function test_active_superadmin_can_access_filament_admin_panel(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $superadmin->assignRole('superadmin');

        $response = $this->actingAs($superadmin)->get('/admin');
        $response->assertSuccessful();
    }

    public function test_teacher_and_student_are_forbidden_from_filament_admin_panel(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'status' => 'active',
        ]);

        $siswa = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
        ]);

        $this->actingAs($guru)->get('/admin')->assertForbidden();
        $this->actingAs($siswa)->get('/admin')->assertForbidden();
    }

    public function test_suspended_superadmin_is_forbidden_from_admin_panel(): void
    {
        $suspendedAdmin = User::factory()->create([
            'role' => 'admin',
            'status' => 'suspended',
        ]);
        $suspendedAdmin->assignRole('superadmin');

        $this->actingAs($suspendedAdmin)->get('/admin')->assertForbidden();
    }

    public function test_last_superadmin_cannot_be_deleted(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $superadmin->assignRole('superadmin');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Superadmin terakhir tidak dapat dihapus');

        $superadmin->delete();
    }

    public function test_last_superadmin_cannot_be_downgraded(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $superadmin->assignRole('superadmin');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Peran Superadmin terakhir tidak dapat diturunkan');

        $superadmin->update(['role' => 'guru']);
    }

    public function test_reset_two_factor_artisan_command_resets_totp_fields(): void
    {
        $admin = User::factory()->create([
            'email' => 'superadmin2fa@edugame.id',
            'role' => 'admin',
            'two_factor_secret' => 'SECRETKEY123456',
            'two_factor_recovery_codes' => json_encode(['CODE1', 'CODE2']),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->artisan('app:reset-2fa', ['email' => 'superadmin2fa@edugame.id'])
            ->expectsOutputToContain('berhasil diatur ulang')
            ->assertSuccessful();

        $admin->refresh();
        $this->assertNull($admin->two_factor_secret);
        $this->assertNull($admin->two_factor_recovery_codes);
        $this->assertNull($admin->two_factor_confirmed_at);
    }

    public function test_authentication_events_are_recorded_in_login_logs(): void
    {
        $user = User::factory()->create([
            'email' => 'logintest@edugame.id',
            'password' => Hash::make('secret123'),
        ]);

        // 1. Simulasikan login event
        event(new Login('web', $user, false));

        $this->assertDatabaseHas('login_logs', [
            'user_id' => $user->id,
            'email_attempted' => 'logintest@edugame.id',
            'status' => 'success',
        ]);

        // 2. Simulasikan failed login event
        event(new Failed('web', null, ['email' => 'hacker@edugame.id', 'password' => 'wrong']));

        $this->assertDatabaseHas('login_logs', [
            'email_attempted' => 'hacker@edugame.id',
            'status' => 'failed',
        ]);
    }
}
