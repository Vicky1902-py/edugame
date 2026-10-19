<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cache permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat Peran (Roles)
        $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $adminSekolahRole = Role::firstOrCreate(['name' => 'admin_sekolah', 'guard_name' => 'web']);
        $guruRole = Role::firstOrCreate(['name' => 'guru', 'guard_name' => 'web']);
        $siswaRole = Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);

        // 2. Buat Izin Dasar (Permissions)
        $permissions = [
            'manage_platform',
            'manage_users',
            'manage_schools',
            'manage_questions',
            'manage_games',
            'manage_gamification',
            'manage_cms',
            'view_audit_logs',
            'manage_settings',
            'teach_class',
            'play_game',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Assign permissions ke role
        $guruRole->syncPermissions(['teach_class']);
        $siswaRole->syncPermissions(['play_game']);
        $adminSekolahRole->syncPermissions(['teach_class']); // struktur siap, nonaktif di MVP

        // Superadmin memiliki seluruh permissions
        $superadminRole->syncPermissions(Permission::all());

        // 3. Pastikan Akun Superadmin Awal Terdaftar & Memiliki Role superadmin
        $superadmin = User::firstOrCreate(
            ['email' => 'admin@edugame.id'],
            [
                'name' => 'Super Administrator EduGame',
                'role' => 'admin',
                'status' => 'active',
                'password' => Hash::make('admin12345'),
            ]
        );

        $superadmin->update(['status' => 'active', 'role' => 'admin']);
        $superadmin->assignRole('superadmin');
    }
}
