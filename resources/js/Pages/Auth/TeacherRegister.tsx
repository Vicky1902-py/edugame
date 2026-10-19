import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { GraduationCap, ArrowLeft, Mail, Lock, User, Building2 } from 'lucide-react';

export default function TeacherRegister() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        school_name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/daftar-guru');
    };

    return (
        <div className="min-h-screen bg-slate-100 flex flex-col justify-between p-4 sm:p-6 text-slate-800">
            <Head title="Pendaftaran Akun Guru - EduGame Indonesia" />

            {/* Header */}
            <div className="max-w-md w-full mx-auto flex items-center justify-between">
                <Link
                    href="/login-guru"
                    className="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-sm shadow-sm transition"
                >
                    <ArrowLeft className="w-4 h-4" />
                    <span>Sudah Punya Akun</span>
                </Link>
                <div className="flex items-center gap-2 text-indigo-900 font-extrabold text-lg">
                    <GraduationCap className="w-6 h-6 text-indigo-600" />
                    <span>EduGame</span>
                </div>
            </div>

            {/* Card Form */}
            <div className="max-w-md w-full mx-auto my-6 bg-white rounded-3xl shadow-xl p-6 sm:p-8 border border-slate-200">
                <div className="text-center mb-6">
                    <h2 className="text-2xl font-black text-slate-800">Daftar Akun Guru</h2>
                    <p className="text-sm text-slate-500 mt-1">
                        Buat kelas game edukasi untuk murid-murid Anda secara gratis.
                    </p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-4">
                    {/* Nama Lengkap */}
                    <div>
                        <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Nama Lengkap & Gelar
                        </label>
                        <div className="relative">
                            <span className="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <User className="w-5 h-5" />
                            </span>
                            <input
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="Contoh: Budi Santoso, S.Pd."
                                className="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none text-sm transition"
                                required
                            />
                        </div>
                        {errors.name && (
                            <p className="text-xs text-rose-600 mt-1 font-semibold">{errors.name}</p>
                        )}
                    </div>

                    {/* Nama Sekolah */}
                    <div>
                        <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Nama Sekolah / Madrasah
                        </label>
                        <div className="relative">
                            <span className="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <Building2 className="w-5 h-5" />
                            </span>
                            <input
                                type="text"
                                value={data.school_name}
                                onChange={(e) => setData('school_name', e.target.value)}
                                placeholder="Contoh: SD Negeri 1 Merdeka Nusantara"
                                className="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none text-sm transition"
                                required
                            />
                        </div>
                        {errors.school_name && (
                            <p className="text-xs text-rose-600 mt-1 font-semibold">{errors.school_name}</p>
                        )}
                    </div>

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
                                className="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none text-sm transition"
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
                            Kata Sandi (Minimal 8 Karakter)
                        </label>
                        <div className="relative">
                            <span className="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <Lock className="w-5 h-5" />
                            </span>
                            <input
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="••••••••"
                                className="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none text-sm transition"
                                required
                            />
                        </div>
                        {errors.password && (
                            <p className="text-xs text-rose-600 mt-1 font-semibold">{errors.password}</p>
                        )}
                    </div>

                    {/* Konfirmasi Password */}
                    <div>
                        <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Ulangi Kata Sandi
                        </label>
                        <div className="relative">
                            <span className="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <Lock className="w-5 h-5" />
                            </span>
                            <input
                                type="password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                placeholder="••••••••"
                                className="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none text-sm transition"
                                required
                            />
                        </div>
                    </div>

                    {/* Submit Button */}
                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full min-h-[48px] rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-50 text-white font-bold text-sm shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2 mt-2"
                    >
                        {processing ? 'Mendaftarkan Akun...' : 'Daftar Sekarang'}
                    </button>
                </form>

                <div className="mt-6 text-center text-xs text-slate-500">
                    Sudah memiliki akun pengajar?{' '}
                    <Link href="/login-guru" className="font-bold text-indigo-600 hover:underline">
                        Masuk ke Sini
                    </Link>
                </div>
            </div>

            {/* Footer */}
            <div className="text-center text-xs text-slate-400">
                &copy; {new Date().getFullYear()} EduGame Indonesia &bull; Memajukan Pendidikan Digital
            </div>
        </div>
    );
}
