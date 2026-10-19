import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import TeacherLayout from '@/Layouts/TeacherLayout';
import { ArrowLeft, PlusCircle, CheckCircle2 } from 'lucide-react';

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
    subjects: SubjectItem[];
    topics: TopicItem[];
}

export default function Create({ subjects, topics }: Props) {
    const [selectedSubjectId, setSelectedSubjectId] = useState<number>(subjects[0]?.id || 0);

    const filteredTopics = topics.filter((t) => !selectedSubjectId || t.subject_id === Number(selectedSubjectId));

    const { data, setData, post, processing, errors } = useForm({
        topic_id: filteredTopics[0]?.id || topics[0]?.id || '',
        tipe: 'pilihan_ganda' as 'pilihan_ganda' | 'isian_singkat' | 'benar_salah',
        teks: '',
        opsi: {
            A: '',
            B: '',
            C: '',
            D: '',
        },
        jawaban: 'A',
        penjelasan: '',
        kesulitan: 'sedang' as 'mudah' | 'sedang' | 'sulit',
    });

    const handleTipeChange = (newType: 'pilihan_ganda' | 'isian_singkat' | 'benar_salah') => {
        setData((prev) => ({
            ...prev,
            tipe: newType,
            jawaban: newType === 'pilihan_ganda' ? 'A' : newType === 'benar_salah' ? 'Benar' : '',
        }));
    };

    const handleSubjectChange = (subjId: number) => {
        setSelectedSubjectId(subjId);
        const subTopics = topics.filter((t) => t.subject_id === subjId);
        if (subTopics.length > 0) {
            setData('topic_id', subTopics[0].id);
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/guru/soal');
    };

    return (
        <TeacherLayout>
            <Head title="Tambah Soal Baru - Bank Soal" />

            {/* Back link */}
            <div className="mb-6">
                <Link
                    href="/guru/soal"
                    className="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition"
                >
                    <ArrowLeft className="w-4 h-4" />
                    <span>Kembali ke Bank Soal</span>
                </Link>
            </div>

            {/* Form Container */}
            <div className="max-w-3xl mx-auto bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs">
                <div className="mb-6 pb-4 border-b border-slate-100">
                    <h1 className="text-2xl font-black text-slate-900 tracking-tight">Tambah Soal Baru</h1>
                    <p className="text-xs text-slate-500 mt-1">
                        Lengkapi pertanyaan, pilihan jawaban, kunci jawaban, dan pembahasan materi.
                    </p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Baris 1: Mata Pelajaran & Topik */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Mata Pelajaran
                            </label>
                            <select
                                value={selectedSubjectId}
                                onChange={(e) => handleSubjectChange(Number(e.target.value))}
                                className="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm bg-white text-slate-800 focus:border-indigo-600 outline-none"
                            >
                                {subjects.map((subj) => (
                                    <option key={subj.id} value={subj.id}>{subj.nama}</option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Topik / Materi Pembelajaran
                            </label>
                            <select
                                value={data.topic_id}
                                onChange={(e) => setData('topic_id', Number(e.target.value))}
                                className="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm bg-white text-slate-800 focus:border-indigo-600 outline-none"
                                required
                            >
                                {filteredTopics.map((top) => (
                                    <option key={top.id} value={top.id}>{top.nama}</option>
                                ))}
                            </select>
                            {errors.topic_id && <p className="text-xs text-rose-600 mt-1">{errors.topic_id}</p>}
                        </div>
                    </div>

                    {/* Baris 2: Tipe Soal & Kesulitan */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Tipe Soal
                            </label>
                            <select
                                value={data.tipe}
                                onChange={(e) => handleTipeChange(e.target.value as any)}
                                className="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm bg-white text-slate-800 focus:border-indigo-600 outline-none font-semibold"
                            >
                                <option value="pilihan_ganda">Pilihan Ganda (4 Opsi)</option>
                                <option value="isian_singkat">Isian Singkat</option>
                                <option value="benar_salah">Benar / Salah</option>
                            </select>
                        </div>

                        <div>
                            <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Tingkat Kesulitan
                            </label>
                            <div className="flex items-center gap-2">
                                {(['mudah', 'sedang', 'sulit'] as const).map((lvl) => (
                                    <button
                                        key={lvl}
                                        type="button"
                                        onClick={() => setData('kesulitan', lvl)}
                                        className={`flex-1 py-2 rounded-xl text-xs font-bold capitalize transition border ${
                                            data.kesulitan === lvl
                                                ? lvl === 'mudah'
                                                    ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs'
                                                    : lvl === 'sedang'
                                                    ? 'bg-amber-500 text-white border-amber-500 shadow-xs'
                                                    : 'bg-rose-600 text-white border-rose-600 shadow-xs'
                                                : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
                                        }`}
                                    >
                                        {lvl}
                                    </button>
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* Teks Pertanyaan */}
                    <div>
                        <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Teks Pertanyaan
                        </label>
                        <textarea
                            rows={3}
                            value={data.teks}
                            onChange={(e) => setData('teks', e.target.value)}
                            placeholder="Tuliskan teks pertanyaan di sini..."
                            className="w-full px-4 py-3 rounded-2xl border border-slate-300 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-100 outline-none text-sm leading-relaxed"
                            required
                        />
                        {errors.teks && <p className="text-xs text-rose-600 mt-1">{errors.teks}</p>}
                    </div>

                    {/* Area Pilihan Jawaban - Tipe Pilihan Ganda */}
                    {data.tipe === 'pilihan_ganda' && (
                        <div className="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                            <div className="flex items-center justify-between">
                                <label className="text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Opsi Jawaban & Kunci Jawaban
                                </label>
                                <span className="text-[11px] text-slate-500 font-semibold">
                                    Pilih radio button di kiri untuk kunci yang benar
                                </span>
                            </div>

                            {(['A', 'B', 'C', 'D'] as const).map((key) => (
                                <div key={key} className="flex items-center gap-3">
                                    <label className="flex items-center gap-2 cursor-pointer">
                                        <input
                                            type="radio"
                                            name="kunci_jawaban_radio"
                                            checked={data.jawaban === key}
                                            onChange={() => setData('jawaban', key)}
                                            className="w-4 h-4 text-emerald-600 focus:ring-emerald-500 border-slate-300"
                                        />
                                        <span className={`w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold ${
                                            data.jawaban === key ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700'
                                        }`}>
                                            {key}
                                        </span>
                                    </label>

                                    <input
                                        type="text"
                                        value={data.opsi[key]}
                                        onChange={(e) => setData('opsi', { ...data.opsi, [key]: e.target.value })}
                                        placeholder={`Teks pilihan ${key}...`}
                                        className="flex-1 px-3 py-2 rounded-xl border border-slate-300 text-xs focus:border-indigo-600 outline-none bg-white"
                                        required
                                    />
                                </div>
                            ))}
                        </div>
                    )}

                    {/* Area Pilihan Jawaban - Tipe Benar / Salah */}
                    {data.tipe === 'benar_salah' && (
                        <div className="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                            <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Tentukan Kunci Jawaban yang Benar
                            </label>
                            <div className="grid grid-cols-2 gap-3 max-w-sm">
                                {(['Benar', 'Salah'] as const).map((opt) => (
                                    <button
                                        key={opt}
                                        type="button"
                                        onClick={() => setData('jawaban', opt)}
                                        className={`py-3 rounded-xl font-bold text-sm transition flex items-center justify-center gap-2 border ${
                                            data.jawaban === opt
                                                ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs'
                                                : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'
                                        }`}
                                    >
                                        {data.jawaban === opt && <CheckCircle2 className="w-4 h-4" />}
                                        <span>{opt}</span>
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Area Pilihan Jawaban - Tipe Isian Singkat */}
                    {data.tipe === 'isian_singkat' && (
                        <div className="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                            <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Kunci Jawaban (Teks / Angka yang diharapkan)
                            </label>
                            <input
                                type="text"
                                value={data.jawaban}
                                onChange={(e) => setData('jawaban', e.target.value)}
                                placeholder="Contoh: 56, Jakarta, Oksigen"
                                className="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-indigo-600 outline-none bg-white font-bold"
                                required
                            />
                            <p className="text-[11px] text-slate-500 mt-1">
                                Sistem kuis akan mencocokkan jawaban siswa tanpa membedakan huruf besar/kecil.
                            </p>
                        </div>
                    )}

                    {/* Penjelasan / Pembahasan Jawaban */}
                    <div>
                        <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Penjelasan / Pembahasan Jawaban
                        </label>
                        <textarea
                            rows={3}
                            value={data.penjelasan}
                            onChange={(e) => setData('penjelasan', e.target.value)}
                            placeholder="Tuliskan cara pengerjaan atau alasan kenapa jawaban tersebut benar..."
                            className="w-full px-4 py-2.5 rounded-2xl border border-slate-300 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-100 outline-none text-xs leading-relaxed"
                        />
                        <p className="text-[11px] text-slate-400 mt-1">
                            Penjelasan ini akan ditampilkan kepada siswa di akhir game saat meninjau soal yang salah.
                        </p>
                    </div>

                    {/* Submit Actions */}
                    <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <Link
                            href="/guru/soal"
                            className="px-5 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 text-xs font-bold transition"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold text-xs shadow-sm transition"
                        >
                            <PlusCircle className="w-4 h-4" />
                            <span>{processing ? 'Menyimpan...' : 'Simpan ke Bank Soal'}</span>
                        </button>
                    </div>
                </form>
            </div>
        </TeacherLayout>
    );
}
