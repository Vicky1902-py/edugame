<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsInitialContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = User::first();
        $authorId = $superadmin ? $superadmin->id : 1;

        // 1. Halaman Statis Publik (Pages)
        $pages = [
            [
                'slug' => 'beranda',
                'title' => 'EduGame Indonesia - Belajar Lebih Seru Lewat Petualangan Game Edukasi',
                'content_html' => '<h2>Selamat Datang di EduGame Indonesia</h2><p>EduGame Indonesia adalah platform pembelajaran berbasis permainan yang dirancang selaras dengan Kurikulum Nasional untuk siswa SD dan SMP.</p>',
                'meta_title' => 'EduGame Indonesia | Game Edukasi Matematika & Sains',
                'meta_description' => 'Platform gaming edukasi interaktif untuk siswa dan guru sekolah di Indonesia.',
                'is_published' => true,
                'published_at' => now(),
            ],
            [
                'slug' => 'tentang-kami',
                'title' => 'Tentang EduGame Indonesia',
                'content_html' => '<h2>Misi Kami</h2><p>Mendukung transformasi digital pendidikan Indonesia dengan menghadirkan metode belajar yang memotivasi, menyenangkan, dan ramah privasi anak.</p>',
                'meta_title' => 'Tentang Kami - EduGame Indonesia',
                'meta_description' => 'Mengenal visi dan misi EduGame Indonesia dalam mendemokratisasi akses game edukasi berkualitas.',
                'is_published' => true,
                'published_at' => now(),
            ],
            [
                'slug' => 'kebijakan-privasi',
                'title' => 'Kebijakan Privasi & Kepatuhan UU PDP',
                'content_html' => '<h2>Komitmen Pelindungan Data Anak</h2><p>EduGame Indonesia mematuhi ketentuan Undang-Undang Pelindungan Data Pribadi (UU PDP). Kami tidak mengumpulkan email atau data kontak pribadi dari akun siswa.</p>',
                'meta_title' => 'Kebijakan Privasi - EduGame Indonesia',
                'meta_description' => 'Kebijakan privasi dan standar keamanan data siswa di EduGame Indonesia.',
                'is_published' => true,
                'published_at' => now(),
            ],
            [
                'slug' => 'syarat-ketentuan',
                'title' => 'Syarat dan Ketentuan Layanan',
                'content_html' => '<h2>Ketentuan Penggunaan</h2><p>Dengan menggunakan platform EduGame, sekolah, guru, dan siswa menyetujui pedoman penggunaan yang menjunjung integritas akademik dan keselamatan digital anak.</p>',
                'meta_title' => 'Syarat & Ketentuan - EduGame Indonesia',
                'meta_description' => 'Syarat dan ketentuan penggunaan platform EduGame Indonesia.',
                'is_published' => true,
                'published_at' => now(),
            ],
            [
                'slug' => 'faq',
                'title' => 'Pertanyaan yang Sering Diajukan (FAQ)',
                'content_html' => '<h2>Bagaimana cara siswa bergabung?</h2><p>Siswa cukup memasukkan Kode Kelas yang dibagikan oleh guru dan PIN 4-digit mereka tanpa perlu mendaftar email.</p>',
                'meta_title' => 'FAQ - EduGame Indonesia',
                'meta_description' => 'Pertanyaan umum mengenai penggunaan EduGame untuk guru dan siswa.',
                'is_published' => true,
                'published_at' => now(),
            ],
        ];

        foreach ($pages as $page) {
            DB::table('pages')->updateOrInsert(
                ['slug' => $page['slug']],
                array_merge($page, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // 2. Kategori Blog (Post Categories)
        $categories = [
            ['name' => 'Kabar & Pembaruan', 'slug' => 'kabar-pembaruan', 'description' => 'Pembaruan fitur game dan platform.'],
            ['name' => 'Tips Guru', 'slug' => 'tips-guru', 'description' => 'Panduan gamifikasi dan pengajaran kreatif.'],
            ['name' => 'Prestasi Sekolah', 'slug' => 'prestasi-sekolah', 'description' => 'Kisah inspiratif sekolah mitra EduGame.'],
        ];

        foreach ($categories as $cat) {
            DB::table('post_categories')->updateOrInsert(
                ['slug' => $cat['slug']],
                array_merge($cat, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $catId = DB::table('post_categories')->where('slug', 'kabar-pembaruan')->value('id');

        // 3. Post Contoh
        DB::table('posts')->updateOrInsert(
            ['slug' => 'peluncuran-modul-balapan-perkalian'],
            [
                'category_id' => $catId,
                'author_id' => $authorId,
                'title' => 'Peluncuran Modul Game Balapan Perkalian untuk SD',
                'summary' => 'Siswa SD kini dapat mengasah kelancaran hitung perkalian sambil memacu mobil balap virtual!',
                'content_html' => '<p>EduGame resmi merilis game Balapan Perkalian dengan sistem anti-cheat validasi server dan Web Audio sintetis.</p>',
                'is_published' => true,
                'published_at' => now(),
                'views_count' => 120,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 4. Menu Navigasi
        $headerMenuId = DB::table('menus')->updateOrInsert(
            ['location' => 'header'],
            ['name' => 'Menu Utama Header', 'created_at' => now(), 'updated_at' => now()]
        );
        $headerMenu = DB::table('menus')->where('location', 'header')->first();

        if ($headerMenu) {
            $headerItems = [
                ['title' => 'Beranda', 'url' => '/', 'sort_order' => 1],
                ['title' => 'Tentang Kami', 'url' => '/tentang-kami', 'sort_order' => 2],
                ['title' => 'FAQ', 'url' => '/faq', 'sort_order' => 3],
                ['title' => 'Portal Guru', 'url' => '/login-guru', 'sort_order' => 4],
            ];

            foreach ($headerItems as $item) {
                DB::table('menu_items')->updateOrInsert(
                    ['menu_id' => $headerMenu->id, 'url' => $item['url']],
                    array_merge($item, ['menu_id' => $headerMenu->id, 'created_at' => now(), 'updated_at' => now()])
                );
            }
        }

        // 5. Testimoni Awal
        DB::table('testimonials')->updateOrInsert(
            ['author_name' => 'Ibu Ratna Dewi, S.Pd'],
            [
                'author_title' => 'Guru Kelas 5 SDN Menteng 01',
                'quote' => 'Siswa saya yang tadinya takut matematika perkalian sekarang antusias berlomba mencapai finish setiap hari.',
                'rating' => 5,
                'is_featured' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
