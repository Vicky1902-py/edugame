import React, { useState } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import TeacherLayout from '@/Layouts/TeacherLayout';
import { PlusCircle, Users, Copy, Check, Trash2, ExternalLink, X } from 'lucide-react';

interface ClassItem {
    id: number;
    nama: string;
    jenjang: 'SD' | 'SMP' | 'SMA' | 'SMK';
    kode_gabung: string;
    total_siswa: number;
    leaderboard_visible: boolean;
    created_at: string;
}

interface Props {
    classes: ClassItem[];
    teacher: {
        name: string;
        email: string;
    };
}

export default function Index({ classes }: Props) {
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [copiedCode, setCopiedCode] = useState<string | null>(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        nama: '',
        jenjang: 'SD',
        leaderboard_visible: true,
    });

    const handleCopy = (code: string) => {
        navigator.clipboard.writeText(code);
        setCopiedCode(code);
        setTimeout(() => setCopiedCode(null), 2000);
    };

    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/guru/kelas', {
            onSuccess: () => {
                reset();
                setIsCreateModalOpen(false);
            },
        });
    };

    const handleDelete = (id: number, name: string) => {
        if (confirm(`Apakah Anda yakin ingin menghapus kelas "${name}"? Data siswa dan hasil kuis kelas ini akan terhapus.`)) {
            router.delete(`/guru/kelas/${id}`);
        }
    };

    return (
        <TeacherLayout>
            <Head title="Manajemen Kelas" />

            {/* Header */}
            <div className="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Manajemen Kelas
                    </h1>
                    <p className="text-sm text-slate-500 mt-1">
                        Buat kelas dan bagikan kode gabung unik agar siswa dapat masuk dan bermain.
                    </p>
                </div>

                <button
                    type="button"
                    onClick={() => setIsCreateModalOpen(true)}
                    className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-sm transition"
                >
                    <PlusCircle className="w-4 h-4" />
                    <span>Buat Kelas Baru</span>
                </button>
            </div>

            {/* Class Cards Grid */}
            {classes.length === 0 ? (
                <div className="bg-white rounded-3xl border border-slate-200 p-12 text-center shadow-xs">
                    <div className="w-16 h-16 mx-auto rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-3xl mb-4">
                        🏫
                    </div>
                    <h3 className="text-lg font-bold text-slate-800">Belum Ada Kelas Dibuat</h3>
                    <p className="text-sm text-slate-500 max-w-md mx-auto mt-1 mb-6">
                        Buat kelas pertama Anda sekarang. Setiap kelas akan mendapatkan kode gabung 6 digit unik yang mudah dihafal murid.
                    </p>
                    <button
                        type="button"
                        onClick={() => setIsCreateModalOpen(true)}
                        className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-sm transition"
                    >
                        <PlusCircle className="w-4 h-4" />
                        <span>Mulai Buat Kelas</span>
                    </button>
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {classes.map((cls) => (
                        <div
                            key={cls.id}
                            className="bg-white rounded-2xl border border-slate-200 hover:shadow-md transition p-6 flex flex-col justify-between"
                        >
                            <div>
                                <div className="flex items-center justify-between mb-3">
                                    <span className="px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        Jenjang {cls.jenjang}
                                    </span>
                                    <button
                                        type="button"
                                        onClick={() => handleDelete(cls.id, cls.nama)}
                                        className="text-slate-400 hover:text-rose-600 p-1 rounded-lg transition"
                                        title="Hapus Kelas"
                                    >
                                        <Trash2 className="w-4 h-4" />
                                    </button>
                                </div>

                                <h3 className="text-xl font-bold text-slate-900 mb-1">{cls.nama}</h3>
                                <p className="text-xs text-slate-500 flex items-center gap-1.5 mb-5">
                                    <Users className="w-3.5 h-3.5" />
                                    <span>{cls.total_siswa} Siswa Terdaftar</span>
                                </p>

                                {/* Big Join Code Box */}
                                <div className="p-4 rounded-xl bg-slate-50 border border-slate-200/80 mb-5">
                                    <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                                        Kode Gabung Siswa
                                    </span>
                                    <div className="flex items-center justify-between">
                                        <span className="font-mono font-black text-2xl tracking-widest text-indigo-950">
                                            {cls.kode_gabung}
                                        </span>
                                        <button
                                            type="button"
                                            onClick={() => handleCopy(cls.kode_gabung)}
                                            className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-300 hover:border-indigo-500 text-xs font-bold text-slate-700 shadow-xs transition"
                                        >
                                            {copiedCode === cls.kode_gabung ? (
                                                <>
                                                    <Check className="w-3.5 h-3.5 text-emerald-600" />
                                                    <span className="text-emerald-700">Tersalin</span>
                                                </>
                                            ) : (
                                                <>
                                                    <Copy className="w-3.5 h-3.5 text-slate-500" />
                                                    <span>Salin Kode</span>
                                                </>
                                            )}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div className="pt-4 border-t border-slate-100 flex items-center justify-between">
                                <span className="text-xs text-slate-500">
                                    Leaderboard: <strong className={cls.leaderboard_visible ? 'text-emerald-600' : 'text-slate-400'}>
                                        {cls.leaderboard_visible ? 'Aktif' : 'Nonaktif'}
                                    </strong>
                                </span>
                                <Link
                                    href={`/guru/kelas/${cls.id}`}
                                    className="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800"
                                >
                                    <span>Detail Siswa</span>
                                    <ExternalLink className="w-3.5 h-3.5" />
                                </Link>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {/* Modal Buat Kelas Baru */}
            {isCreateModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                    <div className="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-200">
                        <div className="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                            <h3 className="text-lg font-bold text-slate-900">Buat Kelas Baru</h3>
                            <button
                                type="button"
                                onClick={() => setIsCreateModalOpen(false)}
                                className="text-slate-400 hover:text-slate-600 p-1 rounded-lg"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <form onSubmit={handleCreateSubmit} className="space-y-4">
                            {/* Nama Kelas */}
                            <div>
                                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Nama Kelas
                                </label>
                                <input
                                    type="text"
                                    value={data.nama}
                                    onChange={(e) => setData('nama', e.target.value)}
                                    placeholder="Contoh: Kelas 4-B, 8-A, atau X-RPL"
                                    className="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none text-sm transition"
                                    required
                                />
                                {errors.nama && (
                                    <p className="text-xs text-rose-600 mt-1">{errors.nama}</p>
                                )}
                            </div>

                            {/* Jenjang */}
                            <div>
                                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Jenjang Pendidikan
                                </label>
                                <select
                                    value={data.jenjang}
                                    onChange={(e) => setData('jenjang', e.target.value as any)}
                                    className="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none text-sm transition bg-white"
                                >
                                    <option value="SD">SD (Sekolah Dasar)</option>
                                    <option value="SMP">SMP (Sekolah Menengah Pertama)</option>
                                    <option value="SMA">SMA (Sekolah Menengah Atas)</option>
                                    <option value="SMK">SMK (Sekolah Menengah Kejuruan)</option>
                                </select>
                            </div>

                            {/* Leaderboard Toggle */}
                            <div className="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <label className="flex items-center gap-3 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={data.leaderboard_visible}
                                        onChange={(e) => setData('leaderboard_visible', e.target.checked)}
                                        className="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300"
                                    />
                                    <div>
                                        <div className="text-xs font-bold text-slate-800">Tampilkan Papan Skor (Leaderboard)</div>
                                        <div className="text-[11px] text-slate-500">
                                            Jika diaktifkan, siswa dapat melihat peringkat skor kelas secara sportif.
                                        </div>
                                    </div>
                                </label>
                            </div>

                            {/* Action Buttons */}
                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                                <button
                                    type="button"
                                    onClick={() => setIsCreateModalOpen(false)}
                                    className="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 text-xs font-bold transition"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-50 text-white text-xs font-bold shadow-sm transition"
                                >
                                    {processing ? 'Menyimpan...' : 'Simpan & Dapatkan Kode'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </TeacherLayout>
    );
}
