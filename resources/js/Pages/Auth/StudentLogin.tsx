import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { Gamepad2, ArrowLeft, KeyRound, Sparkles, AlertCircle } from 'lucide-react';

interface Props {
    initialCode?: string;
    flash?: {
        message?: string;
        error?: string;
    };
}

export default function StudentLogin({ initialCode = '', flash }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        kode_kelas: initialCode || '',
        nama_panggilan: '',
        pin: '',
    });

    const [pinDisplay, setPinDisplay] = useState('');

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/masuk-siswa');
    };

    const handleKeypadPress = (num: string) => {
        if (data.pin.length < 4) {
            const newPin = data.pin + num;
            setData('pin', newPin);
            setPinDisplay('*'.repeat(newPin.length));
        }
    };

    const handleKeypadBackspace = () => {
        const newPin = data.pin.slice(0, -1);
        setData('pin', newPin);
        setPinDisplay('*'.repeat(newPin.length));
    };

    const handleKeypadClear = () => {
        setData('pin', '');
        setPinDisplay('');
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-amber-400 via-orange-500 to-rose-600 flex flex-col justify-between p-4 sm:p-6 text-slate-800">
            <Head title="Masuk Siswa - EduGame Indonesia" />

            {/* Header */}
            <div className="max-w-md w-full mx-auto flex items-center justify-between">
                <Link
                    href="/"
                    className="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/20 hover:bg-white/30 text-white font-bold text-sm backdrop-blur transition"
                >
                    <ArrowLeft className="w-4 h-4" />
                    <span>Beranda</span>
                </Link>
                <div className="flex items-center gap-2 text-white font-extrabold text-lg">
                    <span>🎮 EduGame Siswa</span>
                </div>
            </div>

            {/* Login Card */}
            <div className="max-w-md w-full mx-auto my-6 bg-white rounded-3xl shadow-2xl p-6 sm:p-8 border-4 border-yellow-300">
                <div className="text-center mb-6">
                    <div className="w-16 h-16 mx-auto rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-3xl shadow-inner mb-3">
                        🚀
                    </div>
                    <h2 className="text-2xl font-black text-slate-800">Ayo Mulai Bermain!</h2>
                    <p className="text-sm text-slate-500 mt-1">
                        Masukkan kode kelas dari gurumu, nama panggilan, dan 4 angka PIN rahasiamu.
                    </p>
                </div>

                {/* Alerts */}
                {flash?.error && (
                    <div className="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm flex items-center gap-2">
                        <AlertCircle className="w-5 h-5 flex-shrink-0" />
                        <span>{flash.error}</span>
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-4">
                    {/* Kode Kelas */}
                    <div>
                        <label className="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1">
                            Kode Kelas (6 Huruf/Angka)
                        </label>
                        <input
                            type="text"
                            maxLength={10}
                            value={data.kode_kelas}
                            onChange={(e) => setData('kode_kelas', e.target.value.toUpperCase())}
                            placeholder="Contoh: JUARA5"
                            className="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none font-bold text-lg text-center tracking-widest uppercase transition"
                            required
                        />
                        {errors.kode_kelas && (
                            <p className="text-xs text-rose-600 mt-1 font-semibold">{errors.kode_kelas}</p>
                        )}
                    </div>

                    {/* Nama Panggilan */}
                    <div>
                        <label className="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1">
                            Nama Panggilanmu
                        </label>
                        <input
                            type="text"
                            maxLength={30}
                            value={data.nama_panggilan}
                            onChange={(e) => setData('nama_panggilan', e.target.value)}
                            placeholder="Contoh: Budi, Siti, Edo"
                            className="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none font-bold text-base transition"
                            required
                        />
                        {errors.nama_panggilan && (
                            <p className="text-xs text-rose-600 mt-1 font-semibold">{errors.nama_panggilan}</p>
                        )}
                    </div>

                    {/* PIN 4 Angka */}
                    <div>
                        <div className="flex items-center justify-between mb-1">
                            <label className="text-xs font-black uppercase tracking-wider text-slate-600">
                                PIN 4 Angka (Ingat baik-baik ya!)
                            </label>
                            <span className="text-xs text-slate-400 font-semibold">Tepat 4 angka</span>
                        </div>
                        <div className="relative">
                            <input
                                type="password"
                                maxLength={4}
                                inputMode="numeric"
                                value={data.pin}
                                onChange={(e) => {
                                    const val = e.target.value.replace(/\D/g, '').slice(0, 4);
                                    setData('pin', val);
                                }}
                                placeholder="••••"
                                className="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none font-black text-2xl text-center tracking-[0.5em] transition"
                                required
                            />
                        </div>
                        {errors.pin && (
                            <p className="text-xs text-rose-600 mt-1 font-semibold">{errors.pin}</p>
                        )}
                    </div>

                    {/* Numeric Keypad for Kids (Touch targets >= 44px) */}
                    <div className="bg-slate-50 p-3 rounded-2xl border border-slate-200">
                        <p className="text-[11px] font-semibold text-center text-slate-500 mb-2">
                            Papan Tombol Angka Layar Sentuh
                        </p>
                        <div className="grid grid-cols-3 gap-2">
                            {['1', '2', '3', '4', '5', '6', '7', '8', '9'].map((num) => (
                                <button
                                    key={num}
                                    type="button"
                                    onClick={() => handleKeypadPress(num)}
                                    className="min-h-[48px] rounded-xl bg-white hover:bg-amber-100 active:scale-95 text-slate-800 font-bold text-lg shadow-sm border border-slate-200 transition"
                                >
                                    {num}
                                </button>
                            ))}
                            <button
                                type="button"
                                onClick={handleKeypadClear}
                                className="min-h-[48px] rounded-xl bg-slate-200 hover:bg-slate-300 active:scale-95 text-slate-600 font-bold text-xs transition"
                            >
                                Hapus Semua
                            </button>
                            <button
                                type="button"
                                onClick={() => handleKeypadPress('0')}
                                className="min-h-[48px] rounded-xl bg-white hover:bg-amber-100 active:scale-95 text-slate-800 font-bold text-lg shadow-sm border border-slate-200 transition"
                            >
                                0
                            </button>
                            <button
                                type="button"
                                onClick={handleKeypadBackspace}
                                className="min-h-[48px] rounded-xl bg-rose-100 hover:bg-rose-200 active:scale-95 text-rose-700 font-bold text-sm transition"
                            >
                                ⌫
                            </button>
                        </div>
                    </div>

                    {/* Submit Button */}
                    <button
                        type="submit"
                        disabled={processing || data.pin.length !== 4 || !data.kode_kelas || !data.nama_panggilan}
                        className="w-full min-h-[52px] rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-lg shadow-lg shadow-orange-500/30 transition transform active:scale-98 flex items-center justify-center gap-2"
                    >
                        {processing ? 'Memeriksa...' : 'Masuk & Main Sekarang 🎮'}
                    </button>
                </form>

                {/* Demo Hint Banner */}
                <div className="mt-6 p-3 rounded-2xl bg-amber-50/80 border border-amber-200/80 text-xs text-amber-900">
                    <p className="font-bold mb-1 flex items-center gap-1 text-amber-800">
                        <Sparkles className="w-3.5 h-3.5" />
                        Akun Contoh untuk Pengujian:
                    </p>
                    <p>Kode: <strong className="font-mono bg-amber-100 px-1 py-0.5 rounded">JUARA5</strong> | Nama: <strong className="font-mono bg-amber-100 px-1 py-0.5 rounded">Budi</strong> | PIN: <strong className="font-mono bg-amber-100 px-1 py-0.5 rounded">1234</strong></p>
                    <p className="text-[11px] text-amber-700 mt-1">Atau masukkan nama baru untuk membuat profil siswa baru secara instan!</p>
                </div>
            </div>

            {/* Footer */}
            <div className="text-center text-xs text-white/80">
                <p>EduGame Indonesia &bull; Aman & Ramah Privasi (Tanpa Email & Nomor HP Siswa)</p>
            </div>
        </div>
    );
}
