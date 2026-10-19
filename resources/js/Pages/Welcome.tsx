import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Gamepad2, GraduationCap, Trophy, Sparkles, BookOpen, Users } from 'lucide-react';

interface Props {
    appName?: string;
    auth?: {
        user?: {
            id: number;
            name: string;
            role: 'siswa' | 'guru' | 'admin';
        } | null;
    };
    stats?: {
        schools: number;
        teachers: number;
        students: number;
        questions: number;
    };
}

export default function Welcome({ appName = 'EduGame Indonesia', auth, stats }: Props) {
    return (
        <div className="min-h-screen bg-gradient-to-br from-indigo-900 via-indigo-800 to-purple-900 text-white flex flex-col justify-between">
            <Head title="Platform Gaming Edukasi Interaktif" />

            {/* Header */}
            <header className="px-6 py-5 max-w-7xl w-full mx-auto flex items-center justify-between">
                <div className="flex items-center gap-3">
                    <div className="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-400 to-yellow-300 flex items-center justify-center shadow-lg shadow-yellow-500/30 text-indigo-950 font-black text-2xl">
                        🎮
                    </div>
                    <div>
                        <h1 className="text-xl font-bold tracking-tight">{appName}</h1>
                        <p className="text-xs text-indigo-200">Belajar Asyik & Berprestasi</p>
                    </div>
                </div>

                <div className="flex items-center gap-3">
                    {auth?.user ? (
                        <Link
                            href={auth.user.role === 'guru' ? '/guru/dashboard' : '/siswa/dashboard'}
                            className="px-5 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-indigo-950 font-bold text-sm shadow transition"
                        >
                            Ke Dashboard ({auth.user.name})
                        </Link>
                    ) : (
                        <>
                            <Link
                                href="/masuk-siswa"
                                className="px-4 py-2 rounded-xl bg-indigo-700/80 hover:bg-indigo-600 text-white text-sm font-semibold border border-indigo-500/40 transition"
                            >
                                Masuk Siswa
                            </Link>
                            <Link
                                href="/login-guru"
                                className="px-4 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-indigo-950 font-bold text-sm shadow transition"
                            >
                                Area Guru
                            </Link>
                        </>
                    )}
                </div>
            </header>

            {/* Hero Section */}
            <main className="max-w-7xl mx-auto px-6 py-12 flex-1 flex flex-col justify-center items-center text-center">
                <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-yellow-300 mb-6">
                    <Sparkles className="w-4 h-4" />
                    <span>Didesain Khusus untuk Kurikulum Sekolah di Indonesia</span>
                </div>

                <h2 className="text-4xl md:text-6xl font-extrabold max-w-3xl leading-tight mb-6">
                    Belajar Lebih Seru Lewat{' '}
                    <span className="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-yellow-200 to-orange-400">
                        Petualangan Game Edukasi
                    </span>
                </h2>

                <p className="text-lg md:text-xl text-indigo-200 max-w-2xl mb-10">
                    Bantu siswa menguasai matematika & sains tanpa rasa bosan. Guru membuat kelas & bank soal dalam hitungan menit, siswa bergabung hanya dengan kode kelas!
                </p>

                {/* Call to action buttons */}
                <div className="flex flex-col sm:flex-row gap-4 w-full sm:w-auto justify-center mb-16">
                    <Link
                        href="/masuk-siswa"
                        className="inline-flex items-center justify-center gap-3 px-8 py-4 rounded-2xl bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-300 hover:to-orange-400 text-indigo-950 font-black text-lg shadow-xl shadow-orange-500/25 transition transform hover:-translate-y-0.5 active:translate-y-0"
                    >
                        <Gamepad2 className="w-6 h-6" />
                        Siswa: Mainkan Sekarang
                    </Link>

                    <Link
                        href="/login-guru"
                        className="inline-flex items-center justify-center gap-3 px-8 py-4 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-lg backdrop-blur-md border border-white/20 transition"
                    >
                        <GraduationCap className="w-6 h-6" />
                        Guru: Kelola Kelas & Soal
                    </Link>
                </div>

                {/* Feature highlights */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl w-full text-left">
                    <div className="p-6 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15">
                        <div className="w-12 h-12 rounded-xl bg-amber-400/20 text-amber-300 flex items-center justify-center mb-4">
                            <Gamepad2 className="w-6 h-6" />
                        </div>
                        <h3 className="text-lg font-bold mb-2">Balapan Perkalian</h3>
                        <p className="text-sm text-indigo-200">
                            Game balap mobil seru untuk siswa SD (kelas 4-6). Jawaban perkalian & pembagian yang tepat membuat mobil melaju kencang menyalip lawan bot.
                        </p>
                    </div>

                    <div className="p-6 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15">
                        <div className="w-12 h-12 rounded-xl bg-purple-400/20 text-purple-300 flex items-center justify-center mb-4">
                            <Users className="w-6 h-6" />
                        </div>
                        <h3 className="text-lg font-bold mb-2">Kuis Duel Real-Time</h3>
                        <p className="text-sm text-indigo-200">
                            Pertarungan adu pintar untuk siswa SMP. Berisi 2-4 pemain dengan skor kilat dan reaksi emoji seru, ramah koneksi sekolah tanpa lag.
                        </p>
                    </div>

                    <div className="p-6 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15">
                        <div className="w-12 h-12 rounded-xl bg-emerald-400/20 text-emerald-300 flex items-center justify-center mb-4">
                            <Trophy className="w-6 h-6" />
                        </div>
                        <h3 className="text-lg font-bold mb-2">Gamifikasi & Lencana</h3>
                        <p className="text-sm text-indigo-200">
                            Dapatkan XP, naiki level, buka 6 lencana prestasi, pertahankan streak harian, dan pantau leaderboard kelas secara sportif.
                        </p>
                    </div>
                </div>

                {/* Live stats */}
                {stats && (
                    <div className="mt-12 flex flex-wrap items-center justify-center gap-8 text-sm text-indigo-200/90 border-t border-white/10 pt-8 w-full max-w-4xl">
                        <div><strong className="text-white text-lg">{stats.schools}</strong> Sekolah Terdaftar</div>
                        <div><strong className="text-white text-lg">{stats.teachers}</strong> Guru Penggerak</div>
                        <div><strong className="text-white text-lg">{stats.students}</strong> Siswa Aktif</div>
                        <div><strong className="text-white text-lg">{stats.questions}</strong> Bank Soal Siap Pakai</div>
                    </div>
                )}
            </main>

            {/* Footer */}
            <footer className="px-6 py-6 border-t border-white/10 text-center text-xs text-indigo-300">
                <p>&copy; {new Date().getFullYear()} EduGame Indonesia. Dirancang ramah privasi anak (tanpa pengumpulan email/data pribadi siswa) dan dioptimalkan untuk hosting cPanel.</p>
            </footer>
        </div>
    );
}
