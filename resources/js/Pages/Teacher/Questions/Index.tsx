import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import TeacherLayout from '@/Layouts/TeacherLayout';
import {
    PlusCircle,
    Upload,
    Download,
    Search,
    Filter,
    Edit3,
    Trash2,
    CheckCircle,
    HelpCircle,
    X,
    FileSpreadsheet,
    BookOpen,
} from 'lucide-react';

interface QuestionItem {
    id: number;
    tipe: 'pilihan_ganda' | 'isian_singkat' | 'benar_salah';
    teks: string;
    opsi_json: Record<string, string> | string[] | null;
    jawaban: string;
    penjelasan: string;
    kesulitan: 'mudah' | 'sedang' | 'sulit';
    versi: number;
    created_at: string;
    topic?: {
        id: number;
        nama: string;
        subject?: {
            id: number;
            nama: string;
        };
    };
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedQuestions {
    data: QuestionItem[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    links: PaginationLink[];
}

interface SubjectItem {
    id: number;
    nama: string;
    topics: Array<{ id: number; nama: string }>;
}

interface TopicItem {
    id: number;
    subject_id: number;
    nama: string;
}

interface Props {
    questions: PaginatedQuestions;
    filters: {
        search?: string;
        subject_id?: string;
        topic_id?: string;
        tipe?: string;
        kesulitan?: string;
    };
    subjects: SubjectItem[];
    topics: TopicItem[];
}

export default function Index({ questions, filters, subjects, topics }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [isImportModalOpen, setIsImportModalOpen] = useState(false);

    const { data: importData, setData: setImportData, post: postImport, processing: importProcessing, errors: importErrors, reset: resetImport } = useForm({
        file_csv: null as File | null,
        default_topic_id: topics[0]?.id || '',
    });

    const handleFilterChange = (key: string, val: string) => {
        router.get(
            '/guru/soal',
            { ...filters, [key]: val || undefined },
            { preserveState: true, replace: true }
        );
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/guru/soal', { ...filters, search: search || undefined }, { preserveState: true });
    };

    const handleDelete = (id: number, text: string) => {
        if (confirm(`Apakah Anda yakin ingin menghapus soal:\n"${text.substring(0, 50)}..."?`)) {
            router.delete(`/guru/soal/${id}`);
        }
    };

    const handleImportSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        postImport('/guru/soal/import-csv', {
            onSuccess: () => {
                setIsImportModalOpen(false);
                resetImport();
            },
        });
    };

    const getDifficultyBadge = (level: string) => {
        switch (level) {
            case 'mudah':
                return <span className="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Mudah</span>;
            case 'sedang':
                return <span className="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Sedang</span>;
            case 'sulit':
                return <span className="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Sulit</span>;
            default:
                return null;
        }
    };

    const getTypeBadge = (type: string) => {
        switch (type) {
            case 'pilihan_ganda':
                return <span className="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Pilihan Ganda</span>;
            case 'isian_singkat':
                return <span className="px-2 py-0.5 rounded text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">Isian Singkat</span>;
            case 'benar_salah':
                return <span className="px-2 py-0.5 rounded text-[11px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">Benar / Salah</span>;
            default:
                return null;
        }
    };

    return (
        <TeacherLayout>
            <Head title="Bank Soal EduGame" />

            {/* Header */}
            <div className="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Bank Soal
                    </h1>
                    <p className="text-sm text-slate-500 mt-1">
                        Kelola pertanyaan kuis dan materi game edukasi untuk seluruh mata pelajaran.
                    </p>
                </div>

                <div className="flex items-center gap-2.5">
                    <button
                        type="button"
                        onClick={() => setIsImportModalOpen(true)}
                        className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition"
                    >
                        <Upload className="w-4 h-4 text-slate-500" />
                        <span>Impor CSV</span>
                    </button>

                    <Link
                        href="/guru/soal/create"
                        className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm transition"
                    >
                        <PlusCircle className="w-4 h-4" />
                        <span>Tambah Soal Baru</span>
                    </Link>
                </div>
            </div>

            {/* Search & Filters */}
            <div className="bg-white rounded-2xl border border-slate-200 p-4 mb-6 shadow-xs">
                <form onSubmit={handleSearchSubmit} className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    {/* Search Input */}
                    <div className="relative lg:col-span-2">
                        <span className="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <Search className="w-4 h-4" />
                        </span>
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Cari teks soal atau pembahasan..."
                            className="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-100 text-xs outline-none"
                        />
                    </div>

                    {/* Filter Mata Pelajaran */}
                    <div>
                        <select
                            value={filters.subject_id || ''}
                            onChange={(e) => handleFilterChange('subject_id', e.target.value)}
                            className="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white text-slate-700 focus:border-indigo-600 outline-none"
                        >
                            <option value="">Semua Mapel</option>
                            {subjects.map((sub) => (
                                <option key={sub.id} value={sub.id}>{sub.nama}</option>
                            ))}
                        </select>
                    </div>

                    {/* Filter Tipe Soal */}
                    <div>
                        <select
                            value={filters.tipe || ''}
                            onChange={(e) => handleFilterChange('tipe', e.target.value)}
                            className="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white text-slate-700 focus:border-indigo-600 outline-none"
                        >
                            <option value="">Semua Tipe Soal</option>
                            <option value="pilihan_ganda">Pilihan Ganda</option>
                            <option value="isian_singkat">Isian Singkat</option>
                            <option value="benar_salah">Benar / Salah</option>
                        </select>
                    </div>

                    {/* Filter Kesulitan */}
                    <div>
                        <select
                            value={filters.kesulitan || ''}
                            onChange={(e) => handleFilterChange('kesulitan', e.target.value)}
                            className="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white text-slate-700 focus:border-indigo-600 outline-none"
                        >
                            <option value="">Semua Kesulitan</option>
                            <option value="mudah">Mudah</option>
                            <option value="sedang">Sedang</option>
                            <option value="sulit">Sulit</option>
                        </select>
                    </div>
                </form>
            </div>

            {/* Questions Table / List */}
            <div className="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs mb-6">
                <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <span className="text-xs font-bold text-slate-600">
                        Total {questions.total} Soal Ditemukan
                    </span>
                    <span className="text-xs text-slate-400">
                        Halaman {questions.current_page} dari {questions.last_page}
                    </span>
                </div>

                {questions.data.length === 0 ? (
                    <div className="text-center py-16 px-4">
                        <div className="w-16 h-16 mx-auto rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-3xl mb-3">
                            📝
                        </div>
                        <h3 className="font-bold text-slate-800 text-base">Tidak Ada Soal yang Cocok</h3>
                        <p className="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-4">
                            Coba sesuaikan kata kunci pencarian atau buat soal baru untuk topik ini.
                        </p>
                        <Link
                            href="/guru/soal/create"
                            className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 transition"
                        >
                            <PlusCircle className="w-4 h-4" />
                            <span>Buat Soal Pertama</span>
                        </Link>
                    </div>
                ) : (
                    <div className="divide-y divide-slate-100">
                        {questions.data.map((q, idx) => (
                            <div key={q.id} className="p-6 hover:bg-slate-50/60 transition">
                                <div className="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-3">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <span className="text-xs font-bold text-slate-400">
                                            #{(questions.current_page - 1) * questions.per_page + idx + 1}
                                        </span>
                                        {getTypeBadge(q.tipe)}
                                        {getDifficultyBadge(q.kesulitan)}
                                        {q.topic && (
                                            <span className="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-600">
                                                {q.topic.subject?.nama} &rsaquo; {q.topic.nama}
                                            </span>
                                        )}
                                    </div>

                                    {/* Action Buttons */}
                                    <div className="flex items-center gap-1 self-end sm:self-auto">
                                        <Link
                                            href={`/guru/soal/${q.id}/edit`}
                                            className="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition"
                                            title="Edit Soal"
                                        >
                                            <Edit3 className="w-4 h-4" />
                                        </Link>
                                        <button
                                            type="button"
                                            onClick={() => handleDelete(q.id, q.teks)}
                                            className="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition"
                                            title="Hapus Soal"
                                        >
                                            <Trash2 className="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>

                                {/* Pertanyaan */}
                                <h3 className="text-base font-bold text-slate-900 mb-3 leading-relaxed">
                                    {q.teks}
                                </h3>

                                {/* Pilihan Jawaban (Jika Pilihan Ganda) */}
                                {q.tipe === 'pilihan_ganda' && q.opsi_json && (
                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-3">
                                        {Object.entries(q.opsi_json).map(([key, val]) => {
                                            const isCorrect = q.jawaban === key || q.jawaban === val;
                                            return (
                                                <div
                                                    key={key}
                                                    className={`px-3 py-2 rounded-xl text-xs font-medium flex items-center gap-2 border ${
                                                        isCorrect
                                                            ? 'bg-emerald-50 border-emerald-300 text-emerald-900 font-bold'
                                                            : 'bg-white border-slate-200 text-slate-700'
                                                    }`}
                                                >
                                                    <span className={`w-5 h-5 rounded-md flex items-center justify-center font-bold text-[10px] ${
                                                        isCorrect ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'
                                                    }`}>
                                                        {key}
                                                    </span>
                                                    <span>{val}</span>
                                                    {isCorrect && <CheckCircle className="w-3.5 h-3.5 text-emerald-600 ml-auto flex-shrink-0" />}
                                                </div>
                                            );
                                        })}
                                    </div>
                                )}

                                {/* Jawaban Benar / Salah atau Isian */}
                                {q.tipe !== 'pilihan_ganda' && (
                                    <div className="mb-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-bold">
                                        <span>Kunci Jawaban:</span>
                                        <span className="font-mono bg-emerald-100 px-2 py-0.5 rounded">{q.jawaban}</span>
                                    </div>
                                )}

                                {/* Penjelasan / Pembahasan */}
                                {q.penjelasan && (
                                    <div className="p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-xs text-slate-600">
                                        <strong className="text-slate-700 block mb-0.5">💡 Pembahasan:</strong>
                                        <p>{q.penjelasan}</p>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                )}

                {/* Pagination Controls */}
                {questions.last_page > 1 && (
                    <div className="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span className="text-slate-500">
                            Menampilkan halaman {questions.current_page} dari {questions.last_page}
                        </span>

                        <div className="flex items-center gap-1">
                            {questions.links.map((link, idx) => (
                                link.url ? (
                                    <Link
                                        key={idx}
                                        href={link.url}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                        className={`px-3 py-1.5 rounded-lg font-bold transition ${
                                            link.active
                                                ? 'bg-indigo-600 text-white'
                                                : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                                        }`}
                                    />
                                ) : (
                                    <span
                                        key={idx}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                        className="px-3 py-1.5 rounded-lg text-slate-300 font-bold"
                                    />
                                )
                            ))}
                        </div>
                    </div>
                )}
            </div>

            {/* Modal Impor CSV */}
            {isImportModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                    <div className="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-200">
                        <div className="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                            <div className="flex items-center gap-2">
                                <FileSpreadsheet className="w-5 h-5 text-indigo-600" />
                                <h3 className="text-base font-bold text-slate-900">Impor Bank Soal dari CSV</h3>
                            </div>
                            <button
                                type="button"
                                onClick={() => setIsImportModalOpen(false)}
                                className="text-slate-400 hover:text-slate-600 p-1 rounded-lg"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <form onSubmit={handleImportSubmit} className="space-y-4">
                            <p className="text-xs text-slate-500 leading-relaxed">
                                Unggah berkas CSV berisi daftar soal Anda. Format mendukung soal pilihan ganda, isian singkat, dan benar/salah.
                            </p>

                            {/* Download Template Banner */}
                            <div className="p-3.5 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-between">
                                <div>
                                    <h4 className="text-xs font-bold text-indigo-950">Belum punya formatnya?</h4>
                                    <p className="text-[11px] text-indigo-700">Unduh contoh format CSV resmi EduGame.</p>
                                </div>
                                <a
                                    href="/guru/soal/template-csv"
                                    className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition"
                                >
                                    <Download className="w-3.5 h-3.5" />
                                    <span>Unduh Template</span>
                                </a>
                            </div>

                            {/* Pilih File CSV */}
                            <div>
                                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Pilih Berkas CSV
                                </label>
                                <input
                                    type="file"
                                    accept=".csv,text/csv,text/plain"
                                    onChange={(e) => setImportData('file_csv', e.target.files ? e.target.files[0] : null)}
                                    className="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 file:cursor-pointer cursor-pointer border border-slate-300 rounded-xl p-1.5"
                                    required
                                />
                                {importErrors.file_csv && (
                                    <p className="text-xs text-rose-600 mt-1">{importErrors.file_csv}</p>
                                )}
                            </div>

                            {/* Default Topik */}
                            <div>
                                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Topik Default (Bila baris CSV tidak mencantumkan topik)
                                </label>
                                <select
                                    value={importData.default_topic_id}
                                    onChange={(e) => setImportData('default_topic_id', Number(e.target.value))}
                                    className="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white text-slate-700 focus:border-indigo-600 outline-none"
                                >
                                    {topics.map((t) => (
                                        <option key={t.id} value={t.id}>{t.nama}</option>
                                    ))}
                                </select>
                            </div>

                            {/* Action Buttons */}
                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                                <button
                                    type="button"
                                    onClick={() => setIsImportModalOpen(false)}
                                    className="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 text-xs font-bold transition"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={importProcessing || !importData.file_csv}
                                    className="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-bold shadow-sm transition"
                                >
                                    {importProcessing ? 'Mengimpor...' : 'Mulai Impor Soal'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </TeacherLayout>
    );
}
