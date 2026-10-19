import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import {
    Gamepad2,
    Sliders,
    Zap,
    Heart,
    Clock,
    Trophy,
    Flame,
    Play,
    Edit3,
    CheckCircle2,
    Sparkles,
    X,
    Activity,
    Layers,
    Info,
} from 'lucide-react';

interface GameConfig {
    nyawa?: number;
    timer_per_soal?: number;
    poin_dasar?: number;
    bonus_kecepatan_maks?: number;
    bonus_streak_maks?: number;
    [key: string]: any;
}

interface GameItem {
    id: number;
    kode_slug: string;
    nama: string;
    jenjang: 'SD' | 'SMP' | 'SMA' | 'SMK';
    konfigurasi_json: GameConfig | null;
    sessions_count: number;
}

interface Props {
    games: GameItem[];
}

export default function GamesIndex({ games }: Props) {
    const [selectedGame, setSelectedGame] = useState<GameItem | null>(null);

    const configForm = useForm({
        nama: '',
        jenjang: 'SD' as 'SD' | 'SMP' | 'SMA' | 'SMK',
        konfigurasi: {
            nyawa: 3,
            timer_per_soal: 15,
            poin_dasar: 100,
            bonus_kecepatan_maks: 50,
            bonus_streak_maks: 50,
        },
    });

    const openConfigModal = (g: GameItem) => {
        setSelectedGame(g);
        const c = g.konfigurasi_json || {};
        configForm.setData({
            nama: g.nama,
            jenjang: g.jenjang,
            konfigurasi: {
                nyawa: Number(c.nyawa ?? 3),
                timer_per_soal: Number(c.timer_per_soal ?? 15),
                poin_dasar: Number(c.poin_dasar ?? 100),
                bonus_kecepatan_maks: Number(c.bonus_kecepatan_maks ?? 50),
                bonus_streak_maks: Number(c.bonus_streak_maks ?? 50),
            },
        });
    };

    const handleConfigSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedGame) return;

        configForm.put(`/admin/game/${selectedGame.id}`, {
            onSuccess: () => {
                setSelectedGame(null);
            },
        });
    };

    return (
        <AdminLayout title="Konfigurasi Modul Game">
            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-slate-900 via-indigo-950/40 to-slate-900 p-6 rounded-3xl border border-indigo-500/20 shadow-xl">
                    <div className="flex items-center gap-4">
                        <div className="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/20">
                            <Gamepad2 className="w-7 h-7" />
                        </div>
                        <div>
                            <h1 className="text-2xl font-black text-white tracking-tight flex items-center gap-2">
                                Modul Permainan Edukasi
                                <span className="text-xs px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-bold">
                                    {games.length} Modul Terpasang
                                </span>
                            </h1>
                            <p className="text-sm text-slate-400 mt-0.5">
                                Kendalikan parameter engine gameplay, batas nyawa pemain, durasi timer, dan aturan scoring server.
                            </p>
                        </div>
                    </div>
                </div>

                {/* Grid Daftar Game */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {games.map((game) => {
                        const cfg = game.konfigurasi_json || {};
                        const nyawa = cfg.nyawa ?? 3;
                        const timer = cfg.timer_per_soal ?? 15;
                        const poinDasar = cfg.poin_dasar ?? 100;
                        const bonusSpeed = cfg.bonus_kecepatan_maks ?? 50;
                        const bonusStreak = cfg.bonus_streak_maks ?? 50;

                        const isBalapan = game.kode_slug === 'balapan_perkalian';

                        return (
                            <div
                                key={game.id}
                                className="relative bg-slate-950/90 rounded-3xl border border-slate-800/80 hover:border-indigo-500/40 p-6 shadow-2xl transition duration-300 flex flex-col justify-between overflow-hidden group"
                            >
                                {/* Background glow accent */}
                                <div
                                    className={`absolute -right-20 -top-20 w-60 h-60 rounded-full blur-3xl opacity-20 pointer-events-none transition group-hover:opacity-30 ${
                                        isBalapan ? 'bg-amber-500' : 'bg-indigo-500'
                                    }`}
                                />

                                <div>
                                    <div className="flex items-start justify-between gap-4 mb-4">
                                        <div className="flex items-center gap-3">
                                            <div
                                                className={`w-12 h-12 rounded-2xl flex items-center justify-center font-black shadow-lg ${
                                                    isBalapan
                                                        ? 'bg-gradient-to-tr from-amber-500 to-orange-600 text-slate-950 shadow-orange-500/20'
                                                        : 'bg-gradient-to-tr from-indigo-500 to-purple-600 text-white shadow-indigo-500/20'
                                                }`}
                                            >
                                                <Zap className="w-6 h-6" />
                                            </div>
                                            <div>
                                                <div className="flex items-center gap-2">
                                                    <h3 className="text-xl font-black text-white">{game.nama}</h3>
                                                    <span className="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 border border-slate-700 font-mono font-bold">
                                                        {game.kode_slug}
                                                    </span>
                                                </div>
                                                <p className="text-xs text-slate-400 mt-0.5">
                                                    Target Kurikulum:{' '}
                                                    <span className="text-indigo-400 font-bold">{game.jenjang}</span>
                                                </p>
                                            </div>
                                        </div>

                                        <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 text-[11px] font-bold">
                                            <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
                                            Aktif di Sistem
                                        </span>
                                    </div>

                                    {/* Stats mini badge */}
                                    <div className="flex items-center gap-2 mb-6">
                                        <div className="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300 flex items-center gap-2 font-medium">
                                            <Activity className="w-3.5 h-3.5 text-indigo-400" />
                                            <span>
                                                Dimainkan:{' '}
                                                <strong className="text-white font-bold">{game.sessions_count}</strong> kali
                                            </span>
                                        </div>
                                    </div>

                                    {/* Parameter Engine Gameplay */}
                                    <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
                                        <div className="p-3 bg-slate-900/80 rounded-2xl border border-slate-800">
                                            <div className="flex items-center gap-1.5 text-rose-400 text-xs font-bold mb-1">
                                                <Heart className="w-3.5 h-3.5" />
                                                <span>Nyawa Player</span>
                                            </div>
                                            <div className="text-lg font-black text-white">{nyawa} HP</div>
                                            <div className="text-[10px] text-slate-500">Batas kesempatan salah</div>
                                        </div>

                                        <div className="p-3 bg-slate-900/80 rounded-2xl border border-slate-800">
                                            <div className="flex items-center gap-1.5 text-amber-400 text-xs font-bold mb-1">
                                                <Clock className="w-3.5 h-3.5" />
                                                <span>Timer Soal</span>
                                            </div>
                                            <div className="text-lg font-black text-white">{timer}s</div>
                                            <div className="text-[10px] text-slate-500">Waktu jawab per soal</div>
                                        </div>

                                        <div className="p-3 bg-slate-900/80 rounded-2xl border border-slate-800">
                                            <div className="flex items-center gap-1.5 text-indigo-400 text-xs font-bold mb-1">
                                                <Trophy className="w-3.5 h-3.5" />
                                                <span>Poin Dasar</span>
                                            </div>
                                            <div className="text-lg font-black text-white">{poinDasar}</div>
                                            <div className="text-[10px] text-slate-500">Skor jawaban benar</div>
                                        </div>

                                        <div className="p-3 bg-slate-900/80 rounded-2xl border border-slate-800">
                                            <div className="flex items-center gap-1.5 text-emerald-400 text-xs font-bold mb-1">
                                                <Zap className="w-3.5 h-3.5" />
                                                <span>Bonus Speed</span>
                                            </div>
                                            <div className="text-lg font-black text-white">+{bonusSpeed}</div>
                                            <div className="text-[10px] text-slate-500">Maksimum akselerasi</div>
                                        </div>

                                        <div className="p-3 bg-slate-900/80 rounded-2xl border border-slate-800">
                                            <div className="flex items-center gap-1.5 text-purple-400 text-xs font-bold mb-1">
                                                <Flame className="w-3.5 h-3.5" />
                                                <span>Bonus Streak</span>
                                            </div>
                                            <div className="text-lg font-black text-white">+{bonusStreak}</div>
                                            <div className="text-[10px] text-slate-500">Maksimum combo beruntun</div>
                                        </div>

                                        <div className="p-3 bg-slate-900/80 rounded-2xl border border-slate-800">
                                            <div className="flex items-center gap-1.5 text-sky-400 text-xs font-bold mb-1">
                                                <Layers className="w-3.5 h-3.5" />
                                                <span>Validasi Server</span>
                                            </div>
                                            <div className="text-lg font-black text-emerald-400 flex items-center gap-1">
                                                <CheckCircle2 className="w-4 h-4" />
                                                Aktif
                                            </div>
                                            <div className="text-[10px] text-slate-500">Anti-cheat checksum</div>
                                        </div>
                                    </div>
                                </div>

                                {/* Action Buttons */}
                                <div className="flex items-center gap-3 pt-4 border-t border-slate-800/80">
                                    <button
                                        onClick={() => openConfigModal(game)}
                                        className="flex-1 py-2.5 px-4 rounded-xl bg-indigo-600/20 hover:bg-indigo-600 text-indigo-300 hover:text-white border border-indigo-500/30 text-xs font-bold flex items-center justify-center gap-2 transition"
                                    >
                                        <Sliders className="w-4 h-4" />
                                        Atur Parameter Engine
                                    </button>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>

            {/* Modal Edit Parameter Konfigurasi */}
            {selectedGame && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="bg-slate-900 border border-indigo-500/30 w-full max-w-lg rounded-3xl p-6 shadow-2xl relative">
                        <button
                            onClick={() => setSelectedGame(null)}
                            className="absolute top-5 right-5 text-slate-400 hover:text-white"
                        >
                            <X className="w-5 h-5" />
                        </button>

                        <div className="flex items-center gap-3 mb-5">
                            <div className="w-10 h-10 rounded-2xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center">
                                <Sliders className="w-5 h-5" />
                            </div>
                            <div>
                                <h3 className="text-lg font-black text-white">Parameter Engine Gameplay</h3>
                                <p className="text-xs text-slate-400">
                                    Modul: <span className="text-indigo-400 font-bold">{selectedGame.nama}</span>
                                </p>
                            </div>
                        </div>

                        <form onSubmit={handleConfigSubmit} className="space-y-4">
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                        Nama Modul Game
                                    </label>
                                    <input
                                        type="text"
                                        required
                                        value={configForm.data.nama}
                                        onChange={(e) => configForm.setData('nama', e.target.value)}
                                        className="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                        Jenjang Kurikulum
                                    </label>
                                    <select
                                        value={configForm.data.jenjang}
                                        onChange={(e) =>
                                            configForm.setData('jenjang', e.target.value as 'SD' | 'SMP' | 'SMA' | 'SMK')
                                        }
                                        className="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500"
                                    >
                                        <option value="SD">SD (Sekolah Dasar)</option>
                                        <option value="SMP">SMP (Menengah Pertama)</option>
                                        <option value="SMA">SMA (Menengah Atas)</option>
                                        <option value="SMK">SMK (Kejuruan)</option>
                                    </select>
                                </div>
                            </div>

                            <div className="p-3 bg-indigo-950/30 border border-indigo-500/20 rounded-2xl space-y-3">
                                <div className="text-xs font-bold text-indigo-300 flex items-center gap-1.5">
                                    <Sparkles className="w-4 h-4" />
                                    Aturan Engine Server (Scoring & Rules)
                                </div>

                                <div className="grid grid-cols-2 gap-3">
                                    {/* Nyawa */}
                                    <div>
                                        <label className="block text-[11px] font-bold text-slate-400 mb-1">
                                            Nyawa Player (1 - 10 HP)
                                        </label>
                                        <input
                                            type="number"
                                            min={1}
                                            max={10}
                                            required
                                            value={configForm.data.konfigurasi.nyawa}
                                            onChange={(e) =>
                                                configForm.setData('konfigurasi', {
                                                    ...configForm.data.konfigurasi,
                                                    nyawa: parseInt(e.target.value) || 1,
                                                })
                                            }
                                            className="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white font-mono"
                                        />
                                    </div>

                                    {/* Timer */}
                                    <div>
                                        <label className="block text-[11px] font-bold text-slate-400 mb-1">
                                            Timer Soal (5 - 60 Detik)
                                        </label>
                                        <input
                                            type="number"
                                            min={5}
                                            max={60}
                                            required
                                            value={configForm.data.konfigurasi.timer_per_soal}
                                            onChange={(e) =>
                                                configForm.setData('konfigurasi', {
                                                    ...configForm.data.konfigurasi,
                                                    timer_per_soal: parseInt(e.target.value) || 5,
                                                })
                                            }
                                            className="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white font-mono"
                                        />
                                    </div>

                                    {/* Poin Dasar */}
                                    <div>
                                        <label className="block text-[11px] font-bold text-slate-400 mb-1">
                                            Poin Dasar (10 - 500 Poin)
                                        </label>
                                        <input
                                            type="number"
                                            min={10}
                                            max={500}
                                            step={10}
                                            required
                                            value={configForm.data.konfigurasi.poin_dasar}
                                            onChange={(e) =>
                                                configForm.setData('konfigurasi', {
                                                    ...configForm.data.konfigurasi,
                                                    poin_dasar: parseInt(e.target.value) || 10,
                                                })
                                            }
                                            className="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white font-mono"
                                        />
                                    </div>

                                    {/* Bonus Kecepatan Maks */}
                                    <div>
                                        <label className="block text-[11px] font-bold text-slate-400 mb-1">
                                            Bonus Speed Maks (0 - 100)
                                        </label>
                                        <input
                                            type="number"
                                            min={0}
                                            max={100}
                                            required
                                            value={configForm.data.konfigurasi.bonus_kecepatan_maks}
                                            onChange={(e) =>
                                                configForm.setData('konfigurasi', {
                                                    ...configForm.data.konfigurasi,
                                                    bonus_kecepatan_maks: parseInt(e.target.value) || 0,
                                                })
                                            }
                                            className="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white font-mono"
                                        />
                                    </div>

                                    {/* Bonus Streak Maks */}
                                    <div className="col-span-2">
                                        <label className="block text-[11px] font-bold text-slate-400 mb-1">
                                            Bonus Streak Combo Maks (0 - 100)
                                        </label>
                                        <input
                                            type="number"
                                            min={0}
                                            max={100}
                                            required
                                            value={configForm.data.konfigurasi.bonus_streak_maks}
                                            onChange={(e) =>
                                                configForm.setData('konfigurasi', {
                                                    ...configForm.data.konfigurasi,
                                                    bonus_streak_maks: parseInt(e.target.value) || 0,
                                                })
                                            }
                                            className="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white font-mono"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div className="pt-3 flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    onClick={() => setSelectedGame(null)}
                                    className="px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={configForm.processing}
                                    className="px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition disabled:opacity-50"
                                >
                                    {configForm.processing ? 'Menyimpan...' : 'Terapkan Konfigurasi'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
