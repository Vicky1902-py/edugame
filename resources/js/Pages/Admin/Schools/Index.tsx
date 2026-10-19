import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import {
    School as SchoolIcon,
    PlusCircle,
    Search,
    Filter,
    Edit3,
    Trash2,
    X,
    CheckCircle2,
    Building2,
    GraduationCap,
    Users,
} from 'lucide-react';

interface SchoolItem {
    id: number;
    nama: string;
    jenjang: 'SD' | 'SMP' | 'SMA' | 'SMK';
    status_lisensi: 'aktif' | 'nonaktif' | 'trial';
    created_at: string;
    classes_count: number;
    teachers_count: number;
    students_count: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedSchools {
    data: SchoolItem[];
    current_page: number;
    last_page: number;
    total: number;
    links: PaginationLink[];
}

interface Props {
    schools: PaginatedSchools;
    filters: {
        search?: string;
        jenjang?: string;
        status?: string;
    };
}

export default function Index({ schools, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [editingSchool, setEditingSchool] = useState<SchoolItem | null>(null);

    // Form Tambah
    const { data: createData, setData: setCreateData, post: postCreate, processing: createProcessing, errors: createErrors, reset: resetCreate } = useForm({
        nama: '',
        jenjang: 'SD' as 'SD' | 'SMP' | 'SMA' | 'SMK',
        status_lisensi: 'aktif' as 'aktif' | 'nonaktif' | 'trial',
    });

    // Form Edit
    const { data: editData, setData: setEditData, put: putEdit, processing: editProcessing, errors: editErrors } = useForm({
        nama: '',
        jenjang: 'SD' as 'SD' | 'SMP' | 'SMA' | 'SMK',
        status_lisensi: 'aktif' as 'aktif' | 'nonaktif' | 'trial',
    });

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/admin/sekolah', { ...filters, search }, { preserveState: true });
    };

    const handleFilterChange = (key: string, value: string) => {
        router.get('/admin/sekolah', { ...filters, [key]: value || undefined }, { preserveState: true });
    };

    const handleOpenEdit = (school: SchoolItem) => {
        setEditingSchool(school);
        setEditData({
            nama: school.nama,
            jenjang: school.jenjang,
            status_lisensi: school.status_lisensi,
        });
    };

    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        postCreate('/admin/sekolah', {
            onSuccess: () => {
                setIsCreateModalOpen(false);
                resetCreate();
            },
        });
    };

    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!editingSchool) return;
        putEdit(`/admin/sekolah/${editingSchool.id}`, {
            onSuccess: () => setEditingSchool(null),
        });
    };

    const handleDelete = (id: number, nama: string) => {
        if (confirm(`Yakin ingin menghapus sekolah "${nama}"? Data yang sudah dihapus tidak dapat dikembalikan.`)) {
            router.delete(`/admin/sekolah/${id}`);
        }
    };

    return (
        <AdminLayout title="Manajemen Sekolah">
            <div className="space-y-6">
                {/* Header Page */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-black text-white flex items-center gap-2.5">
                            <SchoolIcon className="w-6 h-6 text-indigo-400" />
                            <span>Manajemen Sekolah</span>
                        </h1>
                        <p className="text-xs text-slate-400 mt-1">
                            Kelola registrasi sekolah, jenjang pendidikan, dan status lisensi platform di seluruh Indonesia.
                        </p>
                    </div>

                    <button
                        onClick={() => setIsCreateModalOpen(true)}
                        className="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-300 hover:to-orange-400 text-slate-950 font-black text-xs shadow-lg shadow-orange-500/20 transition active:scale-95"
                    >
                        <PlusCircle className="w-4 h-4" />
                        <span>Tambah Sekolah Baru</span>
                    </button>
                </div>

                {/* Filter & Search Bar */}
                <div className="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-3">
                    <form onSubmit={handleSearch} className="w-full md:w-80 relative">
                        <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Cari nama sekolah..."
                            className="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"
                        />
                    </form>

                    <div className="flex flex-wrap items-center gap-2 w-full md:w-auto">
                        <select
                            value={filters.jenjang || ''}
                            onChange={(e) => handleFilterChange('jenjang', e.target.value)}
                            className="px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-xs text-slate-300 focus:outline-none"
                        >
                            <option value="">Semua Jenjang</option>
                            <option value="SD">SD</option>
                            <option value="SMP">SMP</option>
                            <option value="SMA">SMA</option>
                            <option value="SMK">SMK</option>
                        </select>

                        <select
                            value={filters.status || ''}
                            onChange={(e) => handleFilterChange('status', e.target.value)}
                            className="px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-xs text-slate-300 focus:outline-none"
                        >
                            <option value="">Semua Lisensi</option>
                            <option value="aktif">Aktif</option>
                            <option value="trial">Uji Coba (Trial)</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                {/* Tabel Sekolah */}
                <div className="rounded-3xl bg-slate-950/60 border border-slate-800 overflow-hidden shadow-xl">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-900/80 border-b border-slate-800 text-slate-400 uppercase font-black tracking-wider text-[11px]">
                                <tr>
                                    <th className="px-6 py-4">Sekolah</th>
                                    <th className="px-4 py-4">Jenjang</th>
                                    <th className="px-4 py-4">Status Lisensi</th>
                                    <th className="px-4 py-4 text-center">Kelas</th>
                                    <th className="px-4 py-4 text-center">Guru</th>
                                    <th className="px-4 py-4 text-center">Siswa</th>
                                    <th className="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800">
                                {schools.data.length > 0 ? (
                                    schools.data.map((school) => (
                                        <tr key={school.id} className="hover:bg-slate-900/50 transition">
                                            <td className="px-6 py-4 font-bold text-white flex items-center gap-2.5">
                                                <div className="w-8 h-8 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center shrink-0">
                                                    <Building2 className="w-4 h-4" />
                                                </div>
                                                <span className="text-sm">{school.nama}</span>
                                            </td>
                                            <td className="px-4 py-4">
                                                <span className="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                                    {school.jenjang}
                                                </span>
                                            </td>
                                            <td className="px-4 py-4">
                                                <span
                                                    className={`px-2.5 py-1 rounded-full text-[10px] font-black uppercase border ${
                                                        school.status_lisensi === 'aktif'
                                                            ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30'
                                                            : school.status_lisensi === 'trial'
                                                            ? 'bg-amber-500/20 text-amber-300 border-amber-500/30'
                                                            : 'bg-rose-500/20 text-rose-300 border-rose-500/30'
                                                    }`}
                                                >
                                                    {school.status_lisensi}
                                                </span>
                                            </td>
                                            <td className="px-4 py-4 text-center font-bold text-slate-300">
                                                {school.classes_count}
                                            </td>
                                            <td className="px-4 py-4 text-center font-bold text-slate-300">
                                                {school.teachers_count}
                                            </td>
                                            <td className="px-4 py-4 text-center font-bold text-slate-300">
                                                {school.students_count}
                                            </td>
                                            <td className="px-6 py-4 text-right space-x-2">
                                                <button
                                                    onClick={() => handleOpenEdit(school)}
                                                    className="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition"
                                                    title="Ubah Sekolah"
                                                >
                                                    <Edit3 className="w-3.5 h-3.5" />
                                                </button>
                                                <button
                                                    onClick={() => handleDelete(school.id, school.nama)}
                                                    className="p-1.5 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 transition"
                                                    title="Hapus Sekolah"
                                                >
                                                    <Trash2 className="w-3.5 h-3.5" />
                                                </button>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan={7} className="px-6 py-12 text-center text-slate-500">
                                            Tidak ada data sekolah yang sesuai dengan pencarian.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {schools.links.length > 3 && (
                        <div className="px-6 py-4 border-t border-slate-800 flex items-center justify-between">
                            <span className="text-xs text-slate-400">Total: {schools.total} sekolah</span>
                            <div className="flex items-center gap-1">
                                {schools.links.map((link, idx) => (
                                    <Link
                                        key={idx}
                                        href={link.url || '#'}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                        className={`px-3 py-1.5 rounded-xl text-xs font-bold transition ${
                                            link.active
                                                ? 'bg-indigo-600 text-white'
                                                : link.url
                                                ? 'bg-slate-800 text-slate-300 hover:bg-slate-700'
                                                : 'text-slate-600 cursor-not-allowed'
                                        }`}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Modal Tambah Sekolah */}
            {isCreateModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs">
                    <div className="w-full max-w-md p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl animate-fade-in">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-lg font-black text-white">Tambah Sekolah Baru</h3>
                            <button onClick={() => setIsCreateModalOpen(false)} className="text-slate-400 hover:text-white">
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <form onSubmit={handleCreateSubmit} className="space-y-4">
                            <div>
                                <label className="text-xs font-bold text-slate-300 block mb-1">Nama Sekolah</label>
                                <input
                                    type="text"
                                    value={createData.nama}
                                    onChange={(e) => setCreateData('nama', e.target.value)}
                                    placeholder="Contoh: SMP Negeri 2 Surabaya"
                                    className="w-full px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-indigo-500"
                                    required
                                />
                                {createErrors.nama && <span className="text-xs text-rose-400 mt-1">{createErrors.nama}</span>}
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="text-xs font-bold text-slate-300 block mb-1">Jenjang</label>
                                    <select
                                        value={createData.jenjang}
                                        onChange={(e) => setCreateData('jenjang', e.target.value as any)}
                                        className="w-full px-3 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none"
                                    >
                                        <option value="SD">SD</option>
                                        <option value="SMP">SMP</option>
                                        <option value="SMA">SMA</option>
                                        <option value="SMK">SMK</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="text-xs font-bold text-slate-300 block mb-1">Status Lisensi</label>
                                    <select
                                        value={createData.status_lisensi}
                                        onChange={(e) => setCreateData('status_lisensi', e.target.value as any)}
                                        className="w-full px-3 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none"
                                    >
                                        <option value="aktif">Aktif</option>
                                        <option value="trial">Uji Coba</option>
                                        <option value="nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                            </div>

                            <div className="flex items-center justify-end gap-2 pt-4">
                                <button
                                    type="button"
                                    onClick={() => setIsCreateModalOpen(false)}
                                    className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={createProcessing}
                                    className="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-bold text-white shadow-md"
                                >
                                    Simpan Sekolah
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal Edit Sekolah */}
            {editingSchool && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs">
                    <div className="w-full max-w-md p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl animate-fade-in">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-lg font-black text-white">Ubah Data Sekolah</h3>
                            <button onClick={() => setEditingSchool(null)} className="text-slate-400 hover:text-white">
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <form onSubmit={handleEditSubmit} className="space-y-4">
                            <div>
                                <label className="text-xs font-bold text-slate-300 block mb-1">Nama Sekolah</label>
                                <input
                                    type="text"
                                    value={editData.nama}
                                    onChange={(e) => setEditData('nama', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-indigo-500"
                                    required
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="text-xs font-bold text-slate-300 block mb-1">Jenjang</label>
                                    <select
                                        value={editData.jenjang}
                                        onChange={(e) => setEditData('jenjang', e.target.value as any)}
                                        className="w-full px-3 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none"
                                    >
                                        <option value="SD">SD</option>
                                        <option value="SMP">SMP</option>
                                        <option value="SMA">SMA</option>
                                        <option value="SMK">SMK</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="text-xs font-bold text-slate-300 block mb-1">Status Lisensi</label>
                                    <select
                                        value={editData.status_lisensi}
                                        onChange={(e) => setEditData('status_lisensi', e.target.value as any)}
                                        className="w-full px-3 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none"
                                    >
                                        <option value="aktif">Aktif</option>
                                        <option value="trial">Uji Coba</option>
                                        <option value="nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                            </div>

                            <div className="flex items-center justify-end gap-2 pt-4">
                                <button
                                    type="button"
                                    onClick={() => setEditingSchool(null)}
                                    className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={editProcessing}
                                    className="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-bold text-white shadow-md"
                                >
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
