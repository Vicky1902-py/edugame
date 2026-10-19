<?php

namespace App\Filament\Widgets;

use App\Models\Game;
use App\Models\GameSession;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class GamePopularityChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Game Paling Populer (Total Sesi Dimainkan)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $games = Game::withCount('sessions')
            ->orderByDesc('sessions_count')
            ->limit(6)
            ->get();

        $labels = $games->pluck('judul')->toArray();
        $data = $games->pluck('sessions_count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Sesi Game',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(14, 165, 233, 0.8)',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
