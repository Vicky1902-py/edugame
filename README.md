# EduGame Indonesia 🎮📚

Platform MVP gaming edukasi berbasis website untuk siswa SD, SMP, SMA, dan SMK di Indonesia. Memungkinkan guru membuat kelas dan bank soal, siswa bermain game edukasi menggunakan kode kelas tanpa data pribadi berlebih, dan guru memantau progres belajar siswa secara mendalam lewat dashboard analitik.

---

## 🚀 Fitur Utama

1. **Akses & Privasi Ramah Anak**:
   - **Guru**: Registrasi dan login dengan email & password.
   - **Siswa**: Masuk cepat dengan **Kode Kelas + Nama Panggilan + PIN 4 Angka** (tanpa email, tanpa nomor HP, tanpa data pribadi).
2. **Manajemen Kelas & Bank Soal**:
   - Pembuatan kelas dengan kode gabung unik 6 karakter.
   - CRUD soal: Pilihan Ganda, Isian Singkat, Benar/Salah. Dilengkapi topik, tingkat kesulitan, dan pembahasan jawaban.
   - Impor soal massal via file CSV.
3. **Mesin Kuis-Game**:
   - Validasi jawaban dan skor **100% di server**.
   - Timer per soal, nyawa, petunjuk (*hint*), skor dasar + bonus kecepatan + streak.
   - Tema visual & audio berbasis file konfigurasi (suara nonaktif secara bawaan dengan tombol mute).
4. **Dua Game Edukasi**:
   - 🏎️ **"Balapan Perkalian" (SD Kelas 4-6)**: Game balap mobil 3-jalur melawan 2 bot menggunakan engine Phaser 3. Jawaban perkalian/pembagian yang benar melajukan mobil, salah memperlambat. Layar akhir menyajikan evaluasi soal yang keliru beserta pembahasan.
   - ⚔️ **"Kuis Duel" (SMP)**: Mode duel 2-4 pemain dengan batas 15 detik/soal, poin kecepatan, dan reaksi emoji (tanpa chat bebas). Didukung arsitektur `RealtimeDriver` (default: database polling ringan ramah shared hosting cPanel tanpa server WebSocket yang harus terus hidup).
5. **Gamifikasi**:
   - Level, XP Ledger, 6 Lencana Prestasi, Streak Harian, dan Leaderboard Kelas (bisa disembunyikan guru).
6. **Dashboard Guru**:
   - Analitik skor per siswa, pemahaman per topik, 10 soal paling sering salah, riwayat keaktifan, dan ekspor data CSV.

---

## 🛠️ Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+) dengan arsitektur berlapis: Route -> Controller -> FormRequest -> Service -> Repository/Eloquent -> API Resource.
- **Frontend**: Inertia.js v2 + React 18 + TypeScript + Tailwind CSS v4.
- **Game Engine**: Phaser 3 (tertanam dalam komponen React).
- **Database**:
  - Lokal: SQLite (`database/database.sqlite`) — instan tanpa perlu install MySQL server lokal.
  - Produksi: MySQL / MariaDB (utf8mb4).
- **Arsitektur Realtime**: `RealtimeDriverInterface` (Default: Database Polling 1-2s yang sangat ringan; Opsi: Pusher/Ably jika diaktifkan).

---

## ⚡ Cara Menjalankan di Komputer Lokal (< 5 Menit)

### 1. Prasyarat
- **PHP 8.2+** terpasang di sistem.
- **Composer 2.x** terpasang.
- **Node.js 20+** terpasang.

### 2. Langkah Instalasi
Jalankan perintah berikut di terminal:

```bash
# 1. Masuk ke direktori proyek
cd "G:\My Drive\gaming"

# 2. Salin environment file (jika belum ada)
cp .env.example .env

# 3. Jalankan migrasi dan seeder database
php artisan migrate:fresh --seed

# 4. Kompilasi aset frontend
./build.bat
# (atau jalankan: powershell -ExecutionPolicy Bypass -File ./build.ps1)

# 5. Jalankan server lokal
php artisan serve
```

Buka peramban Anda di: **`http://127.0.0.1:8000`**

---

## 🔑 Akun Uji Coba Bawaan (Seed Data)

Setelah menjalankan `php artisan migrate:fresh --seed`, akun berikut siap digunakan:

| Peran | Kredensial Masuk | Keterangan |
| :--- | :--- | :--- |
| **Guru** | Email: `guru@edugame.id`<br>Password: `password123` | Guru Kelas 5-A SD Negeri 1 Merdeka Nusantara |
| **Admin** | Email: `admin@edugame.id`<br>Password: `admin12345` | Administrator platform |
| **Siswa 1** | Kode Kelas: `JUARA5`<br>Nama: `Budi`<br>PIN: `1234` | Siswa Kelas 5-A (XP: 350, Level 2) |
| **Siswa 2** | Kode Kelas: `JUARA5`<br>Nama: `Siti`<br>PIN: `2345` | Siswa Kelas 5-A (XP: 520, Level 3) |
| **Siswa 3** | Kode Kelas: `JUARA5`<br>Nama: `Edo`<br>PIN: `3456` | Siswa Kelas 5-A (XP: 180, Level 1) |
| **Siswa 4** | Kode Kelas: `JUARA5`<br>Nama: `Maya`<br>PIN: `4567` | Siswa Kelas 5-A (XP: 640, Level 3) |
| **Siswa 5** | Kode Kelas: `JUARA5`<br>Nama: `Rizky`<br>PIN: `5678` | Siswa Kelas 5-A (XP: 290, Level 2) |

---

## 🧪 Menjalankan Pengujian Otomatis

Untuk memastikan semua modul dan relasi database berjalan normal:

```bash
php artisan test
```

Semua pengujian berjalan menggunakan in-memory database (`:memory:`) terisolasi dengan `RefreshDatabase`.

---

## 🌐 Panduan Deploy ke cPanel Shared Hosting

Petunjuk lengkap mengenai pemilihan versi MultiPHP, konfigurasi MySQL, pembuatan cron job scheduler, pengunggahan build frontend, dan checklist keamanan server produksi tersedia di:
👉 **[Dokumentasi Deploy cPanel](docs/DEPLOY_CPANEL.md)**

---

## 📂 Struktur Direktori Proyek

```text
├── app/
│   ├── Http/
│   │   ├── Controllers/         # Controller tipis (Thin Controllers)
│   │   ├── Middleware/          # HandleInertiaRequests, RoleCheck, RateLimiting
│   │   ├── Requests/            # FormRequest untuk validasi input yang ketat
│   │   └── Resources/           # API Resource untuk transformasi respon JSON
│   ├── Models/                  # 17 Model Eloquent dengan relasi & indeks
│   └── Services/                # Logika bisnis: skor, gamifikasi, realtime
├── database/
│   ├── migrations/              # 18 skrip migrasi database
│   └── seeders/                 # DatabaseSeeder (Guru, Siswa, 90 Soal, Lencana)
├── docs/
│   └── DEPLOY_CPANEL.md         # Panduan komprehensif deploy cPanel
├── resources/
│   ├── css/                     # Tailwind CSS v4 styling
│   ├── js/
│   │   ├── Components/          # Komponen UI React
│   │   ├── Pages/               # Halaman Inertia (Welcome, Auth, Dashboard, Game)
│   │   ├── Games/               # Phaser 3 Game Scenes (Balapan & Kuis Duel)
│   │   └── app.tsx              # Entrypoint Inertia React
│   └── views/
│       └── app.blade.php        # Root Blade template
├── routes/
│   ├── web.php                  # Rute web dan autentikasi
│   ├── console.php              # Tugas terjadwal / schedule
│   └── channels.php             # Kanal broadcast (opsional)
├── build.bat / build.ps1        # Skrip otomatisasi kompilasi aset lokal
└── tests/                       # Pengujian Feature & Unit
```
