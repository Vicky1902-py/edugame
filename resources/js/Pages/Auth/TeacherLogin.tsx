import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { GraduationCap, ArrowLeft, Mail, Lock, Eye, EyeOff, Sparkles } from 'lucide-react';

interface Props {
    flash?: {
        message?: string;
        error?: string;
        success?: string;
    };
}

export default function TeacherLogin({ flash }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
    });

    const [showPassword, setShowPassword] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/login-guru');
    };

    return (
        <div className="min-h-screen bg-slate-100 flex flex-col justify-between p-4 sm:p-6 text-slate-800">
            <Head title="Masuk Pengajar - EduGame Indonesia" />

            {/* Header */}
            <div className="max-w-md w-full mx-auto flex items-center justify-between">
                <Link
                    href="/"
                    className="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-sm shadow-sm transition"
                >
                    <ArrowLeft className="w-4 h-4" />
                    <span>Kembali</span>
                </Link>
                <div className="flex items-center gap-2 text-indigo-900 font-extrabold text-lg">
                    <GraduationCap className="w-6 h-6 text-indigo-600" />
                    <span>Portal Guru</span>
                </div>
            </div>

            {/* Card Form */}
            <div className="max-w-md w-full mx-auto my-6 bg-white rounded-3xl shadow-xl p-6 sm:p-8 border border-slate-200">
                <div className="text-center mb-6">
                    <div className="w-14 h-14 mx-auto rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl shadow-inner mb-3">
                        <GraduationCap className="w-7 h-7" />
                    </div>
                    <h2 className="text-2xl font-black text-slate-800">Masuk ke Ruang Guru</h2>
                    <p className="text-sm text-slate-500 mt-1">
                        Kelola kelas, bank soal, dan pantau perkembangan belajar siswa Anda.
                    </p>
                </div>

                {/* Alerts */}
                {flash?.success && (
                    <div className="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                        {flash.error}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-4">
                    {/* Email */}
                    <div>
                        <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Alamat Email
                        </label>
                        <div className="relative">
                            <span className="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <Mail className="w-5 h-5" />
                            </span>
                            <input
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder="guru@sekolah.sch.id"
                                className="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none text-sm transition"
                                required
                            />
                        </div>
                        {errors.email && (
                            <p className="text-xs text-rose-600 mt-1 font-semibold">{errors.email}</p>
                        )}
                    </div>

                    {/* Password */}
                    <div>
                        <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Kata Sandi
                        </label>
                        <div className="relative">
                            <span className="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <Lock className="w-5 h-5" />
                            </span>
                            <input
                                type={showPassword ? 'text' : 'password'}
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="••••••••"
                                className="w-full pl-11 pr-11 py-3 rounded-xl border border-slate-300 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none text-sm transition"
                                required
                            />
                            <button
                                type="button"
                                onClick={() => setShowPassword(!showPassword)}
                                className="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600"
                            >
                                {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                            </button>
                        </div>
                        {errors.password && (
                            <p className="text-xs text-rose-600 mt-1 font-semibold">{errors.password}</p>
                        )}
                    </div>

                    {/* Submit Button */}
                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full min-h-[48px] rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-50 text-white font-bold text-sm shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2"
                    >
                        {processing ? 'Memproses...' : 'Masuk Dashboard Guru'}
                    </button>
                </form>

                {/* Demo Credentials */}
                <div className="mt-6 p-3 rounded-2xl bg-indigo-50 border border-indigo-100 text-xs text-indigo-900">
                    <p className="font-bold mb-1 flex items-center gap-1 text-indigo-700">
                        <Sparkles className="w-3.5 h-3.5" />
                        Akun Guru Demo:
                    </p>
                    <p>Email: <strong className="font-mono">guru@edugame.id</strong></p>
                    <p>Password: <strong className="font-mono">password123</strong></p>
                </div>

                {/* Register Link */}
                <div className="mt-6 text-center text-xs text-slate-500">
                    Belum punya akun pengajar?{' '}
                    <Link href="/daftar-guru" className="font-bold text-indigo-600 hover:underline">
                        Daftar Akun Guru Baru
                    </Link>
                </div>
            </div>

            {/* Footer */}
            <div className="text-center text-xs text-slate-400">
                &copy; {new Date().getFullYear()} EduGame Indonesia &bull; Portal Khusus Guru & Sekolah
            </div>
        </div>
    );
}
