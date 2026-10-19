<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Game;
use App\Models\SchoolClass;
use App\Models\UserBadge;
use App\Models\XpLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class StudentDashboardController extends Controller
{
    /**
     * Tampilkan beranda siswa.
     */
    public function index(Request $request): Response
    {
        $student = Auth::user();

        // Ambil kelas aktif siswa
        $activeClassId = session('active_class_id');
        $enrollment = Enrollment::where('student_id', $student->id)
            ->when($activeClassId, fn ($q) => $q->where('class_id', $activeClassId))
            ->with(['schoolClass.guru:id,name', 'schoolClass.school:id,nama'])
            ->first();

        $activeClass = $enrollment?->schoolClass;

        // Hitung total XP
        $totalXp = (int) XpLedger::where('student_id', $student->id)->sum('jumlah_xp');
        $level = max(1, (int) floor($totalXp / 150) + 1);
        $currentLevelXp = $totalXp % 150;
        $nextLevelXp = 150;

        // Ambil lencana yang sudah diperoleh
        $userBadges = UserBadge::where('student_id', $student->id)
            ->with('badge')
            ->get();

        // Ambil game aktif
        $games = Game::where('aktif', true)->get();

        return Inertia::render('Student/Dashboard', [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'username' => $student->username,
                'xp' => $totalXp,
                'level' => $level,
                'current_level_xp' => $currentLevelXp,
                'next_level_xp' => $nextLevelXp,
            ],
            'activeClass' => $activeClass ? [
                'id' => $activeClass->id,
                'nama' => $activeClass->nama,
                'jenjang' => $activeClass->jenjang,
                'kode_gabung' => $activeClass->kode_gabung,
                'guru_nama' => $activeClass->guru?->name,
                'sekolah_nama' => $activeClass->school?->nama,
                'leaderboard_visible' => (bool) $activeClass->leaderboard_visible,
            ] : null,
            'badges' => $userBadges,
            'games' => $games,
        ]);
    }
}
