import React from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import {
    GraduationCap,
    LayoutDashboard,
    Users,
    BookOpen,
    BarChart3,
    LogOut,
    CheckCircle2,
    AlertCircle,
    Info,
} from 'lucide-react';

interface Props {
    title?: string;
    children: React.ReactNode;
}

export default function TeacherLayout({ title, children }: Props) {
    const { auth, flash, url } = usePage().props as any;

    const handleLogout = () => {
        router.post('/guru/logout');
    };

    const navigation = [
        { name: 'Dashboard', href: '/guru/dashboard', icon: LayoutDashboard, current: url === '/guru/dashboard' },
        { name: 'Kelas Saya', href: '/guru/kelas', icon: Users, current: url.startsWith('/guru/kelas') },
    ];

    return (
        <div className="min-h-screen bg-slate-50 text-slate-800 flex flex-col">
            {/* Top Navigation */}
            <header className="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                    <div className="flex items-center gap-8">
                        <Link href="/guru/dashboard" className="flex items-center gap-2.5">
                            <div className="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-sm">
                                <GraduationCap className="w-5 h-5" />
                            </div>
                            <div>
                                <span className="font-extrabold text-base text-slate-900 tracking-tight">EduGame</span>
                                <span className="text-xs ml-1.5 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-bold">Guru</span>
                            </div>
                        </Link>

                        <nav className="hidden md:flex items-center gap-1">
                            {navigation.map((item) => {
                                const Icon = item.icon;
                                return (
                                    <Link
                                        key={item.name}
                                        href={item.href}
                                        className={`inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold transition ${
                                            item.current
                                                ? 'bg-indigo-50 text-indigo-700'
                                                : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
                                        }`}
                                    >
                                        <Icon className="w-4 h-4" />
                                        <span>{item.name}</span>
                                    </Link>
                                );
                            })}
                        </nav>
                    </div>

                    <div className="flex items-center gap-3">
                        <div className="text-right hidden sm:block">
                            <div className="text-sm font-bold text-slate-900">{auth?.user?.name || 'Guru'}</div>
                            <div className="text-xs text-slate-500">{auth?.user?.email || 'Pengajar'}</div>
                        </div>

                        <button
                            onClick={handleLogout}
                            className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-rose-50 hover:text-rose-600 text-slate-600 text-xs font-bold transition"
                            title="Keluar dari akun"
                        >
                            <LogOut className="w-4 h-4" />
                            <span className="hidden sm:inline">Keluar</span>
                        </button>
                    </div>
                </div>
            </header>

            {/* Flash Messages */}
            <div className="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                {flash?.success && (
                    <div className="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3 shadow-xs mb-3 animate-fade-in">
                        <CheckCircle2 className="w-5 h-5 flex-shrink-0 text-emerald-600" />
                        <span className="font-medium">{flash.success}</span>
                    </div>
                )}
                {flash?.error && (
                    <div className="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3 shadow-xs mb-3 animate-fade-in">
                        <AlertCircle className="w-5 h-5 flex-shrink-0 text-rose-600" />
                        <span className="font-medium">{flash.error}</span>
                    </div>
                )}
                {flash?.message && (
                    <div className="p-4 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-800 text-sm flex items-center gap-3 shadow-xs mb-3 animate-fade-in">
                        <Info className="w-5 h-5 flex-shrink-0 text-indigo-600" />
                        <span className="font-medium">{flash.message}</span>
                    </div>
                )}
            </div>

            {/* Main Content */}
            <main className="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
                {children}
            </main>

            {/* Footer */}
            <footer className="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-400">
                EduGame Indonesia &bull; Platform Gaming Edukasi Interaktif Sekolah Dasar & Menengah
            </footer>
        </div>
    );
}
