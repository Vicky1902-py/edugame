import React, { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    ShieldCheck,
    LayoutDashboard,
    School as SchoolIcon,
    Users,
    Gamepad2,
    BookOpen,
    LogOut,
    Menu,
    X,
    Bell,
    CheckCircle,
    AlertCircle,
    Sliders,
} from 'lucide-react';

interface Props {
    title?: string;
    children: React.ReactNode;
}

export default function AdminLayout({ title, children }: Props) {
    const { auth, flash, url } = usePage().props as any;
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    const handleLogout = () => {
        router.post('/guru/logout');
    };

    const navigation = [
        {
            name: 'Pusat Kendali',
            href: '/admin/dashboard',
            icon: LayoutDashboard,
            current: url === '/admin/dashboard',
        },
        {
            name: 'Manajemen Sekolah',
            href: '/admin/sekolah',
            icon: SchoolIcon,
            current: url.startsWith('/admin/sekolah'),
        },
        {
            name: 'Manajemen Pengguna',
            href: '/admin/pengguna',
            icon: Users,
            current: url.startsWith('/admin/pengguna'),
        },
        {
            name: 'Konfigurasi Game',
            href: '/admin/game',
            icon: Gamepad2,
            current: url.startsWith('/admin/game'),
        },
        {
            name: 'Bank Soal Global',
            href: '/guru/soal',
            icon: BookOpen,
            current: url.startsWith('/guru/soal'),
        },
    ];

    return (
        <div className="min-h-screen bg-slate-900 text-slate-100 flex flex-col font-sans">
            <Head title={title ? `${title} - Superadmin EduGame` : 'Superadmin - EduGame Indonesia'} />

            {/* Header Superadmin */}
            <header className="bg-slate-950/80 backdrop-blur-md border-b border-indigo-500/20 sticky top-0 z-40">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                    <div className="flex items-center gap-6">
                        <Link href="/admin/dashboard" className="flex items-center gap-2.5 group">
                            <div className="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-500 via-orange-500 to-rose-500 text-white flex items-center justify-center font-black shadow-lg shadow-orange-500/20 group-hover:scale-105 transition">
                                <ShieldCheck className="w-6 h-6" />
                            </div>
                            <div>
                                <span className="font-black text-lg text-white tracking-tight flex items-center gap-2">
                                    EduGame
                                    <span className="text-[10px] px-2 py-0.5 rounded-full bg-gradient-to-r from-amber-400 to-orange-500 text-slate-950 font-black uppercase tracking-wider">
                                        Superadmin
                                    </span>
                                </span>
                                <p className="text-[11px] text-slate-400 -mt-0.5">Pusat Kendali & Tata Kelola</p>
                            </div>
                        </Link>

                        {/* Desktop Navigation */}
                        <nav className="hidden md:flex items-center gap-1 ml-4">
                            {navigation.map((item) => {
                                const Icon = item.icon;
                                return (
                                    <Link
                                        key={item.name}
                                        href={item.href}
                                        className={`px-3.5 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition ${
                                            item.current
                                                ? 'bg-indigo-600/30 text-amber-300 border border-indigo-500/40 shadow-sm'
                                                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
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
                        <div className="hidden sm:flex flex-col text-right">
                            <span className="text-xs font-bold text-white">{auth?.user?.name || 'Administrator'}</span>
                            <span className="text-[10px] text-amber-400 font-semibold uppercase tracking-wider">
                                Level Akses Tertinggi
                            </span>
                        </div>

                        <button
                            onClick={handleLogout}
                            className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/30 text-xs font-bold transition"
                            title="Keluar dari Superadmin"
                        >
                            <LogOut className="w-3.5 h-3.5" />
                            <span className="hidden sm:inline">Keluar</span>
                        </button>

                        <button
                            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                            className="md:hidden p-2 rounded-xl bg-slate-800 text-slate-300"
                        >
                            {mobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
                        </button>
                    </div>
                </div>

                {/* Mobile Navigation Drawer */}
                {mobileMenuOpen && (
                    <div className="md:hidden border-t border-slate-800 bg-slate-950 px-4 py-3 space-y-1">
                        {navigation.map((item) => {
                            const Icon = item.icon;
                            return (
                                <Link
                                    key={item.name}
                                    href={item.href}
                                    onClick={() => setMobileMenuOpen(false)}
                                    className={`w-full px-3 py-2.5 rounded-xl text-sm font-bold flex items-center gap-3 ${
                                        item.current
                                            ? 'bg-indigo-600/40 text-amber-300'
                                            : 'text-slate-300 hover:bg-slate-800'
                                    }`}
                                >
                                    <Icon className="w-4 h-4" />
                                    <span>{item.name}</span>
                                </Link>
                            );
                        })}
                    </div>
                )}
            </header>

            {/* Flash Messages */}
            {flash?.success && (
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
                    <div className="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-sm flex items-center gap-2 shadow-lg">
                        <CheckCircle className="w-5 h-5 shrink-0" />
                        <span>{flash.success}</span>
                    </div>
                </div>
            )}

            {flash?.error && (
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
                    <div className="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-300 text-sm flex items-center gap-2 shadow-lg">
                        <AlertCircle className="w-5 h-5 shrink-0" />
                        <span>{flash.error}</span>
                    </div>
                </div>
            )}

            {/* Main Content Area */}
            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full">
                {children}
            </main>

            {/* Footer Superadmin */}
            <footer className="border-t border-slate-800 bg-slate-950 py-4 text-center text-xs text-slate-500">
                Pusat Kendali EduGame Indonesia &bull; Hak Akses Khusus Superadmin &bull; Versi 1.0.0
            </footer>
        </div>
    );
}
