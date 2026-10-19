import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Trophy, Gamepad2, Award, LogOut, Sparkles, Users, Zap } from 'lucide-react';

interface BadgeItem {
    id: number;
    kode_slug: string;
    nama: string;
    deskripsi: string;
    ikon: string;
}

interface UserBadgeItem {
    id: number;
    badge: BadgeItem;
    unlocked_at: string;
}

interface GameItem {
    id: number;
    nama: string;
    kode_slug: string;
    deskripsi: string;
    jenjang: string;
    ikon: string;
}

interface Props {
    student: {
        id: number;
        name: string;
        username: string;
        xp: number;
        level: number;
        current_level_xp: number;
        next_level_xp: number;
    };
    activeClass: {
        id: number;
        nama: string;
        jenjang: string;
        kode_gabung: string;
        guru_nama: string;
        sekolah_nama: string;
        leaderboard_visible: boolean;
    } | null;
    badges: UserBadgeItem[];
    games: GameItem[];
}

export default function Dashboard({ student, activeClass, badges, games }: Props) {
    const handleLogout = () => {
        if (confirm('Apakah kamu ingin keluar dari game?')) {
            router.post('/siswa/logout');
        }
    };

    const progressPercentage = Math.min(
        100,
        Math.round((student.current_level_xp / student.next_level_xp) * 100)
    );

    return (
        <div className="min-h-screen bg-gradient-to-br from-indigo-950 via-slate-900 to-indigo-900 text-white flex flex-col justify-between">
            <Head title={`Panggung Bermain - ${student.name}`} />

            {/* Header */}
            <header className="px-4 sm:px-8 py-5 border-b border-white/10 backdrop-blur-md sticky top-0 z-20 bg-indigo-950/70">
                <div className="max-w-6xl mx-auto flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-400 to-yellow-300 text-indigo-950 flex items-center justify-center font-black text-2xl shadow-lg shadow-yellow-500/20">
                            🎮
                        </div>
                        <div>
                            <h1 className="text-lg font-black tracking-tight text-white flex items-center gap-2">
                                <span>{student.name}</span>
                                <span className="px-2 py-0.5 rounded-full bg-amber-400 text-indigo-950 text-[10px] font-black uppercase">
                                    Lv. {student.level}
                                </span>
                            </h1>
                            <p className="text-xs text-indigo-300 font-medium">
                                {activeClass ? `${activeClass.nama} • ${activeClass.sekolah_nama || 'Sekolah'}` : 'Siswa EduGame'}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        {/* XP Badge */}
                        <div className="hidden sm:flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/10 border border-white/15 text-amber-300 font-black text-sm">
                            <Zap className="w-4 h-4 fill-amber-300" />
                            <span>{student.xp} XP</span>
                        </div>

                        {/* Logout Button */}
                        <button
                            onClick={handleLogout}
                            className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/30 text-xs font-bold transition"
                            title="Keluar"
                        >
                            <LogOut className="w-3.5 h-3.5" />
                            <span className="hidden sm:inline">Keluar</span>
                        </button>
                    </div>
                </div>
            </header>

            {/* Main Content */}
            <main className="max-w-6xl w-full mx-auto px-4 sm:px-8 py-8 flex-1">
                {/* Level & XP Bar Card */}
                <div className="p-6 rounded-3xl bg-gradient-to-r from-indigo-900/80 to-purple-900/80 border border-white/15 backdrop-blur-md shadow-xl mb-8">
                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                        <div className="flex items-center gap-3">
                            <div className="w-14 h-14 rounded-2xl bg-amber-400 text-indigo-950 flex flex-col items-center justify-center font-black shadow-lg shadow-yellow-500/30">
                                <span className="text-[10px] uppercase tracking-wider font-extrabold -mb-1">Level</span>
                                <span className="text-2xl">{student.level}</span>
                            </div>
                            <div>
                                <h2 className="text-xl font-black text-white">Petualang Cilik</h2>
                                <p className="text-xs text-indigo-200">
                                    Kumpulkan XP dengan menjawab soal matematika dan sains dengan benar!
                                </p>
                            </div>
                        </div>

                        <div className="text-right">
                            <span className="text-sm font-bold text-amber-300">
                                {student.current_level_xp} / {student.next_level_xp} XP
                            </span>
                            <span className="text-xs text-indigo-300 block">Menuju Level {student.level + 1}</span>
                        </div>
                    </div>

                    {/* Progress Bar */}
                    <div className="w-full h-4 rounded-full bg-black/40 overflow-hidden border border-white/10 p-0.5">
                        <div
                            className="h-full rounded-full bg-gradient-to-r from-amber-400 via-yellow-300 to-orange-400 transition-all duration-500 shadow-sm"
                            style={{ width: `${progressPercentage}%` }}
                        />
                    </div>
                </div>

                {/* Game Selection */}
                <div className="mb-10">
                    <div className="flex items-center justify-between mb-4">
                        <div>
                            <h2 className="text-xl font-black text-white flex items-center gap-2">
                                <Gamepad2 className="w-5 h-5 text-amber-400" />
                                <span>Pilih Game Edukasimu</span>
                            </h2>
                            <p className="text-xs text-indigo-300">Pilih arena bermain sesuai tingkat tantanganmu.</p>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {/* Game 1: Balapan Perkalian */}
                        <div className="p-6 rounded-3xl bg-gradient-to-b from-blue-900/60 to-indigo-900/60 border border-blue-500/30 hover:border-amber-400/60 transition group flex flex-col justify-between">
                            <div>
                                <div className="flex items-center justify-between mb-3">
                                    <span className="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-400 text-indigo-950">
                                        SD (Kelas 4-6)
                                    </span>
                                    <span className="text-xs font-bold text-blue-300">Mode Solo vs 2 Bot</span>
                                </div>

                                <div className="text-4xl mb-3">🏎️ 💨</div>
                                <h3 className="text-2xl font-black text-white mb-2 group-hover:text-amber-300 transition">
                                    Balapan Perkalian
                                </h3>
                                <p className="text-sm text-indigo-200 mb-6">
                                    Lintasan 3 jalur melawan bot cerdas! Jawab perkalian dan pembagian secepat mungkin agar mobil balapmu melaju kencang mencapai garis akhir.
                                </p>
                            </div>

                            <Link
                                href="/siswa/game/balapan-perkalian"
                                className="w-full min-h-[50px] rounded-2xl bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-300 hover:to-orange-400 text-indigo-950 font-black text-base shadow-lg shadow-orange-500/30 flex items-center justify-center gap-2 transition transform active:scale-98"
                            >
                                <Gamepad2 className="w-5 h-5" />
                                <span>Mainkan Balapan Perkalian</span>
                            </Link>
                        </div>

                        {/* Game 2: Kuis Duel */}
                        <div className="p-6 rounded-3xl bg-gradient-to-b from-purple-900/60 to-indigo-900/60 border border-purple-500/30 hover:border-purple-400/60 transition group flex flex-col justify-between">
                            <div>
                                <div className="flex items-center justify-between mb-3">
                                    <span className="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-purple-400 text-indigo-950">
                                        SMP
                                    </span>
                                    <span className="text-xs font-bold text-purple-300">2-4 Pemain Real-Time</span>
                                </div>

                                <div className="text-4xl mb-3">⚔️ ⚡</div>
                                <h3 className="text-2xl font-black text-white mb-2 group-hover:text-purple-300 transition">
                                    Kuis Duel Teman Sekelas
                                </h3>
                                <p className="text-sm text-indigo-200 mb-6">
                                    Pertarungan sengit adu cepat! Jawab 10 soal dalam 15 detik, dapatkan bonus poin kecepatan kilat, dan kirimkan reaksi emoji seru ke teman-temanmu.
                                </p>
                            </div>

                            <Link
                                href="/siswa/game/kuis-duel"
                                className="w-full min-h-[50px] rounded-2xl bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-400 hover:to-indigo-500 text-white font-black text-base shadow-lg shadow-purple-500/30 flex items-center justify-center gap-2 transition transform active:scale-98"
                            >
                                <Users className="w-5 h-5" />
                                <span>Masuk Ruang Duel</span>
                            </Link>
                        </div>
                    </div>
                </div>

                {/* Badges Collection */}
                <div className="p-6 rounded-3xl bg-white/5 border border-white/10 backdrop-blur-md">
                    <div className="flex items-center justify-between mb-4">
                        <div className="flex items-center gap-2">
                            <Trophy className="w-5 h-5 text-yellow-400" />
                            <h3 className="text-lg font-black text-white">Lemari Lencana Prestasi</h3>
                        </div>
                        <span className="text-xs text-indigo-300 font-bold">
                            {badges.length} dari 6 Lencana Terbuka
                        </span>
                    </div>

                    <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                        {badges.map((ub) => (
                            <div
                                key={ub.id}
                                className="p-3.5 rounded-2xl bg-white/10 border border-yellow-400/40 text-center flex flex-col items-center justify-center shadow-md shadow-yellow-500/10"
                            >
                                <span className="text-3xl mb-1">{ub.badge.ikon || '🏆'}</span>
                                <h4 className="text-xs font-black text-yellow-300 line-clamp-1">{ub.badge.nama}</h4>
                                <p className="text-[10px] text-indigo-200 line-clamp-1 mt-0.5">{ub.badge.deskripsi}</p>
                            </div>
                        ))}

                        {/* Silhouette placeholder for locked badges */}
                        {Array.from({ length: Math.max(0, 6 - badges.length) }).map((_, idx) => (
                            <div
                                key={`locked-${idx}`}
                                className="p-3.5 rounded-2xl bg-black/20 border border-white/5 text-center flex flex-col items-center justify-center opacity-40"
                            >
                                <span className="text-3xl mb-1 grayscale">🔒</span>
                                <h4 className="text-xs font-bold text-slate-400">Terkunci</h4>
                                <p className="text-[10px] text-slate-500">Mainkan game</p>
                            </div>
                        ))}
                    </div>
                </div>
            </main>

            {/* Footer */}
            <footer className="px-6 py-4 border-t border-white/10 text-center text-xs text-indigo-300">
                EduGame Indonesia &bull; Belajar Asyik, Berprestasi, dan Menyenangkan!
            </footer>
        </div>
    );
}
