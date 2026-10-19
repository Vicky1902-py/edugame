<?php

namespace App\Services\Auth;

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TeacherAuthService
{
    /**
     * Daftarkan guru baru beserta sekolahnya.
     */
    public function registerTeacher(array $data): User
    {
        // Cari atau buat sekolah
        $schoolName = trim($data['school_name'] ?? 'Sekolah EduGame');
        $school = School::firstOrCreate(
            ['nama' => $schoolName],
            ['alamat' => null, 'jenjang' => 'SD']
        );

        $teacher = User::create([
            'school_id' => $school->id,
            'name' => trim($data['name']),
            'username' => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $data['name'])) . '_' . substr(uniqid(), -4),
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'role' => 'guru',
        ]);

        Auth::login($teacher);

        return $teacher;
    }

    /**
     * Autentikasi guru berdasarkan email dan kata sandi.
     *
     * @throws ValidationException
     */
    public function loginTeacher(string $email, string $password): User
    {
        $teacher = User::where('email', strtolower(trim($email)))->first();

        if (!$teacher || !Hash::check($password, $teacher->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau kata sandi yang Anda masukkan salah.'],
            ]);
        }

        if (!in_array($teacher->role, ['guru', 'admin'])) {
            throw ValidationException::withMessages([
                'email' => ['Akun ini bukan akun pengajar.'],
            ]);
        }

        Auth::login($teacher);

        return $teacher;
    }
}
