import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import {
    School,
    GraduationCap,
    Users,
    Shield,
    BookOpen,
    Gamepad2,
    Zap,
    TrendingUp,
    Server,
    Activity,
    ArrowUpRight,
    Sparkles,
} from 'lucide-react';

interface Props {
    metrics: {
        total_schools: number;
        total_teachers: number;
        total_students: number;
        total_admins: number;
        total_classes: number;
        total_questions: number;
        total_sessions: number;
        total_xp: number;
    };
    recentSessions: Array<{
        id: number;
        skor: number;
        akurasi: number;
        mulai: string;
        student?: { name: string; username: string };
        game?: { nama: string };
        schoolClass?: { nama: string; jenjang: string };
    }>;
    recentUsers: Array<{
        id: number;
        name: string;
        email: string | null;
        role: string;
        created_at: string;
        school?: { nama: string };
    }>;
    schoolsByLevel: Record<string, number>;
    systemInfo: {
        php_version: string;
        laravel_version: string;
        environment: string;
        server_time: string;
    };
}

export default function Dashboard({ metrics, recentSessions, recentUsers, schoolsByLevel, systemInfo }: Props) {
    const metricCards = [
        {
            label: 'Total Sekolah',
            value: metrics.total_schools,
            desc: 'Sekolah terdaftar',
            icon: School,
            color: 'from-blue-500 to-indigo-600',
            href: '/admin/sekolah',
        },
        {
            label: 'Total Guru',
            value: metrics.total_teachers,
            desc: 'Pengajar aktif',
            icon: GraduationCap,
            color: 'from-emerald-500 to-teal-600',
            href: '/admin/pengguna?role=guru',
        },
        {
            label: 'Total Siswa',
            value: metrics.total_students,
            desc: 'Pemain terdaftar',
            icon: Users,
            color: 'from-amber-500 to-orange-600',
            href: '/admin/pengguna?role=siswa',
        },
        {
            label: 'Total Kelas',
            value: metrics.total_classes,
            desc: 'Ruang kelas aktif',
            icon: BookOpen,
            color: 'from-purple-500 to-violet-600',
            href: '/admin/sekolah',
        },
        {
            label: 'Bank Soal',
            value: metrics.total_questions,
            desc: 'Soal di database',
            icon: BookOpen,
            color: 'from-pink-500 to-rose-600',
            href: '/guru/soal',
        },
        {
            label: 'Sesi Permainan',
            value: metrics.total_sessions,
            desc: 'Game dimainkan',
            icon: Gamepad2,
            color: 'from-cyan-500 to-blue-600',
            href: '/admin/game',
        },
        {
            label: 'XP Terdistribusi',
            value: `${(metrics.total_xp).toLocaleString('id-ID')} XP`,
            desc: 'Total hadiah poin',
            icon: Zap,
            color: 'from-yellow-400 to-amber-500',
            href: '#',
        },
        {
            label: 'Superadmin',
            value: metrics.total_admins,
            desc: 'Pengelola platform',
            icon: Shield,
            color: 'from-rose-500 to-red-600',
            href: '/admin/pengguna?role=admin',
        },
    ];

    return (
        <AdminLayout title="Dashboard Superadmin">
            <div className="space-y-8">
                {/* Header Welcome Card */}
                <div className="relative overflow-hidden p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-indigo-900/90 via-slate-900 to-purple-900/80 border border-indigo-500/30 shadow-2xl backdrop-blur-md">
                    <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 text-xs font-black uppercase tracking-wider border border-amber-400/30 mb-3">
                                <Sparkles className="w-3.5 h-3.5" />
                                <span>Platform Control Center</span>
                            </div>
                            <h1 className="text-2xl sm:text-3xl font-black text-white tracking-tight">
                                Pusat Kendali Superadmin EduGame
                            </h1>
                            <p className="text-sm text-slate-300 max-w-2xl mt-1">
                                Pantau kesehatan ekosistem pendidikan, tata kelola lisensi sekolah, pengguna guru & siswa, serta parameter mesin game secara terpusat.
                            </p>
                        </div>

                        {/* Quick action shortcuts */}
                        <div className="flex flex-wrap items-center gap-2.5">
                            <Link
                                href="/admin/sekolah"
                                className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5"
                            >
                                <School className="w-4 h-4" />
                                <span>Kelola Sekolah</span>
                            </Link>
                            <Link
                                href="/admin/pengguna"
                                className="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs border border-white/10 transition flex items-center gap-1.5"
                            >
                                <Users className="w-4 h-4" />
                                <span>Kelola Pengguna</span>
                            </Link>
                            <Link
                                href="/admin/game"
                                className="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-md transition flex items-center gap-1.5"
                            >
                                <Gamepad2 className="w-4 h-4" />
                                <span>Konfigurasi Game</span>
                            </Link>
                        </div>
                    </div>
                </div>

                {/* Grid 8 Kartu Metrik */}
                <div>
                    <h2 className="text-sm font-black text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <Activity className="w-4 h-4 text-indigo-400" />
                        <span>Indikator Performa Platform</span>
                    </h2>

                    <div className="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        {metricCards.map((card) => {
                            const Icon = card.icon;
                            return (
                                <Link
                                    key={card.label}
                                    href={card.href}
                                    className="p-5 rounded-3xl bg-slate-950/60 hover:bg-slate-800/80 border border-slate-800 hover:border-indigo-500/40 transition-all duration-200 group flex flex-col justify-between shadow-lg"
                                >
                                    <div className="flex items-center justify-between mb-3">
                                        <div
                                            className={`w-11 h-11 rounded-2xl bg-gradient-to-tr ${card.color} text-white flex items-center justify-center shadow-md group-hover:scale-110 transition`}
                                        >
                                            <Icon className="w-5 h-5" />
                                        </div>
                                        <ArrowUpRight className="w-4 h-4 text-slate-500 group-hover:text-amber-300 transition-colors" />
                                    </div>

                                    <div>
                                        <span className="text-2xl sm:text-3xl font-black text-white tracking-tight block">
                                            {card.value}
                                        </span>
                                        <h3 className="text-xs font-bold text-slate-300 mt-1">{card.label}</h3>
                                        <p className="text-[11px] text-slate-500 mt-0.5">{card.desc}</p>
                                    </div>
                                </Link>
                            );
                        })}
                    </div>
                </div>

                {/* 2 Kolom: Aktivitas Sesi Permainan & Pengguna Baru */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Sesi Game Terbaru */}
                    <div className="p-6 rounded-3xl bg-slate-950/60 border border-slate-800 shadow-xl flex flex-col justify-between">
                        <div>
                            <div className="flex items-center justify-between mb-4">
                                <h3 className="text-base font-black text-white flex items-center gap-2">
                                    <Gamepad2 className="w-5 h-5 text-amber-400" />
                                    <span>Sesi Permainan Siswa Terbaru</span>
                                </h3>
                                <span className="text-xs text-slate-400 font-semibold">Real-Time</span>
                            </div>

                            {recentSessions.length > 0 ? (
                                <div className="divide-y divide-slate-800">
                                    {recentSessions.map((session) => (
                                        <div key={session.id} className="py-3 flex items-center justify-between gap-3 text-xs">
                                            <div>
                                                <p className="font-bold text-white">
                                                    {session.student?.name || 'Siswa'}
                                                    <span className="text-slate-400 font-normal ml-1">
                                                        ({session.schoolClass?.nama || 'Umum'})
                                                    </span>
                                                </p>
                                                <p className="text-[11px] text-slate-400">
                                                    {session.game?.nama || 'Game'} &bull; Akurasi: {session.akurasi}%
                                                </p>
                                            </div>
                                            <div className="text-right">
                                                <span className="px-2.5 py-1 rounded-xl bg-amber-400/20 text-amber-300 font-black text-xs border border-amber-400/30">
                                                    {session.skor} Pts
                                                </span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-8 text-slate-500 text-xs">
                                    Belum ada sesi permainan yang tercatat.
                                </div>
                            )}
                        </div>

                        <div className="pt-4 border-t border-slate-800 mt-4 text-right">
                            <Link href="/admin/game" className="text-xs text-amber-400 hover:text-amber-300 font-bold">
                                Buka Pengaturan Game &rarr;
                            </Link>
                        </div>
                    </div>

                    {/* Pengguna Baru Terdaftar */}
                    <div className="p-6 rounded-3xl bg-slate-950/60 border border-slate-800 shadow-xl flex flex-col justify-between">
                        <div>
                            <div className="flex items-center justify-between mb-4">
                                <h3 className="text-base font-black text-white flex items-center gap-2">
                                    <Users className="w-5 h-5 text-indigo-400" />
                                    <span>Pengguna Baru Terdaftar</span>
                                </h3>
                                <Link href="/admin/pengguna" className="text-xs text-indigo-400 hover:text-indigo-300 font-bold">
                                    Lihat Semua &rarr;
                                </Link>
                            </div>

                            {recentUsers.length > 0 ? (
                                <div className="divide-y divide-slate-800">
                                    {recentUsers.map((user) => (
                                        <div key={user.id} className="py-3 flex items-center justify-between gap-3 text-xs">
                                            <div>
                                                <p className="font-bold text-white flex items-center gap-1.5">
                                                    <span>{user.name}</span>
                                                    <span
                                                        className={`px-1.5 py-0.5 rounded text-[10px] font-black uppercase ${
                                                            user.role === 'admin'
                                                                ? 'bg-rose-500/20 text-rose-300'
                                                                : user.role === 'guru'
                                                                ? 'bg-indigo-500/20 text-indigo-300'
                                                                : 'bg-amber-500/20 text-amber-300'
                                                        }`}
                                                    >
                                                        {user.role}
                                                    </span>
                                                </p>
                                                <p className="text-[11px] text-slate-400 truncate max-w-xs">
                                                    {user.email || user.school?.nama || 'Akun Siswa'}
                                                </p>
                                            </div>
                                            <span className="text-[11px] text-slate-500">
                                                {new Date(user.created_at).toLocaleDateString('id-ID')}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-8 text-slate-500 text-xs">
                                    Belum ada pengguna terdaftar.
                                </div>
                            )}
                        </div>

                        <div className="pt-4 border-t border-slate-800 mt-4 text-right">
                            <Link href="/admin/pengguna" className="text-xs text-indigo-400 hover:text-indigo-300 font-bold">
                                Kelola Pengguna & Reset PIN/Password &rarr;
                            </Link>
                        </div>
                    </div>
                </div>

                {/* Status Server & Lingkungan */}
                <div className="p-6 rounded-3xl bg-slate-950/40 border border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs text-slate-400">
                    <div className="flex items-center gap-2">
                        <Server className="w-4 h-4 text-emerald-400" />
                        <span className="font-bold text-slate-300">Status Server:</span>
                        <span>PHP {systemInfo.php_version}</span>
                        <span>&bull;</span>
                        <span>Laravel {systemInfo.laravel_version}</span>
                        <span>&bull;</span>
                        <span className="px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 font-bold uppercase text-[10px]">
                            {systemInfo.environment}
                        </span>
                    </div>

                    <div className="text-slate-500 text-[11px]">
                        Waktu Server: {new Date(systemInfo.server_time).toLocaleString('id-ID')}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
