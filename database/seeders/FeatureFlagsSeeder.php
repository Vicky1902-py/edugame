<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureFlagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                'feature_key' => 'game_balapan_perkalian',
                'name' => 'Game Balapan Perkalian (SD)',
                'description' => 'Mengaktifkan akses modul permainan balapan perkalian matematika untuk jenjang SD.',
                'is_enabled' => true,
            ],
            [
                'feature_key' => 'game_kuis_duel',
                'name' => 'Kuis Duel Real-Time (SMP)',
                'description' => 'Mengaktifkan ruang pertempuran kuis multiplayer duel untuk siswa SMP.',
                'is_enabled' => true,
            ],
            [
                'feature_key' => 'student_registration',
                'name' => 'Pendaftaran Siswa Baru Mandiri',
                'description' => 'Izinkan siswa mendaftar dengan kode kelas tanpa persetujuan manual guru terlebih dahulu.',
                'is_enabled' => true,
            ],
            [
                'feature_key' => 'public_leaderboard',
                'name' => 'Papan Skor Publik',
                'description' => 'Menampilkan daftar 10 peringkat skor tertinggi kelas kepada seluruh siswa.',
                'is_enabled' => true,
            ],
            [
                'feature_key' => 'impersonation_feature',
                'name' => 'Fitur Impersonasi Superadmin',
                'description' => 'Mengizinkan superadmin masuk sementara sebagai akun guru atau siswa untuk troubleshooting.',
                'is_enabled' => true,
            ],
        ];

        foreach ($features as $f) {
            DB::table('feature_flags')->updateOrInsert(
                ['feature_key' => $f['feature_key']],
                [
                    'name' => $f['name'],
                    'description' => $f['description'],
                    'is_enabled' => $f['is_enabled'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
