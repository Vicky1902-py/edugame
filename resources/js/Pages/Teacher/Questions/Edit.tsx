import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import TeacherLayout from '@/Layouts/TeacherLayout';
import { ArrowLeft, CheckCircle2, Save } from 'lucide-react';

interface QuestionItem {
    id: number;
    topic_id: number;
    tipe: 'pilihan_ganda' | 'isian_singkat' | 'benar_salah';
    teks: string;
    opsi_json: Record<string, string> | string[] | null;
    jawaban: string;
    penjelasan: string;
    kesulitan: 'mudah' | 'sedang' | 'sulit';
    versi: number;
    topic?: {
        subject_id: number;
    };
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
    question: QuestionItem;
    subjects: SubjectItem[];
    topics: TopicItem[];
}

export default function Edit({ question, subjects, topics }: Props) {
    const [selectedSubjectId, setSelectedSubjectId] = useState<number>(
        question.topic?.subject_id || subjects[0]?.id || 0
    );

    const filteredTopics = topics.filter((t) => !selectedSubjectId || t.subject_id === Number(selectedSubjectId));

    // Ekstrak opsi default
    const defaultOpsi = {
        A: (question.opsi_json as any)?.A || '',
        B: (question.opsi_json as any)?.B || '',
        C: (question.opsi_json as any)?.C || '',
        D: (question.opsi_json as any)?.D || '',
    };

    const { data, setData, put, processing, errors } = useForm({
        topic_id: question.topic_id,
        tipe: question.tipe,
        teks: question.teks,
        opsi: defaultOpsi,
        jawaban: question.jawaban,
        penjelasan: question.penjelasan || '',
        kesulitan: question.kesulitan,
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
        put(`/guru/soal/${question.id}`);
    };

    return (
        <TeacherLayout>
            <Head title={`Edit Soal #${question.id} - Bank Soal`} />

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
                <div className="mb-6 pb-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-black text-slate-900 tracking-tight">Edit Soal #{question.id}</h1>
                        <p className="text-xs text-slate-500 mt-0.5">Versi Soal: {question.versi}</p>
                    </div>
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
                                className="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-indigo-600 outline-none bg-white font-bold"
                                required
                            />
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
                            className="w-full px-4 py-2.5 rounded-2xl border border-slate-300 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-100 outline-none text-xs leading-relaxed"
                        />
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
                            <Save className="w-4 h-4" />
                            <span>{processing ? 'Menyimpan...' : 'Perbarui Soal'}</span>
                        </button>
                    </div>
                </form>
            </div>
        </TeacherLayout>
    );
}
