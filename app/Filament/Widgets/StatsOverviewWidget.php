<?php

namespace App\Filament\Widgets;

use App\Models\GameSession;
use App\Models\School;
use App\Models\User;
use App\Models\XpLedger;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return Cache::remember('filament_stats_overview', 60, function () {
            $totalSchools = School::count();
            $totalTeachers = User::where('role', 'guru')->count();
            $totalStudents = User::where('role', 'siswa')->count();
            $sessionsToday = GameSession::whereDate('created_at', today())->count();
            $totalXp = (int) XpLedger::sum('jumlah_xp');

            return [
                Stat::make('Total Sekolah', number_format($totalSchools))
                    ->description('Sekolah terdaftar di platform')
                    ->descriptionIcon('heroicon-m-academic-cap')
                    ->color('primary'),

                Stat::make('Total Guru', number_format($totalTeachers))
                    ->description('Akun pendidik aktif')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('success'),

                Stat::make('Total Siswa', number_format($totalStudents))
                    ->description('Siswa terdaftar')
                    ->descriptionIcon('heroicon-m-users')
                    ->color('info'),

                Stat::make('Sesi Game Hari Ini', number_format($sessionsToday))
                    ->description('Aktivitas kuis & permainan hari ini')
                    ->descriptionIcon('heroicon-m-play')
                    ->color('warning'),

                Stat::make('Total XP Beredar', number_format($totalXp))
                    ->description('Akumulasi poin gamifikasi')
                    ->descriptionIcon('heroicon-m-sparkles')
                    ->color('success'),
            ];
        });
    }
}
