<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Enrollment;
use App\Models\Game;
use App\Models\Question;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Sekolah
        $school = School::create([
            'nama' => 'SD Negeri 1 Merdeka Nusantara',
            'jenjang' => 'SD',
            'status_lisensi' => 'aktif',
        ]);

        // 2. Guru
        $guru = User::create([
            'name' => 'Budi Hartono, S.Pd.',
            'email' => 'guru@edugame.id',
            'password' => Hash::make('password123'),
            'role' => 'guru',
            'school_id' => $school->id,
        ]);

        // Platform Admin & Roles
        $this->call([
            RolesAndPermissionsSeeder::class,
            DefaultSettingsSeeder::class,
            FeatureFlagsSeeder::class,
            WordBlacklistSeeder::class,
            CmsInitialContentSeeder::class,
        ]);

        // 3. Kelas
        $kelas = SchoolClass::create([
            'school_id' => $school->id,
            'guru_id' => $guru->id,
            'nama' => 'Kelas 5-A',
            'jenjang' => 'SD',
            'kode_gabung' => 'JUARA5',
            'leaderboard_visible' => true,
        ]);

        // 4. 5 Siswa
        $siswaList = [
            ['name' => 'Budi', 'pin' => '1234'],
            ['name' => 'Siti', 'pin' => '2345'],
            ['name' => 'Edo', 'pin' => '3456'],
            ['name' => 'Maya', 'pin' => '4567'],
            ['name' => 'Rizky', 'pin' => '5678'],
        ];

        foreach ($siswaList as $s) {
            $student = User::create([
                'name' => $s['name'],
                'role' => 'siswa',
                'pin_hash' => Hash::make($s['pin']),
                'school_id' => $school->id,
            ]);

            Enrollment::create([
                'class_id' => $kelas->id,
                'student_id' => $student->id,
                'terdaftar_pada' => now(),
            ]);
        }

        // 5. 2 Game Inti
        Game::create([
            'kode_slug' => 'balapan_perkalian',
            'nama' => 'Balapan Perkalian',
            'jenjang' => 'SD',
            'konfigurasi_json' => [
                'nyawa' => 3,
                'timer_per_soal' => 10,
                'jalur' => 3,
                'jumlah_bot' => 2,
                'poin_dasar' => 100,
                'bonus_kecepatan_maks' => 50,
                'bonus_streak_maks' => 50,
            ],
        ]);

        Game::create([
            'kode_slug' => 'kuis_duel',
            'nama' => 'Kuis Duel Real-Time',
            'jenjang' => 'SMP',
            'konfigurasi_json' => [
                'waktu_per_soal' => 15,
                'soal_per_ruang' => 10,
                'maks_pemain' => 4,
                'poin_dasar' => 100,
                'bonus_kecepatan_maks' => 100,
            ],
        ]);

        // 6. 6 Lencana (Badges)
        $badges = [
            [
                'kode' => 'pemula',
                'nama' => 'Langkah Pertama',
                'deskripsi' => 'Menyelesaikan permainan edukasi pertamamu.',
                'ikon_url' => '/assets/badges/pemula.svg',
                'syarat_json' => ['tipe' => 'sesi_pertama'],
            ],
            [
                'kode' => 'jago_hitung',
                'nama' => 'Jago Hitung',
                'deskripsi' => 'Menjawab 50 soal matematika dengan benar.',
                'ikon_url' => '/assets/badges/jago_hitung.svg',
                'syarat_json' => ['tipe' => 'jawaban_benar_total', 'jumlah' => 50],
            ],
            [
                'kode' => 'streak_7',
                'nama' => 'Disiplin Juara',
                'deskripsi' => 'Bermain dan belajar selama 7 hari berturut-turut.',
                'ikon_url' => '/assets/badges/streak_7.svg',
                'syarat_json' => ['tipe' => 'streak_hari', 'jumlah' => 7],
            ],
            [
                'kode' => 'juara_kelas',
                'nama' => 'Bintang Kelas',
                'deskripsi' => 'Mencapai posisi 3 besar di leaderboard kelas.',
                'ikon_url' => '/assets/badges/juara_kelas.svg',
                'syarat_json' => ['tipe' => 'top_leaderboard', 'maks_rank' => 3],
            ],
            [
                'kode' => 'si_cepat',
                'nama' => 'Si Cepat Kilat',
                'deskripsi' => 'Menjawab benar 10 soal dalam waktu di bawah 3 detik.',
                'ikon_url' => '/assets/badges/si_cepat.svg',
                'syarat_json' => ['tipe' => 'kecepatan_tinggi', 'detik' => 3, 'jumlah' => 10],
            ],
            [
                'kode' => 'pantang_menyerah',
                'nama' => 'Pantang Menyerah',
                'deskripsi' => 'Menyelesaikan game hingga garis akhir meski sisa 1 nyawa.',
                'ikon_url' => '/assets/badges/pantang_menyerah.svg',
                'syarat_json' => ['tipe' => 'selesai_nyawa_1'],
            ],
        ];

        foreach ($badges as $b) {
            Badge::create($b);
        }

        // 7. Mata Pelajaran & Topik
        $mapelMatematika = Subject::create([
            'nama' => 'Matematika SD',
            'jenjang' => 'SD',
        ]);

        $topikTabel25 = Topic::create([
            'subject_id' => $mapelMatematika->id,
            'nama' => 'Tabel Perkalian 2 - 5',
            'capaian_pembelajaran' => 'Memahami dan menghafal konsep perkalian dasar angka 2 sampai 5.',
        ]);

        $topikTabel69 = Topic::create([
            'subject_id' => $mapelMatematika->id,
            'nama' => 'Tabel Perkalian 6 - 9',
            'capaian_pembelajaran' => 'Menguasai perkalian lanjutan angka 6 sampai 9 secara cepat dan tepat.',
        ]);

        $topikCampuran = Topic::create([
            'subject_id' => $mapelMatematika->id,
            'nama' => 'Perkalian Campuran',
            'capaian_pembelajaran' => 'Menghitung perkalian acak dari 2 hingga 9 serta perkalian puluhan sederhana.',
        ]);

        $topikPembagian = Topic::create([
            'subject_id' => $mapelMatematika->id,
            'nama' => 'Pembagian Dasar',
            'capaian_pembelajaran' => 'Memahami pembagian sebagai kebalikan dari operasi perkalian.',
        ]);

        $mapelUmum = Subject::create([
            'nama' => 'Pengetahuan Umum & Sains',
            'jenjang' => 'SMP',
        ]);

        $topikSains = Topic::create([
            'subject_id' => $mapelUmum->id,
            'nama' => 'Tata Surya & IPA Dasar',
            'capaian_pembelajaran' => 'Mengenal planet-planet tata surya, hukum sains dasar, dan ekosistem alam.',
        ]);

        $topikSejarah = Topic::create([
            'subject_id' => $mapelUmum->id,
            'nama' => 'Sejarah & Budaya Nusantara',
            'capaian_pembelajaran' => 'Mengenal pahlawan nasional, peristiwa kemerdekaan, dan budaya Indonesia.',
        ]);

        // 8. 60 Soal Perkalian & Pembagian (15 per topik)
        $this->seedMultiplicationQuestions($topikTabel25->id, $topikTabel69->id, $topikCampuran->id, $topikPembagian->id);

        // 9. 30 Soal Umum (15 Sains + 15 Sejarah)
        $this->seedGeneralQuestions($topikSains->id, $topikSejarah->id);
    }

    private function seedMultiplicationQuestions(int $tabel25Id, int $tabel69Id, int $campuranId, int $pembagianId): void
    {
        // 15 Soal Tabel 2-5
        $soal25 = [
            ['2 x 3', '6', ['4', '6', '8', '9'], 'mudah', '2 dikali 3 sama dengan 2 + 2 + 2 = 6.'],
            ['3 x 4', '12', ['10', '12', '14', '16'], 'mudah', '3 dikali 4 sama dengan 12.'],
            ['4 x 5', '20', ['15', '20', '25', '30'], 'mudah', '4 dikali 5 adalah 20.'],
            ['5 x 5', '25', ['20', '25', '30', '35'], 'mudah', '5 dikali 5 sama dengan 25.'],
            ['2 x 7', '14', ['12', '14', '16', '18'], 'mudah', '2 dikali 7 adalah 14.'],
            ['3 x 8', '24', ['21', '24', '27', '30'], 'sedang', '3 dikali 8 adalah 24.'],
            ['4 x 6', '24', ['20', '22', '24', '28'], 'sedang', '4 dikali 6 sama dengan 24.'],
            ['5 x 8', '40', ['35', '40', '45', '50'], 'sedang', '5 dikali 8 adalah 40.'],
            ['3 x 9', '27', ['24', '27', '30', '33'], 'sedang', '3 dikali 9 adalah 27.'],
            ['4 x 8', '32', ['28', '30', '32', '36'], 'sedang', '4 dikali 8 sama dengan 32.'],
            ['5 x 9', '45', ['40', '45', '50', '55'], 'sedang', '5 dikali 9 adalah 45.'],
            ['2 x 9', '18', ['16', '18', '20', '22'], 'mudah', '2 dikali 9 adalah 18.'],
            ['3 x 7', '21', ['18', '21', '24', '27'], 'sedang', '3 dikali 7 sama dengan 21.'],
            ['4 x 7', '28', ['24', '26', '28', '32'], 'sedang', '4 dikali 7 adalah 28.'],
            ['5 x 7', '35', ['30', '35', '40', '45'], 'mudah', '5 dikali 7 adalah 35.'],
        ];

        foreach ($soal25 as $s) {
            Question::create([
                'topic_id' => $tabel25Id,
                'tipe' => 'pilihan_ganda',
                'teks' => "Berapakah hasil dari {$s[0]}?",
                'opsi_json' => $s[2],
                'jawaban' => $s[1],
                'penjelasan' => $s[4],
                'kesulitan' => $s[3],
                'versi' => 1,
            ]);
        }

        // 15 Soal Tabel 6-9
        $soal69 = [
            ['6 x 6', '36', ['30', '36', '42', '48'], 'sedang', '6 dikali 6 sama dengan 36.'],
            ['6 x 7', '42', ['40', '42', '44', '48'], 'sedang', '6 dikali 7 adalah 42.'],
            ['6 x 8', '48', ['46', '48', '54', '56'], 'sedang', '6 dikali 8 adalah 48.'],
            ['7 x 7', '49', ['42', '47', '49', '56'], 'sedang', '7 dikali 7 sama dengan 49.'],
            ['7 x 8', '56', ['54', '56', '63', '64'], 'sulit', '7 dikali 8 adalah 56.'],
            ['7 x 9', '63', ['56', '63', '65', '72'], 'sulit', '7 dikali 9 sama dengan 63.'],
            ['8 x 8', '64', ['56', '64', '72', '80'], 'sedang', '8 dikali 8 adalah 64.'],
            ['8 x 9', '72', ['70', '72', '74', '81'], 'sulit', '8 dikali 9 sama dengan 72.'],
            ['9 x 9', '81', ['72', '79', '81', '90'], 'sedang', '9 dikali 9 adalah 81.'],
            ['6 x 9', '54', ['48', '54', '56', '63'], 'sedang', '6 dikali 9 sama dengan 54.'],
            ['8 x 6', '48', ['42', '46', '48', '54'], 'sedang', '8 dikali 6 adalah 48.'],
            ['9 x 7', '63', ['56', '61', '63', '70'], 'sulit', '9 dikali 7 sama dengan 63.'],
            ['8 x 7', '56', ['52', '54', '56', '58'], 'sulit', '8 dikali 7 adalah 56.'],
            ['9 x 8', '72', ['68', '72', '76', '80'], 'sulit', '9 dikali 8 adalah 72.'],
            ['7 x 6', '42', ['36', '40', '42', '48'], 'sedang', '7 dikali 6 sama dengan 42.'],
        ];

        foreach ($soal69 as $s) {
            Question::create([
                'topic_id' => $tabel69Id,
                'tipe' => 'pilihan_ganda',
                'teks' => "Berapakah hasil dari {$s[0]}?",
                'opsi_json' => $s[2],
                'jawaban' => $s[1],
                'penjelasan' => $s[4],
                'kesulitan' => $s[3],
                'versi' => 1,
            ]);
        }

        // 15 Soal Perkalian Campuran
        $soalCampuran = [
            ['12 x 3', '36', ['32', '36', '40', '48'], 'sedang', '12 dikali 3 sama dengan (10 x 3) + (2 x 3) = 30 + 6 = 36.'],
            ['15 x 2', '30', ['25', '30', '35', '40'], 'mudah', '15 dikali 2 adalah 30.'],
            ['11 x 5', '55', ['50', '55', '60', '65'], 'mudah', '11 dikali 5 adalah 55.'],
            ['14 x 3', '42', ['38', '40', '42', '46'], 'sulit', '14 dikali 3 = 42.'],
            ['20 x 4', '80', ['60', '70', '80', '90'], 'mudah', '20 dikali 4 sama dengan 80.'],
            ['25 x 3', '75', ['70', '75', '80', '85'], 'sedang', '25 dikali 3 adalah 75.'],
            ['16 x 2', '32', ['30', '32', '34', '36'], 'mudah', '16 dikali 2 adalah 32.'],
            ['13 x 4', '52', ['48', '50', '52', '56'], 'sulit', '13 dikali 4 = (10 x 4) + (3 x 4) = 40 + 12 = 52.'],
            ['30 x 3', '90', ['80', '90', '100', '120'], 'mudah', '30 dikali 3 sama dengan 90.'],
            ['18 x 2', '36', ['32', '34', '36', '38'], 'mudah', '18 dikali 2 adalah 36.'],
            ['15 x 4', '60', ['50', '55', '60', '65'], 'sedang', '15 dikali 4 adalah 60.'],
            ['12 x 5', '60', ['55', '60', '65', '70'], 'sedang', '12 dikali 5 = 60.'],
            ['22 x 3', '66', ['60', '64', '66', '68'], 'sedang', '22 dikali 3 = 66.'],
            ['17 x 2', '34', ['32', '34', '36', '38'], 'sedang', '17 dikali 2 adalah 34.'],
            ['25 x 4', '100', ['90', '95', '100', '120'], 'mudah', '25 dikali 4 sama dengan 100.'],
        ];

        foreach ($soalCampuran as $s) {
            Question::create([
                'topic_id' => $campuranId,
                'tipe' => 'pilihan_ganda',
                'teks' => "Hitunglah hasil dari {$s[0]}:",
                'opsi_json' => $s[2],
                'jawaban' => $s[1],
                'penjelasan' => $s[4],
                'kesulitan' => $s[3],
                'versi' => 1,
            ]);
        }

        // 15 Soal Pembagian Dasar
        $soalBagi = [
            ['24 : 4', '6', ['4', '5', '6', '7'], 'mudah', '24 dibagi 4 sama dengan 6 karena 6 x 4 = 24.'],
            ['36 : 6', '6', ['5', '6', '7', '8'], 'mudah', '36 dibagi 6 adalah 6.'],
            ['45 : 5', '9', ['7', '8', '9', '10'], 'mudah', '45 dibagi 5 adalah 9.'],
            ['56 : 7', '8', ['6', '7', '8', '9'], 'sedang', '56 dibagi 7 sama dengan 8.'],
            ['63 : 9', '7', ['6', '7', '8', '9'], 'sedang', '63 dibagi 9 adalah 7.'],
            ['72 : 8', '9', ['7', '8', '9', '10'], 'sedang', '72 dibagi 8 adalah 9.'],
            ['81 : 9', '9', ['7', '8', '9', '10'], 'mudah', '81 dibagi 9 adalah 9.'],
            ['48 : 6', '8', ['6', '7', '8', '9'], 'sedang', '48 dibagi 6 sama dengan 8.'],
            ['42 : 7', '6', ['5', '6', '7', '8'], 'sedang', '42 dibagi 7 adalah 6.'],
            ['32 : 4', '8', ['6', '7', '8', '9'], 'mudah', '32 dibagi 4 adalah 8.'],
            ['54 : 6', '9', ['7', '8', '9', '10'], 'sedang', '54 dibagi 6 sama dengan 9.'],
            ['64 : 8', '8', ['6', '7', '8', '9'], 'sedang', '64 dibagi 8 adalah 8.'],
            ['35 : 5', '7', ['5', '6', '7', '8'], 'mudah', '35 dibagi 5 sama dengan 7.'],
            ['28 : 7', '4', ['3', '4', '5', '6'], 'mudah', '28 dibagi 7 adalah 4.'],
            ['49 : 7', '7', ['6', '7', '8', '9'], 'mudah', '49 dibagi 7 adalah 7.'],
        ];

        foreach ($soalBagi as $s) {
            Question::create([
                'topic_id' => $pembagianId,
                'tipe' => 'pilihan_ganda',
                'teks' => "Berapakah hasil dari {$s[0]}?",
                'opsi_json' => $s[2],
                'jawaban' => $s[1],
                'penjelasan' => $s[4],
                'kesulitan' => $s[3],
                'versi' => 1,
            ]);
        }
    }

    private function seedGeneralQuestions(int $sainsId, int $sejarahId): void
    {
        // 15 Soal Sains & Tata Surya (PG, Isian, Benar/Salah)
        $soalSains = [
            ['tipe' => 'pilihan_ganda', 'teks' => 'Planet manakah yang dijuluki Planet Merah?', 'jawaban' => 'Mars', 'opsi' => ['Venus', 'Mars', 'Jupiter', 'Saturnus'], 'kesulitan' => 'mudah', 'penjelasan' => 'Mars berwarna kemerahan karena tanahnya banyak mengandung besi oksida (karat).'],
            ['tipe' => 'pilihan_ganda', 'teks' => 'Planet terbesar dalam tata surya kita adalah...', 'jawaban' => 'Jupiter', 'opsi' => ['Bumi', 'Saturnus', 'Jupiter', 'Neptunus'], 'kesulitan' => 'mudah', 'penjelasan' => 'Jupiter adalah planet raksasa gas terbesar di tata surya.'],
            ['tipe' => 'pilihan_ganda', 'teks' => 'Bintang yang menjadi pusat tata surya adalah...', 'jawaban' => 'Matahari', 'opsi' => ['Bulan', 'Matahari', 'Sirius', 'Polaris'], 'kesulitan' => 'mudah', 'penjelasan' => 'Matahari adalah bintang yang gravitasinya mengikat seluruh planet dalam tata surya.'],
            ['tipe' => 'benar_salah', 'teks' => 'Bulan memancarkan cahayanya sendiri pada malam hari.', 'jawaban' => 'Salah', 'opsi' => ['Benar', 'Salah'], 'kesulitan' => 'mudah', 'penjelasan' => 'Bulan tidak memancarkan cahaya sendiri, melainkan memantulkan cahaya dari Matahari.'],
            ['tipe' => 'benar_salah', 'teks' => 'Oksigen adalah gas yang paling melimpah di atmosfer Bumi.', 'jawaban' => 'Salah', 'opsi' => ['Benar', 'Salah'], 'kesulitan' => 'sedang', 'penjelasan' => 'Gas terbanyak di atmosfer Bumi adalah Nitrogen (~78%), sedangkan Oksigen sekitar 21%.'],
            ['tipe' => 'isian_singkat', 'teks' => 'Proses tumbuhan hijau membuat makanan sendiri menggunakan sinar matahari disebut...', 'jawaban' => 'fotosintesis', 'opsi' => null, 'kesulitan' => 'sedang', 'penjelasan' => 'Fotosintesis adalah proses biokimia tumbuhan hijau menghasilkan glukosa dan oksigen.'],
            ['tipe' => 'pilihan_ganda', 'teks' => 'Alat untuk mengamati benda-benda luar angkasa yang jauh disebut...', 'jawaban' => 'Teleskop', 'opsi' => ['Mikroskop', 'Teleskop', 'Periskop', 'Stetoskop'], 'kesulitan' => 'mudah', 'penjelasan' => 'Teleskop digunakan untuk memperbesar bayangan benda-benda astronomi yang sangat jauh.'],
            ['tipe' => 'pilihan_ganda', 'teks' => 'Berapa waktu yang dibutuhkan Bumi untuk melakukan satu kali revolusi mengelilingi Matahari?', 'jawaban' => '365 hari', 'opsi' => ['24 jam', '30 hari', '365 hari', '7 hari'], 'kesulitan' => 'mudah', 'penjelasan' => 'Satu tahun atau satu kali revolusi Bumi membutuhkan waktu sekitar 365,25 hari.'],
            ['tipe' => 'benar_salah', 'teks' => 'Rotasi Bumi menyebabkan terjadinya pergantian siang dan malam.', 'jawaban' => 'Benar', 'opsi' => ['Benar', 'Salah'], 'kesulitan' => 'mudah', 'penjelasan' => 'Perputaran Bumi pada porosnya (rotasi) menyebabkan bagian bumi bergantian menghadap matahari.'],
            ['tipe' => 'isian_singkat', 'teks' => 'Satelit alami yang mengelilingi planet Bumi bernama...', 'jawaban' => 'Bulan', 'opsi' => null, 'kesulitan' => 'mudah', 'penjelasan' => 'Bulan adalah satu-satunya satelit alami milik planet Bumi.'],
            ['tipe' => 'pilihan_ganda', 'teks' => 'Hewan yang memakan tumbuhan saja disebut...', 'jawaban' => 'Herbivora', 'opsi' => ['Karnivora', 'Herbivora', 'Omnivora', 'Insektivora'], 'kesulitan' => 'mudah', 'penjelasan' => 'Herbivora adalah golongan hewan pemakan tumbuhan seperti sapi dan kambing.'],
            ['tipe' => 'pilihan_ganda', 'teks' => 'Bagian sel darah yang berfungsi mengangkut oksigen ke seluruh tubuh adalah...', 'jawaban' => 'Sel darah merah', 'opsi' => ['Sel darah putih', 'Sel darah merah', 'Keping darah', 'Plasma darah'], 'kesulitan' => 'sedang', 'penjelasan' => 'Hemoglobin di dalam sel darah merah mengikat oksigen untuk diedarkan ke seluruh tubuh.'],
            ['tipe' => 'benar_salah', 'teks' => 'Air membeku menjadi es pada suhu 0 derajat Celsius.', 'jawaban' => 'Benar', 'opsi' => ['Benar', 'Salah'], 'kesulitan' => 'mudah', 'penjelasan' => 'Titik beku air murni pada tekanan normal adalah tepat 0 derajat Celsius.'],
            ['tipe' => 'isian_singkat', 'teks' => 'Satuan untuk mengukur kuat arus listrik adalah...', 'jawaban' => 'ampere', 'opsi' => null, 'kesulitan' => 'sulit', 'penjelasan' => 'Kuat arus listrik dinyatakan dalam satuan Ampere (A).'],
            ['tipe' => 'pilihan_ganda', 'teks' => 'Perubahan wujud benda dari padat langsung menjadi gas dinamakan...', 'jawaban' => 'Menyublim', 'opsi' => ['Mencair', 'Menguap', 'Menyublim', 'Mengembun'], 'kesulitan' => 'sedang', 'penjelasan' => 'Menyublim adalah perubahan wujud dari padat langsung menjadi gas, contohnya kapur barus.'],
        ];

        foreach ($soalSains as $s) {
            Question::create([
                'topic_id' => $sainsId,
                'tipe' => $s['tipe'],
                'teks' => $s['teks'],
                'opsi_json' => $s['opsi'],
                'jawaban' => $s['jawaban'],
                'penjelasan' => $s['penjelasan'],
                'kesulitan' => $s['kesulitan'],
                'versi' => 1,
            ]);
        }

        // 15 Soal Sejarah & Budaya Nusantara
        $soalSejarah = [
            ['tipe' => 'pilihan_ganda', 'teks' => 'Siapakah Proklamator Kemerdekaan Republik Indonesia?', 'jawaban' => 'Soekarno dan Moh. Hatta', 'opsi' => ['Soekarno dan Moh. Hatta', 'Ki Hajar Dewantara', 'Jenderal Soedirman', 'Pangeran Diponegoro'], 'kesulitan' => 'mudah', 'penjelasan' => 'Ir. Soekarno dan Drs. Moh. Hatta membacakan teks proklamasi pada 17 Agustus 1945.'],
            ['tipe' => 'pilihan_ganda', 'teks' => 'Rumah adat Tongkonan berasal dari daerah...', 'jawaban' => 'Toraja (Sulawesi Selatan)', 'opsi' => ['Sumatera Barat', 'Toraja (Sulawesi Selatan)', 'Papua', 'Kalimantan Barat'], 'kesulitan' => 'sedang', 'penjelasan' => 'Rumah Tongkonan adalah rumah adat khas masyarakat suku Toraja di Sulawesi Selatan.'],
            ['tipe' => 'pilihan_ganda', 'teks' => 'Candi Buddha terbesar di dunia yang terletak di Magelang, Jawa Tengah adalah...', 'jawaban' => 'Candi Borobudur', 'opsi' => ['Candi Prambanan', 'Candi Borobudur', 'Candi Mendut', 'Candi Sewu'], 'kesulitan' => 'mudah', 'penjelasan' => 'Candi Borobudur dibangun pada masa dinasti Syailendra sekitar abad ke-8 Masehi.'],
            ['tipe' => 'benar_salah', 'teks' => 'Lagu kebangsaan Indonesia Raya diciptakan oleh W.R. Soepratman.', 'jawaban' => 'Benar', 'opsi' => ['Benar', 'Salah'], 'kesulitan' => 'mudah', 'penjelasan' => 'Wage Rudolf Soepratman menciptakan lagu Indonesia Raya yang pertama kali diperdengarkan pada Kongres Pemuda II 1928.'],
            ['tipe' => 'benar_salah', 'teks' => 'Hari Sumpah Pemuda diperingati setiap tanggal 10 November.', 'jawaban' => 'Salah', 'opsi' => ['Benar', 'Salah'], 'kesulitan' => 'mudah', 'penjelasan' => 'Sumpah Pemuda diperingati setiap 28 Oktober, sedangkan 10 November adalah Hari Pahlawan.'],
            ['tipe' => 'isian_singkat', 'teks' => 'Ibu kota negara Indonesia yang baru di Kalimantan Timur bernama...', 'jawaban' => 'Nusantara', 'opsi' => null, 'kesulitan' => 'mudah', 'penjelasan' => 'Ibu Kota Nusantara (IKN) terletak di sebagian wilayah Penajam Paser Utara dan Kutai Kartanegara.'],
            ['tipe' => 'pilihan_ganda', 'teks' => 'Alat musik petik tradisional yang berasal dari Pulau Rote, NTT adalah...', 'jawaban' => 'Sasando', 'opsi' => ['Angklung', 'Sasando', 'Gamelan', 'Kolintang'], 'kesulitan' => 'sedang', 'penjelasan' => 'Sasando adalah alat musik dawai petik unik dari Pulau Rote dengan resonator daun lontar.'],
            ['tipe' => 'pilihan_ganda', 'teks' => 'Semboyan negara Bhinneka Tunggal Ika diambil dari kitab...', 'jawaban' => 'Sutasoma', 'opsi' => ['Negarakertagama', 'Sutasoma', 'Arjunawiwaha', 'Pararaton'], 'kesulitan' => 'sedang', 'penjelasan' => 'Frasa Bhinneka Tunggal Ika ditulis oleh Mpu Tantular dalam Kitab Sutasoma.'],
            ['tipe' => 'isian_singkat', 'teks' => 'Tari Saman yang sangat terkenal berasal dari provinsi...', 'jawaban' => 'Aceh', 'opsi' => null, 'kesulitan' => 'mudah', 'penjelasan' => 'Tari Saman adalah tarian suku Gayo dari Provinsi Aceh yang diakui UNESCO.'],
            ['tipe' => 'pilihan_ganda', 'teks' => 'Pahlawan nasional yang dikenal sebagai Bapak Pendidikan Nasional adalah...', 'jawaban' => 'Ki Hajar Dewantara', 'opsi' => ['Bung Tomo', 'Ki Hajar Dewantara', 'R.A. Kartini', 'Moh. Yamin'], 'kesulitan' => 'mudah', 'penjelasan' => 'Ki Hajar Dewantara mendirikan Perguruan Taman Siswa dan semboyan Tut Wuri Handayani.'],
            ['tipe' => 'benar_salah', 'teks' => 'Kerajaan Majapahit berpusat di wilayah Jawa Barat.', 'jawaban' => 'Salah', 'opsi' => ['Benar', 'Salah'], 'kesulitan' => 'mudah', 'penjelasan' => 'Kerajaan Majapahit berpusat di Trowulan, Mojokerto, Jawa Timur.'],
            ['tipe' => 'pilihan_ganda', 'teks' => 'Peristiwa penculikan Soekarno-Hatta menjelang proklamasi terjadi di...', 'jawaban' => 'Rengasdengklok', 'opsi' => ['Kalijati', 'Rengasdengklok', 'Ambarawa', 'Surabaya'], 'kesulitan' => 'sedang', 'penjelasan' => 'Para pemuda mendesak Soekarno-Hatta ke Rengasdengklok pada 16 Agustus 1945.'],
            ['tipe' => 'isian_singkat', 'teks' => 'Senjata tradisional khas suku Dayak di Kalimantan adalah...', 'jawaban' => 'Mandau', 'opsi' => null, 'kesulitan' => 'sedang', 'penjelasan' => 'Mandau adalah senjata pusaka dan alat tradisional suku Dayak.'],
            ['tipe' => 'pilihan_ganda', 'teks' => 'Upacara adat pembakaran jenazah di Bali disebut...', 'jawaban' => 'Ngaben', 'opsi' => ['Sekaten', 'Ngaben', 'Rambu Solo', 'Tiwah'], 'kesulitan' => 'mudah', 'penjelasan' => 'Ngaben adalah upacara kremasi jenazah umat Hindu di Bali untuk menyucikan roh.'],
            ['tipe' => 'benar_salah', 'teks' => 'Pancasila terdiri dari lima sila yang menjadi dasar negara Indonesia.', 'jawaban' => 'Benar', 'opsi' => ['Benar', 'Salah'], 'kesulitan' => 'mudah', 'penjelasan' => 'Pancasila adalah dasar filsafat negara Republik Indonesia yang memiliki 5 sila.'],
        ];

        foreach ($soalSejarah as $s) {
            Question::create([
                'topic_id' => $sejarahId,
                'tipe' => $s['tipe'],
                'teks' => $s['teks'],
                'opsi_json' => $s['opsi'],
                'jawaban' => $s['jawaban'],
                'penjelasan' => $s['penjelasan'],
                'kesulitan' => $s['kesulitan'],
                'versi' => 1,
            ]);
        }
    }
}
