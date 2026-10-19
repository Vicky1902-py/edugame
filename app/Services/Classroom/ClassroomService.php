<?php

namespace App\Services\Classroom;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClassroomService
{
    /**
     * Karakter aman untuk kode gabung (tanpa 0, O, 1, I untuk mencegah salah ketik).
     */
    protected string $safeChars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    /**
     * Buat kode unik 6 karakter.
     */
    public function generateUniqueJoinCode(): string
    {
        do {
            $code = '';
            $maxIndex = strlen($this->safeChars) - 1;
            for ($i = 0; $i < 6; $i++) {
                $code .= $this->safeChars[random_int(0, $maxIndex)];
            }
        } while (SchoolClass::where('kode_gabung', $code)->exists());

        return $code;
    }

    /**
     * Buat kelas baru untuk guru.
     */
    public function createClass(User $teacher, array $data): SchoolClass
    {
        return SchoolClass::create([
            'school_id' => $teacher->school_id,
            'guru_id' => $teacher->id,
            'nama' => trim($data['nama']),
            'jenjang' => $data['jenjang'] ?? 'SD',
            'kode_gabung' => $this->generateUniqueJoinCode(),
            'leaderboard_visible' => $data['leaderboard_visible'] ?? true,
        ]);
    }

    /**
     * Ambil semua kelas milik guru tertentu.
     */
    public function getTeacherClasses(int $teacherId): Collection
    {
        return SchoolClass::where('guru_id', $teacherId)
            ->withCount(['enrollments as total_siswa'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Ambil detail kelas beserta daftar siswa dan progresnya.
     *
     * @throws ValidationException
     */
    public function getClassDetails(int $classId, int $teacherId): SchoolClass
    {
        $class = SchoolClass::where('id', $classId)
            ->where('guru_id', $teacherId)
            ->with([
                'enrollments.student' => function ($query) {
                    $query->select('id', 'name', 'username', 'created_at');
                },
                'school',
            ])
            ->first();

        if (!$class) {
            throw ValidationException::withMessages([
                'class' => ['Kelas tidak ditemukan atau Anda tidak memiliki akses ke kelas ini.'],
            ]);
        }

        return $class;
    }

    /**
     * Perbarui data kelas.
     *
     * @throws ValidationException
     */
    public function updateClass(int $classId, int $teacherId, array $data): SchoolClass
    {
        $class = SchoolClass::where('id', $classId)
            ->where('guru_id', $teacherId)
            ->first();

        if (!$class) {
            throw ValidationException::withMessages([
                'class' => ['Kelas tidak ditemukan atau Anda tidak memiliki akses.'],
            ]);
        }

        $class->update([
            'nama' => isset($data['nama']) ? trim($data['nama']) : $class->nama,
            'jenjang' => $data['jenjang'] ?? $class->jenjang,
            'leaderboard_visible' => isset($data['leaderboard_visible']) ? (bool) $data['leaderboard_visible'] : $class->leaderboard_visible,
        ]);

        return $class;
    }

    /**
     * Hapus kelas milik guru.
     *
     * @throws ValidationException
     */
    public function deleteClass(int $classId, int $teacherId): bool
    {
        $class = SchoolClass::where('id', $classId)
            ->where('guru_id', $teacherId)
            ->first();

        if (!$class) {
            throw ValidationException::withMessages([
                'class' => ['Kelas tidak ditemukan atau Anda tidak memiliki akses.'],
            ]);
        }

        return (bool) $class->delete();
    }
}
