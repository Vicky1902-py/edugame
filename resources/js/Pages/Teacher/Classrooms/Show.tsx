import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import TeacherLayout from '@/Layouts/TeacherLayout';
import { ArrowLeft, Users, Copy, Check, Settings, Sparkles, UserCheck } from 'lucide-react';

interface StudentItem {
    id: number;
    name: string;
    username: string;
    created_at: string;
}

interface EnrollmentItem {
    id: number;
    student: StudentItem;
    created_at: string;
}

interface Classroom {
    id: number;
    nama: string;
    jenjang: string;
    kode_gabung: string;
    leaderboard_visible: boolean;
    created_at: string;
    enrollments: EnrollmentItem[];
    school?: {
        nama: string;
    };
}

interface Props {
    classroom: Classroom;
}

export default function Show({ classroom }: Props) {
    const [copiedCode, setCopiedCode] = useState(false);
    const [copiedLink, setCopiedLink] = useState(false);
    const [isSettingsOpen, setIsSettingsOpen] = useState(false);

    const { data, setData, put, processing } = useForm({
        nama: classroom.nama,
        jenjang: classroom.jenjang,
        leaderboard_visible: classroom.leaderboard_visible,
    });

    const handleCopyCode = () => {
        navigator.clipboard.writeText(classroom.kode_gabung);
        setCopiedCode(true);
        setTimeout(() => setCopiedCode(false), 2000);
    };

    const handleCopyLink = () => {
        const link = `${window.location.origin}/masuk-siswa?code=${classroom.kode_gabung}`;
        navigator.clipboard.writeText(link);
        setCopiedLink(true);
        setTimeout(() => setCopiedLink(false), 2000);
    };

    const handleUpdateSettings = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/guru/kelas/${classroom.id}`, {
            onSuccess: () => setIsSettingsOpen(false),
        });
    };

    return (
        <TeacherLayout>
            <Head title={`Kelas ${classroom.nama}`} />

            {/* Top Navigation */}
            <div className="mb-6">
                <Link
                    href="/guru/kelas"
                    className="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition"
                >
                    <ArrowLeft className="w-4 h-4" />
                    <span>Kembali ke Daftar Kelas</span>
                </Link>
            </div>

            {/* Header Banner */}
            <div className="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 mb-8 shadow-xs">
                <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div>
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                                Jenjang {classroom.jenjang}
                            </span>
                            {classroom.school && (
                                <span className="text-xs text-slate-500 font-semibold">
                                    {classroom.school.nama}
                                </span>
                            )}
                        </div>
                        <h1 className="text-3xl font-black text-slate-900 tracking-tight">{classroom.nama}</h1>
                        <p className="text-sm text-slate-500 mt-1">
                            Total {classroom.enrollments.length} siswa telah bergabung di kelas ini.
                        </p>
                    </div>

                    {/* Big Join Code & Share Box */}
                    <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div className="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-100 text-center sm:text-left flex items-center justify-between sm:justify-start gap-4">
                            <div>
                                <span className="text-[10px] uppercase tracking-wider font-bold text-indigo-600 block">
                                    Kode Gabung Siswa
                                </span>
                                <span className="font-mono font-black text-2xl tracking-widest text-indigo-950">
                                    {classroom.kode_gabung}
                                </span>
                            </div>

                            <button
                                type="button"
                                onClick={handleCopyCode}
                                className="px-3 py-2 rounded-xl bg-white border border-indigo-200 hover:bg-indigo-100 text-xs font-bold text-indigo-900 shadow-xs transition"
                            >
                                {copiedCode ? <Check className="w-4 h-4 text-emerald-600" /> : <Copy className="w-4 h-4" />}
                            </button>
                        </div>

                        <button
                            type="button"
                            onClick={handleCopyLink}
                            className="px-4 py-3 rounded-2xl bg-amber-400 hover:bg-amber-300 text-indigo-950 font-bold text-xs shadow-xs transition flex items-center justify-center gap-2"
                        >
                            {copiedLink ? <Check className="w-4 h-4 text-emerald-800" /> : <Sparkles className="w-4 h-4" />}
                            <span>{copiedLink ? 'Link Tersalin!' : 'Salin Tautan Siswa'}</span>
                        </button>

                        <button
                            type="button"
                            onClick={() => setIsSettingsOpen(true)}
                            className="p-3 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 transition"
                            title="Pengaturan Kelas"
                        >
                            <Settings className="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>

            {/* Student List Section */}
            <div className="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs">
                <div className="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 className="text-lg font-bold text-slate-900">Daftar Siswa Terdaftar</h2>
                        <p className="text-xs text-slate-500">Siswa otomatis muncul di sini setelah masuk menggunakan kode kelas.</p>
                    </div>
                    <span className="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                        {classroom.enrollments.length} Siswa
                    </span>
                </div>

                {classroom.enrollments.length === 0 ? (
                    <div className="text-center py-16 px-4">
                        <div className="w-16 h-16 mx-auto rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-3xl mb-3">
                            🎒
                        </div>
                        <h3 className="font-bold text-slate-800 text-base">Belum Ada Siswa yang Masuk</h3>
                        <p className="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-4">
                            Instruksikan murid Anda untuk membuka link atau memasukkan kode <strong className="font-mono text-indigo-900 font-bold">{classroom.kode_gabung}</strong> di halaman Masuk Siswa.
                        </p>
                        <button
                            type="button"
                            onClick={handleCopyLink}
                            className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-400 text-indigo-950 font-bold text-xs hover:bg-amber-300 transition"
                        >
                            <Copy className="w-3.5 h-3.5" />
                            Salin Tautan Masuk untuk Siswa
                        </button>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm text-slate-600">
                            <thead className="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                                <tr>
                                    <th className="px-6 py-3.5">No</th>
                                    <th className="px-6 py-3.5">Nama Panggilan Siswa</th>
                                    <th className="px-6 py-3.5">ID Sistem</th>
                                    <th className="px-6 py-3.5">Tanggal Bergabung</th>
                                    <th className="px-6 py-3.5">Status</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {classroom.enrollments.map((enr, idx) => (
                                    <tr key={enr.id} className="hover:bg-slate-50/80 transition">
                                        <td className="px-6 py-4 font-bold text-slate-400 text-xs">{idx + 1}</td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-400 to-orange-400 text-indigo-950 font-black flex items-center justify-center text-sm shadow-xs">
                                                    {enr.student.name.charAt(0).toUpperCase()}
                                                </div>
                                                <span className="font-bold text-slate-900">{enr.student.name}</span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 font-mono text-xs text-slate-400">
                                            {enr.student.username}
                                        </td>
                                        <td className="px-6 py-4 text-xs text-slate-500">
                                            {new Date(enr.created_at).toLocaleDateString('id-ID', {
                                                day: 'numeric',
                                                month: 'long',
                                                year: 'numeric',
                                            })}
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <UserCheck className="w-3 h-3" />
                                                <span>Aktif</span>
                                            </span>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            {/* Modal Edit Settings */}
            {isSettingsOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                    <div className="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                        <h3 className="text-lg font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">
                            Pengaturan Kelas {classroom.nama}
                        </h3>

                        <form onSubmit={handleUpdateSettings} className="space-y-4">
                            <div>
                                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Nama Kelas
                                </label>
                                <input
                                    type="text"
                                    value={data.nama}
                                    onChange={(e) => setData('nama', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-600 outline-none text-sm transition"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Jenjang
                                </label>
                                <select
                                    value={data.jenjang}
                                    onChange={(e) => setData('jenjang', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-600 outline-none text-sm transition bg-white"
                                >
                                    <option value="SD">SD</option>
                                    <option value="SMP">SMP</option>
                                    <option value="SMA">SMA</option>
                                    <option value="SMK">SMK</option>
                                </select>
                            </div>

                            <div className="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <label className="flex items-center gap-3 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={data.leaderboard_visible}
                                        onChange={(e) => setData('leaderboard_visible', e.target.checked)}
                                        className="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300"
                                    />
                                    <div>
                                        <div className="text-xs font-bold text-slate-800">Tampilkan Leaderboard Kelas</div>
                                        <div className="text-[11px] text-slate-500">
                                            Guru bisa menyembunyikan papan skor jika diperlukan.
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                                <button
                                    type="button"
                                    onClick={() => setIsSettingsOpen(false)}
                                    className="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 text-xs font-bold transition"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition"
                                >
                                    {processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </TeacherLayout>
    );
}
