<?php

namespace App\Filament\Pages;

use App\Models\GameSession;
use App\Models\School;
use App\Models\User;
use App\Models\XpLedger;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Response;

class GlobalReports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Laporan & Analitik';

    protected static ?string $navigationLabel = 'Laporan Analitik Global';

    protected static ?string $title = 'Laporan Analitik Lintas Sekolah';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.global-reports';

    public array $summaryMetrics = [];
    public array $schoolsData = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $totalSchools = School::count();
        $totalStudents = User::where('role', 'siswa')->count();
        $totalTeachers = User::where('role', 'guru')->count();
        $totalSessions = GameSession::count();
        $totalXp = (int) XpLedger::sum('jumlah_xp');

        $this->summaryMetrics = [
            'total_schools' => $totalSchools,
            'total_students' => $totalStudents,
            'total_teachers' => $totalTeachers,
            'total_sessions' => $totalSessions,
            'total_xp' => $totalXp,
        ];

        $schools = School::withCount(['classes', 'users'])
            ->with(['classes.gameSessions'])
            ->get();

        $this->schoolsData = $schools->map(function ($school) {
            $sessionsCount = 0;
            foreach ($school->classes as $class) {
                $sessionsCount += $class->gameSessions->count();
            }

            return [
                'id' => $school->id,
                'nama' => $school->nama,
                'jenjang' => $school->jenjang,
                'status_lisensi' => $school->status_lisensi,
                'classes_count' => $school->classes_count,
                'students_count' => $school->users_count,
                'sessions_count' => $sessionsCount,
            ];
        })->toArray();
    }

    public function exportCsv()
    {
        $csvHeader = ['ID Sekolah', 'Nama Sekolah', 'Jenjang', 'Status Lisensi', 'Total Kelas', 'Total Siswa', 'Total Sesi Game'];

        $callback = function () use ($csvHeader) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $csvHeader);

            foreach ($this->schoolsData as $row) {
                fputcsv($handle, [
                    $row['id'],
                    $row['nama'],
                    $row['jenjang'],
                    $row['status_lisensi'],
                    $row['classes_count'],
                    $row['students_count'],
                    $row['sessions_count'],
                ]);
            }

            fclose($handle);
        };

        $filename = 'laporan_global_edugame_' . date('Y_m_d_His') . '.csv';

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
