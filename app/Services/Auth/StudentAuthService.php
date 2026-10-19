<?php

namespace App\Services\Auth;

use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StudentAuthService
{
    /**
     * Daftar kata tidak pantas (bahasa Indonesia & Inggris) untuk disaring.
     */
    protected array $badWords = [
        'anjing', 'babi', 'bangsat', 'kontol', 'memek', 'jembut', 'pantek',
        'perek', 'pelacur', 'lonte', 'itil', 'tai', 'bajingan', 'tolol',
        'goblok', 'idiot', 'puki', 'silit', 'tetek', 'ngentot', 'titit',
        'fuck', 'shit', 'bitch', 'asshole', 'dick', 'pussy', 'bastard', 'cunt'
    ];

    /**
     * Periksa apakah nama panggilan mengandung kata tidak pantas.
     */
    public function containsInappropriateContent(string $name): bool
    {
        $normalized = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));

        foreach ($this->badWords as $badWord) {
            if (str_contains($normalized, $badWord)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Bersihkan nama panggilan dari karakter aneh dan spasi berlebih.
     */
    public function sanitizeNickname(string $nickname): string
    {
        $cleaned = trim(preg_replace('/\s+/', ' ', $nickname));
        return strip_tags($cleaned);
    }

    /**
     * Masuk atau daftarkan siswa menggunakan Kode Kelas + Nama Panggilan + PIN 4 angka.
     *
     * @throws ValidationException
     */
    public function loginOrCreateStudent(string $joinCode, string $nickname, string $pin): User
    {
        $sanitizedNickname = $this->sanitizeNickname($nickname);

        // 1. Validasi sensor kata tidak pantas
        if ($this->containsInappropriateContent($sanitizedNickname)) {
            throw ValidationException::withMessages([
                'nama_panggilan' => ['Nama panggilan mengandung kata yang tidak sopan. Harap gunakan nama yang santun.'],
            ]);
        }

        // 2. Cari kelas berdasarkan kode gabung
        $normalizedCode = strtoupper(trim($joinCode));
        $class = SchoolClass::where('kode_gabung', $normalizedCode)->first();

        if (!$class) {
            throw ValidationException::withMessages([
                'kode_kelas' => ['Kode kelas tidak ditemukan. Mohon periksa kembali kode dari gurumu.'],
            ]);
        }

        // 3. Cari apakah siswa dengan nama ini sudah terdaftar di kelas tersebut
        $enrollment = Enrollment::where('class_id', $class->id)
            ->whereHas('student', function ($query) use ($sanitizedNickname) {
                $query->where('name', $sanitizedNickname);
            })
            ->with('student')
            ->first();

        if ($enrollment) {
            $student = $enrollment->student;

            // Verifikasi PIN 4 angka
            if (!Hash::check($pin, $student->pin_hash)) {
                throw ValidationException::withMessages([
                    'pin' => ['PIN 4 angka salah untuk nama ini. Masukkan PIN yang kamu buat saat pertama kali bergabung.'],
                ]);
            }
        } else {
            // Siswa belum ada di kelas ini: buat akun siswa baru
            $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $sanitizedNickname)) . '_' . strtolower($class->kode_gabung) . '_' . substr(uniqid(), -4);

            $student = User::create([
                'school_id' => $class->school_id,
                'name' => $sanitizedNickname,
                'username' => $username,
                'email' => null,
                'password' => null,
                'pin_hash' => Hash::make($pin),
                'role' => 'siswa',
            ]);

            // Daftarkan ke kelas (Enrollment)
            Enrollment::create([
                'class_id' => $class->id,
                'student_id' => $student->id,
            ]);
        }

        // 4. Buat sesi login
        Auth::login($student);
        session(['active_class_id' => $class->id]);

        return $student;
    }
}
