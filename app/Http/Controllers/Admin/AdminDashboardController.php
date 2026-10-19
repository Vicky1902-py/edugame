<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\Question;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\XpLedger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    /**
     * Tampilkan beranda utama pusat kendali Superadmin.
     */
    public function index(Request $request): Response
    {
        $totalSchools = School::count();
        $totalTeachers = User::where('role', 'guru')->count();
        $totalStudents = User::where('role', 'siswa')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalClasses = SchoolClass::count();
        $totalQuestions = Question::count();
        $totalSessions = GameSession::count();
        $totalXp = (int) XpLedger::sum('jumlah_xp');

        // Sesi game terbaru di seluruh platform
        $recentSessions = GameSession::with([
            'student:id,name,username',
            'game:id,nama,kode_slug',
            'schoolClass:id,nama,jenjang',
        ])
            ->latest('id')
            ->limit(8)
            ->get();

        // Pengguna terbaru mendaftar
        $recentUsers = User::with('school:id,nama')
            ->latest('id')
            ->limit(8)
            ->get(['id', 'name', 'email', 'role', 'school_id', 'created_at']);

        // Ringkasan per jenjang sekolah
        $schoolsByLevel = School::selectRaw('jenjang, count(*) as count')
            ->groupBy('jenjang')
            ->pluck('count', 'jenjang')
            ->toArray();

        return Inertia::render('Admin/Dashboard', [
            'metrics' => [
                'total_schools' => $totalSchools,
                'total_teachers' => $totalTeachers,
                'total_students' => $totalStudents,
                'total_admins' => $totalAdmins,
                'total_classes' => $totalClasses,
                'total_questions' => $totalQuestions,
                'total_sessions' => $totalSessions,
                'total_xp' => $totalXp,
            ],
            'recentSessions' => $recentSessions,
            'recentUsers' => $recentUsers,
            'schoolsByLevel' => $schoolsByLevel,
            'systemInfo' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'environment' => config('app.env'),
                'server_time' => now()->toIso8601String(),
            ],
        ]);
    }
}
