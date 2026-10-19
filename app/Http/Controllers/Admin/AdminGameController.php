<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminGameController extends Controller
{
    /**
     * Tampilkan daftar modul game dan status konfigurasinya.
     */
    public function index(): Response
    {
        $games = Game::withCount('sessions')->get();

        return Inertia::render('Admin/Games/Index', [
            'games' => $games,
        ]);
    }

    /**
     * Perbarui konfigurasi game.
     */
    public function updateConfig(Request $request, int $id): RedirectResponse
    {
        $game = Game::findOrFail($id);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenjang' => ['required', 'in:SD,SMP,SMA,SMK'],
            'konfigurasi' => ['required', 'array'],
            'konfigurasi.nyawa' => ['required', 'integer', 'min:1', 'max:10'],
            'konfigurasi.timer_per_soal' => ['required', 'integer', 'min:5', 'max:60'],
            'konfigurasi.poin_dasar' => ['required', 'integer', 'min:10', 'max:500'],
            'konfigurasi.bonus_kecepatan_maks' => ['required', 'integer', 'min:0', 'max:100'],
            'konfigurasi.bonus_streak_maks' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $game->update([
            'nama' => $validated['nama'],
            'jenjang' => $validated['jenjang'],
            'konfigurasi_json' => $validated['konfigurasi'],
        ]);

        return back()->with('success', "Konfigurasi game {$game->nama} berhasil diperbarui.");
    }
}
