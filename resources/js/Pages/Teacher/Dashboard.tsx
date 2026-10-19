import React from 'react';
import { Head, Link } from '@inertiajs/react';
import TeacherLayout from '@/Layouts/TeacherLayout';
import { Users, BookOpen, PlusCircle, ArrowRight, Copy, Check } from 'lucide-react';

interface Props {
    teacher: {
        id: number;
        name: string;
        email: string;
    };
    stats: {
        total_classes: number;
        total_students: number;
        total_questions: number;
    };
    recentClasses: Array<{
        id: number;
        nama: string;
        jenjang: string;
        kode_gabung: string;
        enrollments_count: number;
        leaderboard_visible: boolean;
    }>;
}

export default function Dashboard({ teacher, stats, recentClasses }: Props) {
    const [copiedCode, setCopiedCode] = React.useState<string | null>(null);

    const handleCopy = (code: string) => {
        navigator.clipboard.writeText(code);
        setCopiedCode(code);
        setTimeout(() => setCopiedCode(null), 2000);
    };

    return (
        <TeacherLayout>
            <Head title="Dashboard Guru" />

            {/* Header Greeting */}
            <div className="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Selamat Datang, {teacher.name}! 👋
                    </h1>
                    <p className="text-sm text-slate-500 mt-1">
                        Berikut adalah ringkasan kelas dan aktivitas belajar siswa Anda hari ini.
                    </p>
                </div>

                <Link
                    href="/guru/kelas"
                    className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-sm transition"
                >
                    <PlusCircle className="w-4 h-4" />
                    <span>Buat Kelas Baru</span>
                </Link>
            </div>

            {/* Stat Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
                <div className="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-4">
                    <div className="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <Users className="w-6 h-6" />
                    </div>
                    <div>
                        <div className="text-2xl font-black text-slate-900">{stats.total_classes}</div>
                        <div className="text-xs font-semibold text-slate-500">Kelas Aktif</div>
                    </div>
                </div>

                <div className="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-4">
                    <div className="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <Users className="w-6 h-6" />
                    </div>
                    <div>
                        <div className="text-2xl font-black text-slate-900">{stats.total_students}</div>
                        <div className="text-xs font-semibold text-slate-500">Siswa Terdaftar</div>
                    </div>
                </div>

                <div className="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-4">
                    <div className="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <BookOpen className="w-6 h-6" />
                    </div>
                    <div>
                        <div className="text-2xl font-black text-slate-900">{stats.total_questions}</div>
                        <div className="text-xs font-semibold text-slate-500">Soal di Bank Soal</div>
                    </div>
                </div>
            </div>

            {/* Recent Classes */}
            <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                <div className="flex items-center justify-between mb-5">
                    <div>
                        <h2 className="text-lg font-bold text-slate-900">Kelas yang Anda Kelola</h2>
                        <p className="text-xs text-slate-500">Bagikan kode gabung kelas kepada murid untuk mulai bermain.</p>
                    </div>

                    <Link
                        href="/guru/kelas"
                        className="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1"
                    >
                        <span>Lihat Semua</span>
                        <ArrowRight className="w-3.5 h-3.5" />
                    </Link>
                </div>

                {recentClasses.length === 0 ? (
                    <div className="text-center py-12 border-2 border-dashed border-slate-200 rounded-2xl">
                        <div className="text-4xl mb-2">🏫</div>
                        <h3 className="font-bold text-slate-800 text-base">Belum Ada Kelas</h3>
                        <p className="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-4">
                            Mulai perjalanan edukasi interaktif dengan membuat kelas pertama Anda.
                        </p>
                        <Link
                            href="/guru/kelas"
                            className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition"
                        >
                            <PlusCircle className="w-4 h-4" />
                            Buat Kelas Sekarang
                        </Link>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {recentClasses.map((cls) => (
                            <div
                                key={cls.id}
                                className="p-5 rounded-xl border border-slate-200 hover:border-indigo-300 transition bg-slate-50/50 flex flex-col justify-between"
                            >
                                <div className="flex items-start justify-between mb-3">
                                    <div>
                                        <span className="px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-800">
                                            Jenjang {cls.jenjang}
                                        </span>
                                        <h3 className="text-base font-bold text-slate-900 mt-1">{cls.nama}</h3>
                                        <p className="text-xs text-slate-500 mt-0.5">{cls.enrollments_count} siswa terdaftar</p>
                                    </div>

                                    {/* Kode Gabung */}
                                    <div className="text-right">
                                        <span className="text-[10px] text-slate-400 font-bold uppercase block mb-0.5">Kode Gabung</span>
                                        <button
                                            type="button"
                                            onClick={() => handleCopy(cls.kode_gabung)}
                                            className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-300 hover:border-indigo-500 font-mono font-black text-sm text-indigo-950 shadow-xs transition"
                                            title="Salin Kode Kelas"
                                        >
                                            <span>{cls.kode_gabung}</span>
                                            {copiedCode === cls.kode_gabung ? (
                                                <Check className="w-3.5 h-3.5 text-emerald-600" />
                                            ) : (
                                                <Copy className="w-3.5 h-3.5 text-slate-400" />
                                            )}
                                        </button>
                                    </div>
                                </div>

                                <div className="pt-3 border-t border-slate-200/80 flex items-center justify-between text-xs">
                                    <span className="text-slate-500">
                                        Leaderboard: <strong className={cls.leaderboard_visible ? 'text-emerald-600' : 'text-slate-400'}>
                                            {cls.leaderboard_visible ? 'Aktif' : 'Disembunyikan'}
                                        </strong>
                                    </span>
                                    <Link
                                        href={`/guru/kelas/${cls.id}`}
                                        className="font-bold text-indigo-600 hover:text-indigo-800"
                                    >
                                        Kelola Siswa &rarr;
                                    </Link>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </TeacherLayout>
    );
}
