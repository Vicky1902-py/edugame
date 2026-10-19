<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperadminModulesAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'superadmin']);
        Role::firstOrCreate(['name' => 'guru']);
        Role::firstOrCreate(['name' => 'siswa']);

        $this->superadmin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $this->superadmin->assignRole('superadmin');
    }

    public function test_superadmin_can_access_all_fourteen_modules(): void
    {
        $endpoints = [
            '/admin',                         // Modul 1: Dashboard Platform
            '/admin/users',                   // Modul 2: Pengguna
            '/admin/schools',                 // Modul 3: Sekolah
            '/admin/school-classes',          // Modul 3: Kelas
            '/admin/questions',               // Modul 4: Bank Soal
            '/admin/subjects',                // Modul 4: Mapel
            '/admin/topics',                  // Modul 4: Topik
            '/admin/games',                   // Modul 5: Manajer Game
            '/admin/badges',                  // Modul 6: Gamifikasi Lencana
            '/admin/xp-ledgers',              // Modul 6: Buku Besar XP
            '/admin/duel-rooms',              // Modul 7: Pemantau Duel
            '/admin/pages',                   // Modul 8: CMS Halaman Statis
            '/admin/post-categories',         // Modul 8: CMS Kategori
            '/admin/posts',                   // Modul 8: CMS Berita
            '/admin/banners',                 // Modul 8: CMS Banner
            '/admin/menus',                   // Modul 8: CMS Menu
            '/admin/contact-messages',        // Modul 8: CMS Kontak Masuk
            '/admin/testimonials',            // Modul 8: CMS Testimoni
            '/admin/announcements',           // Modul 9: Notifikasi & Siaran
            '/admin/global-reports',          // Modul 10: Laporan Global
            '/admin/system-settings',         // Modul 11: Pengaturan Sistem
            '/admin/activity-logs',           // Modul 12: Log Audit
            '/admin/login-logs',              // Modul 12: Log Login
            '/admin/data-requests',           // Modul 13: Privasi Data UU PDP
            '/admin/word-blacklists',         // Modul 14: Kata Terlarang
            '/admin/user-reports',            // Modul 14: Moderasi Laporan
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->actingAs($this->superadmin)->get($endpoint);
            $response->assertSuccessful("Superadmin gagal mengakses modul pada endpoint: {$endpoint}");
        }
    }

    public function test_non_superadmin_cannot_access_any_filament_module(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'status' => 'active',
        ]);
        $guru->assignRole('guru');

        $this->actingAs($guru)->get('/admin/system-settings')->assertForbidden();
        $this->actingAs($guru)->get('/admin/users')->assertForbidden();
        $this->actingAs($guru)->get('/admin/activity-logs')->assertForbidden();
    }
}
