# Panduan Lengkap Deploy ke Shared Hosting cPanel
## EduGame Indonesia (Laravel 12 + Inertia.js + React + MySQL)

Dokumen ini memuat panduan langkah demi langkah untuk men-deploy aplikasi **EduGame Indonesia** ke shared hosting berbasis cPanel secara aman, efisien, dan andal.

---

### 1. Prasyarat Shared Hosting

Pastikan paket hosting cPanel Anda memenuhi persyaratan berikut:
1. **PHP Version**: PHP 8.2 atau lebih baru.
2. **Database**: MySQL 5.7+ atau MariaDB 10.3+ dengan collation `utf8mb4_unicode_ci`.
3. **Ekstensi PHP Wajib**:
   - `bcmath`
   - `ctype`
   - `curl`
   - `fileinfo`
   - `json`
   - `mbstring`
   - `openssl`
   - `pdo` & `pdo_mysql`
   - `tokenizer`
   - `xml`
4. **Pengaturan Memory & Execution Time**:
   - `memory_limit` minimal `128M` (disarankan `256M`).
   - `max_execution_time` minimal `60s`.

---

### 2. Konfigurasi PHP di cPanel

1. Buka cPanel > Masuk ke menu **MultiPHP Manager**.
2. Pilih domain yang digunakan (misal: `edugame.sekolah.sch.id` atau subdomain), lalu set versi PHP ke **PHP 8.2** atau **PHP 8.3**.
3. Buka menu **Select PHP Version** (jika menggunakan CloudLinux) atau **MultiPHP INI Editor**:
   - Pastikan ekstensi `pdo_mysql`, `mbstring`, `fileinfo`, `bcmath`, `curl`, `xml`, `openssl` dalam kondisi aktif (*checked*).
   - Pastikan `allow_url_fopen = On`.

---

### 3. Pembuatan Database & User MySQL di cPanel

1. Buka menu **MySQL Database Wizard** di cPanel:
   - **Step 1: Create A Database**: Masukkan nama database, misal: `cpaneluser_edugame`.
   - **Step 2: Create Database Users**: Buat username baru, misal: `cpaneluser_dbuser` dan password yang kuat.
   - **Step 3: Add User to Database**: Centang **ALL PRIVILEGES**.
2. Catat:
   - Nama Database: `cpaneluser_edugame`
   - Username: `cpaneluser_dbuser`
   - Password: `(password_anda)`
   - Host: `localhost` atau `127.0.0.1`

---

### 4. Struktur Folder & Deploy via Git Version Control di cPanel

Berdasarkan konfigurasi subdomain Anda (**`edugame.vxai.online`** di direktori `/home/chaf2674/edugame.vxai.online`):

#### Metode 1: Menggunakan cPanel "Git™ Version Control" (Paling Cepat & Praktis)
1. Buka cPanel > Menu **Git™ Version Control**.
2. Klik tombol **Create**.
3. Masukkan informasi:
   - **Clone URL**: `https://github.com/Vicky1902-py/edugame.git`
   - **Repository Path**: `/home/chaf2674/edugame.vxai.online`
   - **Repository Name**: `edugame`
4. Klik **Create**. cPanel akan mengkloning seluruh source code beserta build frontend (`public/build`) secara otomatis.

#### Pengaturan Document Root Subdomain:
1. Masuk ke cPanel > **Domains** > cari `edugame.vxai.online`.
2. Klik ikon pensil pada kolom **Document Root**.
3. Arahkan Document Root ke:
   ```text
   /home/chaf2674/edugame.vxai.online/public
   ```
   *(PENTING: Mengarahkan Document Root ke folder `public` memastikan keamanan 100% sehingga file `.env` dan kode backend tidak bisa diakses dari browser).*
4. Jika Document Root terkunci di `/home/chaf2674/edugame.vxai.online`, jangan khawatir: file `.htaccess` bawaan EduGame di root folder sudah otomatis mengalihkan semua request masuk ke folder `public/`.

---

### 5. Kompilasi Aset Frontend (Dilakukan di Komputer Lokal)

Karena server shared hosting cPanel umumnya tidak menyediakan Node.js, kompilasi frontend (React, Tailwind, Phaser) dilakukan di komputer lokal sebelum diunggah:

1. Di komputer lokal, jalankan perintah build:
   ```bash
   ./build.bat
   # atau
   powershell -ExecutionPolicy Bypass -File ./build.ps1
   ```
2. Berkas hasil kompilasi akan otomatis dibuat di folder `public/build/` (terdiri dari `manifest.json` dan folder `assets/`).
3. Folder `public/build/` ini wajib ikut diunggah ke cPanel.

---

### 6. Unggah Berkas & Instalasi Vendor

1. Di komputer lokal, arsipkan berkas proyek menjadi `edugame.zip`.
   > **PENTING**: Abaikan (jangan masukkan) folder `node_modules/` dan folder `.git/`.
2. Buka **File Manager** di cPanel, unggah `edugame.zip` ke direktori tujuan (misal: `/home/username/edugame`).
3. Ekstrak file zip tersebut.
4. Buat file `.env` di `/home/username/edugame/.env` dengan menyalin `.env.example` dan isi konfigurasinya:
   ```dotenv
   APP_NAME="EduGame Indonesia"
   APP_ENV=production
   APP_KEY=base64:... (gunakan app key dari lokal atau jalankan artisan key:generate)
   APP_DEBUG=false
   APP_URL=https://game.domainanda.com

   APP_LOCALE=id
   APP_FALLBACK_LOCALE=id

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=cpaneluser_edugame
   DB_USERNAME=cpaneluser_dbuser
   DB_PASSWORD=password_db_anda

   SESSION_DRIVER=file
   CACHE_STORE=file
   QUEUE_CONNECTION=sync
   ```

5. Jika hosting Anda menyediakan **Terminal / SSH**:
   ```bash
   cd /home/username/edugame
   composer install --no-dev --optimize-autoloader
   php artisan key:generate --force
   php artisan migrate --force --seed
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
6. Jika hosting **TIDAK menyediakan SSH**:
   - Di komputer lokal, jalankan `composer install --no-dev --optimize-autoloader`.
   - Unggah folder `vendor/` dari lokal ke server hosting.
   - Migrasi database dapat diimpor langsung via **phpMyAdmin** dengan mengekspor schema lokal atau memanggil route setup sementara yang dilindungi password.

---

### 7. Pengaturan Izin Folder (Permissions)

Pastikan izin folder web server diatur dengan benar agar Laravel dapat menulis log, session file, dan cache:
- Folder `storage/` dan seluruh subfoldernya: `chmod -R 775` (atau `755` tergantung konfigurasi suPHP/FastCGI server Anda).
- Folder `bootstrap/cache/`: `chmod -R 775` (atau `755`).
- File `.env`: `chmod 600` (hanya bisa dibaca oleh pemilik akun).

---

### 8. Pengaturan Cron Job (Laravel Scheduler)

Laravel Scheduler diperlukan untuk membersihkan room Kuis Duel yang kedaluwarsa (>30 menit) dan memperbarui streak harian tanpa perlu proses background yang memakan RAM:

1. Buka menu **Cron Jobs** di cPanel.
2. Pada bagian **Add New Cron Job**:
   - Pilih pengaturan interval: **Once Per Minute** (`* * * * *`).
   - Masukkan baris perintah berikut (sesuaikan path PHP dan home direktori akun Anda):
   ```bash
   /usr/local/bin/php /home/username/edugame/artisan schedule:run >> /dev/null 2>&1
   ```
   *(Tips: Cek path PHP CLI cPanel Anda via menu General Information atau gunakan `which php` di Terminal).*

---

### 9. Checklist Keamanan Produksi

Sebelum merilis aplikasi ke pengguna sekolah:
- [x] `APP_DEBUG=false` di `.env` (mencegah kebocoran kredensial saat error).
- [x] `APP_ENV=production`.
- [x] SSL/HTTPS aktif (gunakan fitur **Let's Encrypt** / **AutoSSL** di cPanel).
- [x] File `.htaccess` memuat header keamanan:
  - `X-Content-Type-Options: nosniff`
  - `X-Frame-Options: SAMEORIGIN`
  - `X-XSS-Protection: 1; mode=block`
  - `Referrer-Policy: strict-origin-when-cross-origin`
- [x] Akses langsung ke file `.env`, file `.git`, dan folder rahasia diblokir oleh web server.
