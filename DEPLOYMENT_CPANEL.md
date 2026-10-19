# Panduan Deploy & Operasional EduGame di Shared Hosting cPanel

Dokumen ini memuat panduan komprehensif untuk instalasi, konfigurasi lingkungan, dan optimasi performa platform **EduGame Indonesia** (Laravel 11, Filament v3, Inertia React, MySQL/MariaDB) di shared hosting cPanel (seperti Rumahweb, Niagahoster, DomaiNesia, dll.).

---

## 1. Persyaratan Lingkungan Server (cPanel)

Pastikan paket hosting cPanel Anda memenuhi spesifikasi berikut:
* **Web Server**: LiteSpeed / Apache dengan modul `mod_rewrite` aktif.
* **Versi PHP**: **PHP 8.2** atau **PHP 8.3** (Rekomendasi: `EA-PHP82`).
* **Database**: MySQL 8.0+ atau MariaDB 10.5+.
* **Alokasi Penyimpanan**: Minimal 1 GB ruang kosong.

---

## 2. Konfigurasi PHP di cPanel (MultiPHP INI Editor)

Buka menu **cPanel > Software > MultiPHP INI Editor**, pilih domain/root hosting Anda (`edugame.vxai.online`), lalu sesuaikan nilai parameter berikut:

```ini
; Alokasi Memori (Wajib untuk kompilasi AST Filament & Livewire)
memory_limit = 256M

; Batas Ukuran Unggahan Media & Soal
upload_max_filesize = 32M
post_max_size = 32M

; Batas Waktu Eksekusi Script
max_execution_time = 180
max_input_time = 120

; Pengaturan Input & Karakter
max_input_vars = 3000
default_charset = "UTF-8"
date.timezone = "Asia/Jakarta"
```

---

## 3. Ekstensi PHP yang Wajib Diaktifkan

Buka menu **cPanel > Software > Select PHP Version > Extensions**, pastikan ekstensi berikut dicentang:

| Ekstensi | Fungsi untuk EduGame |
| :--- | :--- |
| `intl` | Wajib untuk Filament v3 (format tanggal, angka, dan mata uang). |
| `fileinfo` | Wajib untuk validasi MIME-type unggahan gambar dan media CMS. |
| `gd` atau `imagick` | Optimasi & kompresi otomatis thumbnail gambar oleh Spatie MediaLibrary. |
| `exif` | Ekstraksi metadata orientasi gambar JPEG/PNG oleh Spatie MediaLibrary. |
| `zip` | Wajib untuk ekstraksi pustaka & pembuatan arsip ZIP oleh Spatie Backup. |
| `pdo_mysql` | Koneksi database MySQL/MariaDB. |
| `mbstring` | Manipulasi karakter string multibyte & token keamanan. |
| `bcmath` | Kalkulasi presisi tinggi & kriptografi 2FA TOTP. |
| `curl` | Komunikasi HTTP API eksternal (Google DNS / S3 Storage). |

---

## 4. Konfigurasi File `.env` Produksi

Pastikan file `.env` di server hosting Anda memuat pengaturan berikut:

```env
APP_NAME="EduGame Indonesia"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://edugame.vxai.online

LOG_CHANNEL=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Koneksi Database cPanel
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chaf2674_edugame
DB_USERNAME=chaf2674_dbuser
DB_PASSWORD="PasswordKuatAnda!"

# Path Binary mysqldump di cPanel Shared Hosting
# (Umumnya terletak di /usr/bin atau /usr/local/bin)
DB_DUMP_PATH=/usr/bin

# Target Disk Cadangan (Spatie Backup)
# Opsi: local (internal hosting) atau s3 (Cloudflare R2 / AWS S3 / IDCloudHost)
BACKUP_DISKS=local
BACKUP_ARCHIVE_PASSWORD="EnkripsiKuatCadangan2026!"

# Pengaturan S3 (Jika menyimpan cadangan di luar server)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=auto
AWS_BUCKET=edugame-backups
AWS_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=false

# Session & Cache Driver
BROADCAST_CONNECTION=log
CACHE_STORE=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

---

## 5. Perintah Optimasi Produksi (Wajib Dijalankan Pasca-Deploy)

Setiap kali melakukan pembaruan kode (`git pull origin main`), jalankan perintah artisan berikut di terminal cPanel agar aplikasi berjalan dengan kecepatan maksimal dan minim penggunaan RAM:

```bash
# 1. Bersihkan cache konfigurasi lama
php artisan optimize:clear

# 2. Cache konfigurasi, rute, dan blade view
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Cache komponen dan ikon Filament v3
php artisan filament:optimize
php artisan icons:cache

# 4. Migrasi skema database baru (jika ada)
php artisan migrate --force
```

---

## 6. Pengaturan Cron Job cPanel (Automasi & Cadangan)

Buka menu **cPanel > Advanced > Cron Jobs**, tambahkan perintah scheduler Laravel untuk dijalankan setiap menit:

* **Interval**: `* * * * *` (Setiap Menit)
* **Command**:
  ```bash
  /usr/local/bin/php /home/chaf2674/public_html/edugame.vxai.online/artisan schedule:run >> /dev/null 2>&1
  ```

*Scheduler ini otomatis menangani:*
1. Pembersihan token kedaluwarsa & sesi usang.
2. Eksekusi cadangan terjadwal (`php artisan backup:run --only-db`).
3. Pemantauan antrean permintaan data siswa (Kepatuhan UU PDP).

---

## 7. Keamanan & Troubleshooting Khusus cPanel

1. **Error `mysqldump: command not found` saat backup**:
   * Periksa lokasi `mysqldump` dengan mengetik `which mysqldump` di terminal SSH hosting.
   * Masukkan path hasilnya ke variabel `DB_DUMP_PATH` di `.env` (misal `DB_DUMP_PATH=/usr/bin` atau `DB_DUMP_PATH=/usr/local/cpanel/bin`).
2. **Timeout saat Backup Database Besar**:
   * Spatie Backup telah dikonfigurasi dengan flag `use_single_transaction = true` dan `timeout = 300` detik di `config/database.php`.
   * Di shared hosting, gunakan selalu opsi `--only-db` pada backup terjadwal untuk menghindari pembengkakan file zip yang memicu batas memori hosting.
3. **Reset Darurat 2FA Superadmin**:
   * Jika Superadmin kehilangan perangkat TOTP Authenticator, jalankan:
     ```bash
     php artisan app:reset-2fa admin@edugame.id
     ```
   * Perintah ini akan menghapus secret 2FA dan recovery code sehingga akun dapat diakses kembali menggunakan email & password.
