import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import {
    Users,
    UserPlus,
    KeyRound,
    Lock,
    Shield,
    GraduationCap,
    School as SchoolIcon,
    Search,
    Filter,
    Edit2,
    Trash2,
    X,
    Check,
    AlertTriangle,
    RefreshCw,
    Sparkles,
} from 'lucide-react';

interface SchoolOption {
    id: number;
    nama: string;
    jenjang: string;
}

interface UserItem {
    id: number;
    name: string;
    email: string | null;
    username: string | null;
    role: 'admin' | 'guru' | 'siswa';
    school_id: number | null;
    school: SchoolOption | null;
    created_at: string;
}

interface PaginatedUsers {
    data: UserItem[];
    total: number;
    current_page: number;
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Props {
    users: PaginatedUsers;
    schools: SchoolOption[];
    filters: {
        role?: string;
        search?: string;
        school_id?: string | number;
    };
    currentAdminId: number;
}

export default function UsersIndex({ users, schools, filters, currentAdminId }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [selectedRole, setSelectedRole] = useState(filters.role || '');
    const [selectedSchool, setSelectedSchool] = useState(filters.school_id?.toString() || '');

    // Modals
    const [isCreateOpen, setIsCreateOpen] = useState(false);
    const [editUser, setEditUser] = useState<UserItem | null>(null);
    const [resetPinUser, setResetPinUser] = useState<UserItem | null>(null);
    const [resetPasswordUser, setResetPasswordUser] = useState<UserItem | null>(null);
    const [deleteUser, setDeleteUser] = useState<UserItem | null>(null);

    // Form: Create User (Admin / Guru)
    const createForm = useForm({
        name: '',
        email: '',
        role: 'guru' as 'admin' | 'guru',
        password: '',
        school_id: '' as string | number,
    });

    // Form: Edit User
    const editForm = useForm({
        name: '',
        email: '',
        school_id: '' as string | number,
    });

    // Form: Reset PIN Siswa
    const pinForm = useForm({
        new_pin: '',
    });

    // Form: Reset Password Guru / Admin
    const passwordForm = useForm({
        new_password: '',
    });

    // Apply Filter Search
    const applyFilter = (newRole = selectedRole, newSchool = selectedSchool, newSearch = search) => {
        router.get(
            '/admin/pengguna',
            {
                role: newRole || undefined,
                school_id: newSchool || undefined,
                search: newSearch || undefined,
            },
            { preserveState: true, replace: true }
        );
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        applyFilter();
    };

    // Open Edit Modal
    const openEdit = (u: UserItem) => {
        setEditUser(u);
        editForm.setData({
            name: u.name,
            email: u.email || '',
            school_id: u.school_id || '',
        });
    };

    // Open Reset PIN Modal
    const openResetPin = (u: UserItem) => {
        setResetPinUser(u);
        pinForm.setData({ new_pin: '1234' });
    };

    // Open Reset Password Modal
    const openResetPassword = (u: UserItem) => {
        setResetPasswordUser(u);
        passwordForm.setData({ new_password: 'EduGame' + Math.floor(1000 + Math.random() * 9000) });
    };

    // Submit Create
    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        createForm.post('/admin/pengguna', {
            onSuccess: () => {
                setIsCreateOpen(false);
                createForm.reset();
            },
        });
    };

    // Submit Edit
    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!editUser) return;
        editForm.put(`/admin/pengguna/${editUser.id}`, {
            onSuccess: () => {
                setEditUser(null);
                editForm.reset();
            },
        });
    };

    // Submit Reset PIN
    const handleResetPinSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!resetPinUser) return;
        pinForm.post(`/admin/pengguna/${resetPinUser.id}/reset-pin`, {
            onSuccess: () => {
                setResetPinUser(null);
                pinForm.reset();
            },
        });
    };

    // Submit Reset Password
    const handleResetPasswordSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!resetPasswordUser) return;
        passwordForm.post(`/admin/pengguna/${resetPasswordUser.id}/reset-password`, {
            onSuccess: () => {
                setResetPasswordUser(null);
                passwordForm.reset();
            },
        });
    };

    // Submit Delete
    const handleDelete = () => {
        if (!deleteUser) return;
        router.delete(`/admin/pengguna/${deleteUser.id}`, {
            onSuccess: () => setDeleteUser(null),
        });
    };

    const generateRandomPin = () => {
        const random = Math.floor(1000 + Math.random() * 9000).toString();
        pinForm.setData('new_pin', random);
    };

    return (
        <AdminLayout title="Manajemen Pengguna">
            <div className="space-y-6">
                {/* Header Section */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-slate-900 via-slate-850 to-slate-900 p-6 rounded-3xl border border-indigo-500/20 shadow-xl">
                    <div className="flex items-center gap-4">
                        <div className="w-14 h-14 rounded-2xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center shadow-lg shadow-indigo-500/10">
                            <Users className="w-7 h-7" />
                        </div>
                        <div>
                            <h1 className="text-2xl font-black text-white tracking-tight flex items-center gap-2">
                                Tata Kelola Pengguna
                                <span className="text-xs px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-bold">
                                    {users.total} Akun Terdaftar
                                </span>
                            </h1>
                            <p className="text-sm text-slate-400 mt-0.5">
                                Kelola hak akses Superadmin, Akun Guru Sekolah, dan Reset Kredensial / PIN Siswa.
                            </p>
                        </div>
                    </div>

                    <button
                        onClick={() => setIsCreateOpen(true)}
                        className="flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-500/25 transition active:scale-95"
                    >
                        <UserPlus className="w-4 h-4" />
                        Tambah Admin / Guru
                    </button>
                </div>

                {/* Filter Tabs & Search Bar */}
                <div className="bg-slate-950/60 p-4 rounded-2xl border border-slate-800 flex flex-col lg:flex-row items-center justify-between gap-4">
                    {/* Role Tabs */}
                    <div className="flex items-center gap-1.5 p-1 bg-slate-900 rounded-xl border border-slate-800 w-full lg:w-auto overflow-x-auto">
                        {[
                            { id: '', label: 'Semua Akun' },
                            { id: 'admin', label: 'Superadmin' },
                            { id: 'guru', label: 'Guru' },
                            { id: 'siswa', label: 'Siswa' },
                        ].map((tab) => (
                            <button
                                key={tab.id}
                                onClick={() => {
                                    setSelectedRole(tab.id);
                                    applyFilter(tab.id, selectedSchool, search);
                                }}
                                className={`px-4 py-2 rounded-lg text-xs font-bold whitespace-nowrap transition ${
                                    selectedRole === tab.id
                                        ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30'
                                        : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'
                                }`}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>

                    {/* Filters & Search */}
                    <div className="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                        {/* School Filter Dropdown */}
                        <div className="relative w-full sm:w-60">
                            <SchoolIcon className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                            <select
                                value={selectedSchool}
                                onChange={(e) => {
                                    setSelectedSchool(e.target.value);
                                    applyFilter(selectedRole, e.target.value, search);
                                }}
                                className="w-full pl-9 pr-8 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-medium text-slate-200 focus:outline-none focus:border-indigo-500 appearance-none"
                            >
                                <option value="">Semua Sekolah</option>
                                {schools.map((s) => (
                                    <option key={s.id} value={s.id}>
                                        {s.nama} ({s.jenjang})
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Search Input */}
                        <form onSubmit={handleSearchSubmit} className="relative w-full sm:w-64">
                            <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                            <input
                                type="text"
                                placeholder="Cari nama, email, username..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="w-full pl-9 pr-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-medium text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500"
                            />
                        </form>
                    </div>
                </div>

                {/* Table of Users */}
                <div className="bg-slate-950/80 rounded-3xl border border-slate-800/80 shadow-2xl overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm text-slate-300">
                            <thead className="bg-slate-900/80 border-b border-slate-800 text-[11px] font-black uppercase tracking-wider text-slate-400">
                                <tr>
                                    <th className="py-4 px-6">Identitas Pengguna</th>
                                    <th className="py-4 px-6">Peran (Role)</th>
                                    <th className="py-4 px-6">Sekolah Asal</th>
                                    <th className="py-4 px-6">Tgl Terdaftar</th>
                                    <th className="py-4 px-6 text-right">Tindakan Cepat</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60 font-medium">
                                {users.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={5} className="py-12 text-center text-slate-500">
                                            Tidak ada data pengguna yang sesuai dengan filter pencarian.
                                        </td>
                                    </tr>
                                ) : (
                                    users.data.map((u) => {
                                        const isSelf = u.id === currentAdminId;
                                        return (
                                            <tr key={u.id} className="hover:bg-slate-900/40 transition">
                                                <td className="py-4 px-6">
                                                    <div className="flex items-center gap-3">
                                                        <div
                                                            className={`w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm shrink-0 shadow-md ${
                                                                u.role === 'admin'
                                                                    ? 'bg-gradient-to-tr from-amber-500 to-rose-500 text-white shadow-orange-500/20'
                                                                    : u.role === 'guru'
                                                                    ? 'bg-gradient-to-tr from-indigo-500 to-sky-500 text-white shadow-indigo-500/20'
                                                                    : 'bg-gradient-to-tr from-emerald-500 to-teal-500 text-white shadow-emerald-500/20'
                                                            }`}
                                                        >
                                                            {u.role === 'admin' ? (
                                                                <Shield className="w-5 h-5" />
                                                            ) : u.role === 'guru' ? (
                                                                <GraduationCap className="w-5 h-5" />
                                                            ) : (
                                                                u.name.charAt(0).toUpperCase()
                                                            )}
                                                        </div>
                                                        <div>
                                                            <div className="font-bold text-white flex items-center gap-2">
                                                                {u.name}
                                                                {isSelf && (
                                                                    <span className="text-[10px] px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 font-black">
                                                                        Anda
                                                                    </span>
                                                                )}
                                                            </div>
                                                            <div className="text-xs text-slate-400 mt-0.5">
                                                                {u.role === 'siswa'
                                                                    ? `@${u.username || 'siswa_' + u.id}`
                                                                    : u.email}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                {/* Role Badge */}
                                                <td className="py-4 px-6">
                                                    {u.role === 'admin' && (
                                                        <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/30 text-xs font-bold">
                                                            <Shield className="w-3.5 h-3.5" />
                                                            Superadmin
                                                        </span>
                                                    )}
                                                    {u.role === 'guru' && (
                                                        <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/30 text-xs font-bold">
                                                            <GraduationCap className="w-3.5 h-3.5" />
                                                            Guru Pengajar
                                                        </span>
                                                    )}
                                                    {u.role === 'siswa' && (
                                                        <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 text-xs font-bold">
                                                            <span className="w-2 h-2 rounded-full bg-emerald-400"></span>
                                                            Siswa
                                                        </span>
                                                    )}
                                                </td>

                                                {/* Sekolah */}
                                                <td className="py-4 px-6">
                                                    {u.school ? (
                                                        <div className="text-xs">
                                                            <div className="text-slate-200 font-semibold">{u.school.nama}</div>
                                                            <div className="text-slate-500 text-[11px]">{u.school.jenjang}</div>
                                                        </div>
                                                    ) : (
                                                        <span className="text-xs text-slate-500 italic">Pusat / Tanpa Sekolah</span>
                                                    )}
                                                </td>

                                                {/* Tgl Terdaftar */}
                                                <td className="py-4 px-6 text-xs text-slate-400">
                                                    {new Date(u.created_at).toLocaleDateString('id-ID', {
                                                        day: 'numeric',
                                                        month: 'short',
                                                        year: 'numeric',
                                                    })}
                                                </td>

                                                {/* Action Buttons */}
                                                <td className="py-4 px-6 text-right">
                                                    <div className="flex items-center justify-end gap-1.5">
                                                        {u.role === 'siswa' ? (
                                                            <button
                                                                onClick={() => openResetPin(u)}
                                                                title="Reset PIN Siswa (4-Digit)"
                                                                className="px-2.5 py-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/25 text-xs font-bold flex items-center gap-1 transition"
                                                            >
                                                                <KeyRound className="w-3.5 h-3.5" />
                                                                Reset PIN
                                                            </button>
                                                        ) : (
                                                            <button
                                                                onClick={() => openResetPassword(u)}
                                                                title="Reset Password Pengguna"
                                                                className="px-2.5 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/25 text-xs font-bold flex items-center gap-1 transition"
                                                            >
                                                                <Lock className="w-3.5 h-3.5" />
                                                                Sandi
                                                            </button>
                                                        )}

                                                        <button
                                                            onClick={() => openEdit(u)}
                                                            title="Edit Informasi Pengguna"
                                                            className="p-1.5 rounded-xl bg-slate-800 hover:bg-indigo-600 text-slate-300 hover:text-white transition"
                                                        >
                                                            <Edit2 className="w-4 h-4" />
                                                        </button>

                                                        {!isSelf && (
                                                            <button
                                                                onClick={() => setDeleteUser(u)}
                                                                title="Hapus Pengguna"
                                                                className="p-1.5 rounded-xl bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white transition"
                                                            >
                                                                <Trash2 className="w-4 h-4" />
                                                            </button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {users.links && users.links.length > 3 && (
                        <div className="p-4 bg-slate-900/60 border-t border-slate-800 flex items-center justify-between">
                            <div className="text-xs text-slate-400">
                                Menampilkan halaman <span className="font-bold text-white">{users.current_page}</span> dari{' '}
                                <span className="font-bold text-white">{users.last_page}</span> (Total {users.total} Pengguna)
                            </div>
                            <div className="flex items-center gap-1">
                                {users.links.map((link, idx) => (
                                    <Link
                                        key={idx}
                                        href={link.url || '#'}
                                        preserveState
                                        className={`px-3 py-1.5 rounded-xl text-xs font-bold transition ${
                                            link.active
                                                ? 'bg-indigo-600 text-white'
                                                : link.url
                                                ? 'bg-slate-800 text-slate-300 hover:bg-slate-700'
                                                : 'text-slate-600 pointer-events-none'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Modal 1: Tambah Pengguna Baru (Admin / Guru) */}
            {isCreateOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-3xl p-6 shadow-2xl relative">
                        <button
                            onClick={() => setIsCreateOpen(false)}
                            className="absolute top-5 right-5 text-slate-400 hover:text-white"
                        >
                            <X className="w-5 h-5" />
                        </button>

                        <div className="flex items-center gap-3 mb-5">
                            <div className="w-10 h-10 rounded-2xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center">
                                <UserPlus className="w-5 h-5" />
                            </div>
                            <div>
                                <h3 className="text-lg font-black text-white">Tambah Akun Pengguna</h3>
                                <p className="text-xs text-slate-400">Daftarkan akun Superadmin atau Guru Sekolah baru</p>
                            </div>
                        </div>

                        <form onSubmit={handleCreateSubmit} className="space-y-4">
                            <div>
                                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Peran (Role) Pengguna
                                </label>
                                <div className="grid grid-cols-2 gap-3">
                                    <button
                                        type="button"
                                        onClick={() => createForm.setData('role', 'guru')}
                                        className={`p-3 rounded-2xl border text-left flex items-center gap-3 transition ${
                                            createForm.data.role === 'guru'
                                                ? 'bg-indigo-600/20 border-indigo-500 text-indigo-200 shadow-sm'
                                                : 'bg-slate-950/40 border-slate-800 text-slate-400 hover:border-slate-700'
                                        }`}
                                    >
                                        <GraduationCap className="w-5 h-5 text-indigo-400" />
                                        <div>
                                            <div className="font-bold text-xs text-white">Guru Pengajar</div>
                                            <div className="text-[10px] text-slate-400">Akses dashboard kelas & soal</div>
                                        </div>
                                    </button>

                                    <button
                                        type="button"
                                        onClick={() => createForm.setData('role', 'admin')}
                                        className={`p-3 rounded-2xl border text-left flex items-center gap-3 transition ${
                                            createForm.data.role === 'admin'
                                                ? 'bg-amber-600/20 border-amber-500 text-amber-200 shadow-sm'
                                                : 'bg-slate-950/40 border-slate-800 text-slate-400 hover:border-slate-700'
                                        }`}
                                    >
                                        <Shield className="w-5 h-5 text-amber-400" />
                                        <div>
                                            <div className="font-bold text-xs text-white">Superadmin</div>
                                            <div className="text-[10px] text-slate-400">Akses penuh kendali sistem</div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Nama Lengkap
                                </label>
                                <input
                                    type="text"
                                    required
                                    placeholder="Contoh: Dra. Siti Nurhaliza, M.Pd"
                                    value={createForm.data.name}
                                    onChange={(e) => createForm.setData('name', e.target.value)}
                                    className="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-2xl text-sm text-white focus:outline-none focus:border-indigo-500"
                                />
                                {createForm.errors.name && (
                                    <p className="text-xs text-rose-400 mt-1">{createForm.errors.name}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Alamat Email (Digunakan untuk Login)
                                </label>
                                <input
                                    type="email"
                                    required
                                    placeholder="nama@sekolah.sch.id"
                                    value={createForm.data.email}
                                    onChange={(e) => createForm.setData('email', e.target.value)}
                                    className="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-2xl text-sm text-white focus:outline-none focus:border-indigo-500"
                                />
                                {createForm.errors.email && (
                                    <p className="text-xs text-rose-400 mt-1">{createForm.errors.email}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Kata Sandi (Minimal 6 Karakter)
                                </label>
                                <input
                                    type="password"
                                    required
                                    placeholder="••••••••"
                                    value={createForm.data.password}
                                    onChange={(e) => createForm.setData('password', e.target.value)}
                                    className="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-2xl text-sm text-white focus:outline-none focus:border-indigo-500"
                                />
                                {createForm.errors.password && (
                                    <p className="text-xs text-rose-400 mt-1">{createForm.errors.password}</p>
                                )}
                            </div>

                            {createForm.data.role === 'guru' && (
                                <div>
                                    <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                        Asosiasi Sekolah
                                    </label>
                                    <select
                                        value={createForm.data.school_id}
                                        onChange={(e) => createForm.setData('school_id', e.target.value)}
                                        className="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-2xl text-sm text-white focus:outline-none focus:border-indigo-500"
                                    >
                                        <option value="">Pilih Sekolah (Opsional)</option>
                                        {schools.map((s) => (
                                            <option key={s.id} value={s.id}>
                                                {s.nama} ({s.jenjang})
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            )}

                            <div className="pt-3 flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    onClick={() => setIsCreateOpen(false)}
                                    className="px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={createForm.processing}
                                    className="px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition disabled:opacity-50"
                                >
                                    {createForm.processing ? 'Menyimpan...' : 'Simpan Akun'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal 2: Edit Data Pengguna */}
            {editUser && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-3xl p-6 shadow-2xl relative">
                        <button
                            onClick={() => setEditUser(null)}
                            className="absolute top-5 right-5 text-slate-400 hover:text-white"
                        >
                            <X className="w-5 h-5" />
                        </button>

                        <div className="flex items-center gap-3 mb-5">
                            <div className="w-10 h-10 rounded-2xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center">
                                <Edit2 className="w-5 h-5" />
                            </div>
                            <div>
                                <h3 className="text-lg font-black text-white">Edit Data Pengguna</h3>
                                <p className="text-xs text-slate-400">
                                    Memperbarui profil akun {editUser.name} ({editUser.role})
                                </p>
                            </div>
                        </div>

                        <form onSubmit={handleEditSubmit} className="space-y-4">
                            <div>
                                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Nama Lengkap
                                </label>
                                <input
                                    type="text"
                                    required
                                    value={editForm.data.name}
                                    onChange={(e) => editForm.setData('name', e.target.value)}
                                    className="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-2xl text-sm text-white focus:outline-none focus:border-indigo-500"
                                />
                            </div>

                            {editUser.role !== 'siswa' && (
                                <div>
                                    <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                        Email Login
                                    </label>
                                    <input
                                        type="email"
                                        required
                                        value={editForm.data.email}
                                        onChange={(e) => editForm.setData('email', e.target.value)}
                                        className="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-2xl text-sm text-white focus:outline-none focus:border-indigo-500"
                                    />
                                </div>
                            )}

                            <div>
                                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Asosiasi Sekolah
                                </label>
                                <select
                                    value={editForm.data.school_id}
                                    onChange={(e) => editForm.setData('school_id', e.target.value)}
                                    className="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-2xl text-sm text-white focus:outline-none focus:border-indigo-500"
                                >
                                    <option value="">Pusat / Tanpa Sekolah</option>
                                    {schools.map((s) => (
                                        <option key={s.id} value={s.id}>
                                            {s.nama} ({s.jenjang})
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="pt-3 flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    onClick={() => setEditUser(null)}
                                    className="px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={editForm.processing}
                                    className="px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition disabled:opacity-50"
                                >
                                    {editForm.processing ? 'Menyimpan...' : 'Perbarui Data'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal 3: Reset PIN Siswa (4-Digit) */}
            {resetPinUser && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="bg-slate-900 border border-emerald-500/30 w-full max-w-md rounded-3xl p-6 shadow-2xl relative">
                        <button
                            onClick={() => setResetPinUser(null)}
                            className="absolute top-5 right-5 text-slate-400 hover:text-white"
                        >
                            <X className="w-5 h-5" />
                        </button>

                        <div className="flex items-center gap-3 mb-4">
                            <div className="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center">
                                <KeyRound className="w-6 h-6" />
                            </div>
                            <div>
                                <h3 className="text-lg font-black text-white">Reset PIN 4-Digit Siswa</h3>
                                <p className="text-xs text-slate-400">
                                    Untuk siswa <span className="text-emerald-400 font-bold">{resetPinUser.name}</span>
                                </p>
                            </div>
                        </div>

                        <div className="p-3 bg-emerald-950/40 border border-emerald-500/20 rounded-2xl text-xs text-emerald-200/90 mb-4">
                            💡 Siswa menggunakan PIN 4-digit ini bersama nama/username mereka untuk login di beranda
                            pembelajaran.
                        </div>

                        <form onSubmit={handleResetPinSubmit} className="space-y-4">
                            <div>
                                <div className="flex items-center justify-between mb-1.5">
                                    <label className="text-xs font-bold uppercase tracking-wider text-slate-400">
                                        PIN Baru (4 Digit Angka)
                                    </label>
                                    <button
                                        type="button"
                                        onClick={generateRandomPin}
                                        className="text-xs font-bold text-indigo-400 hover:text-indigo-300 flex items-center gap-1"
                                    >
                                        <Sparkles className="w-3.5 h-3.5" />
                                        Acak PIN
                                    </button>
                                </div>
                                <input
                                    type="text"
                                    maxLength={4}
                                    pattern="[0-9]{4}"
                                    required
                                    placeholder="Contoh: 1234"
                                    value={pinForm.data.new_pin}
                                    onChange={(e) => {
                                        const val = e.target.value.replace(/\D/g, '').slice(0, 4);
                                        pinForm.setData('new_pin', val);
                                    }}
                                    className="w-full text-center tracking-[0.5em] text-2xl font-black px-4 py-3 bg-slate-950 border border-slate-800 rounded-2xl text-emerald-400 focus:outline-none focus:border-emerald-500 font-mono"
                                />
                                {pinForm.errors.new_pin && (
                                    <p className="text-xs text-rose-400 mt-1">{pinForm.errors.new_pin}</p>
                                )}
                            </div>

                            {/* Quick picks */}
                            <div className="flex items-center justify-center gap-2">
                                {['1234', '0000', '1111', '9999'].map((preset) => (
                                    <button
                                        key={preset}
                                        type="button"
                                        onClick={() => pinForm.setData('new_pin', preset)}
                                        className="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-mono font-bold transition"
                                    >
                                        {preset}
                                    </button>
                                ))}
                            </div>

                            <div className="pt-3 flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    onClick={() => setResetPinUser(null)}
                                    className="px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={pinForm.processing || pinForm.data.new_pin.length !== 4}
                                    className="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition disabled:opacity-50"
                                >
                                    {pinForm.processing ? 'Menyimpan...' : 'Simpan PIN Baru'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal 4: Reset Password Guru / Admin */}
            {resetPasswordUser && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="bg-slate-900 border border-amber-500/30 w-full max-w-md rounded-3xl p-6 shadow-2xl relative">
                        <button
                            onClick={() => setResetPasswordUser(null)}
                            className="absolute top-5 right-5 text-slate-400 hover:text-white"
                        >
                            <X className="w-5 h-5" />
                        </button>

                        <div className="flex items-center gap-3 mb-4">
                            <div className="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center">
                                <Lock className="w-6 h-6" />
                            </div>
                            <div>
                                <h3 className="text-lg font-black text-white">Reset Kata Sandi</h3>
                                <p className="text-xs text-slate-400">
                                    Untuk pengguna <span className="text-amber-400 font-bold">{resetPasswordUser.name}</span>
                                </p>
                            </div>
                        </div>

                        <form onSubmit={handleResetPasswordSubmit} className="space-y-4">
                            <div>
                                <div className="flex items-center justify-between mb-1.5">
                                    <label className="text-xs font-bold uppercase tracking-wider text-slate-400">
                                        Kata Sandi Baru
                                    </label>
                                    <button
                                        type="button"
                                        onClick={() =>
                                            passwordForm.setData(
                                                'new_password',
                                                'EduGame' + Math.floor(1000 + Math.random() * 9000)
                                            )
                                        }
                                        className="text-xs font-bold text-amber-400 hover:text-amber-300 flex items-center gap-1"
                                    >
                                        <RefreshCw className="w-3.5 h-3.5" />
                                        Buat Otomatis
                                    </button>
                                </div>
                                <input
                                    type="text"
                                    required
                                    placeholder="Minimal 6 karakter"
                                    value={passwordForm.data.new_password}
                                    onChange={(e) => passwordForm.setData('new_password', e.target.value)}
                                    className="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-2xl text-sm font-mono text-amber-300 focus:outline-none focus:border-amber-500"
                                />
                                {passwordForm.errors.new_password && (
                                    <p className="text-xs text-rose-400 mt-1">{passwordForm.errors.new_password}</p>
                                )}
                            </div>

                            <div className="pt-3 flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    onClick={() => setResetPasswordUser(null)}
                                    className="px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={passwordForm.processing || passwordForm.data.new_password.length < 6}
                                    className="px-5 py-2.5 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-lg shadow-amber-600/30 transition disabled:opacity-50"
                                >
                                    {passwordForm.processing ? 'Menyimpan...' : 'Perbarui Sandi'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal 5: Konfirmasi Hapus */}
            {deleteUser && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="bg-slate-900 border border-rose-500/30 w-full max-w-md rounded-3xl p-6 shadow-2xl relative text-center">
                        <div className="w-14 h-14 rounded-3xl bg-rose-500/20 text-rose-400 border border-rose-500/30 flex items-center justify-center mx-auto mb-4">
                            <AlertTriangle className="w-7 h-7" />
                        </div>
                        <h3 className="text-lg font-black text-white">Hapus Akun Pengguna?</h3>
                        <p className="text-xs text-slate-400 mt-1">
                            Akun <span className="font-bold text-white">{deleteUser.name}</span> ({deleteUser.role}) akan
                            dihapus permanen dari platform EduGame.
                        </p>

                        <div className="mt-6 flex items-center justify-center gap-3">
                            <button
                                onClick={() => setDeleteUser(null)}
                                className="px-5 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition"
                            >
                                Batalkan
                            </button>
                            <button
                                onClick={handleDelete}
                                className="px-5 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-lg shadow-rose-600/30 transition"
                            >
                                Ya, Hapus Akun
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
