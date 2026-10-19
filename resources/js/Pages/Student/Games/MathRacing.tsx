import React, { useState, useEffect, useRef, useCallback } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import axios from 'axios';
import confetti from 'canvas-confetti';
import {
    Heart,
    Zap,
    Flame,
    Volume2,
    VolumeX,
    RotateCcw,
    Home,
    Award,
    Trophy,
    HelpCircle,
    CheckCircle2,
    XCircle,
    ArrowRight,
    Sparkles,
} from 'lucide-react';
import RacingCanvas from '@/Games/BalapanPerkalian/RacingCanvas';
import { sound } from '@/Games/audioSynthesizer';

interface LevelOption {
    id: string;
    title: string;
    description: string;
    badge: string;
    color: string;
}

interface QuestionItem {
    id: number;
    teks: string;
    tipe: string;
    opsi: Record<string, string> | null;
    kesulitan: string;
}

interface IncorrectReviewItem {
    question_id: number;
    teks: string;
    tipe: string;
    opsi: Record<string, string> | null;
    jawaban_siswa: string;
    kunci_jawaban: string;
    penjelasan: string;
}

interface SessionFinishData {
    session_id: number;
    game_name: string;
    skor_akhir: number;
    total_soal: number;
    total_benar: number;
    total_salah: number;
    akurasi: number;
    podium_rank: number;
    xp_didapat: number;
    soal_salah: IncorrectReviewItem[];
}

interface Props {
    student: {
        id: number;
        name: string;
        username: string;
        level: number;
        xp: number;
    };
    levels: LevelOption[];
}

type GamePhase = 'SELECT_LEVEL' | 'COUNTDOWN' | 'PLAYING' | 'FEEDBACK' | 'FINISHED';

export default function MathRacing({ student, levels }: Props) {
    // State Alur Game
    const [phase, setPhase] = useState<GamePhase>('SELECT_LEVEL');
    const [selectedLevel, setSelectedLevel] = useState<string>('campuran');
    const [loading, setLoading] = useState<boolean>(false);
    const [countdownNumber, setCountdownNumber] = useState<number>(3);

    // State Sesi Game
    const [sessionId, setSessionId] = useState<number | null>(null);
    const [questions, setQuestions] = useState<QuestionItem[]>([]);
    const [currentIndex, setCurrentIndex] = useState<number>(0);
    const [lives, setLives] = useState<number>(3);
    const [score, setScore] = useState<number>(0);
    const [streak, setStreak] = useState<number>(0);
    const [isMuted, setIsMuted] = useState<boolean>(sound.getIsMuted());

    // Timer per Soal (15 detik)
    const TIME_LIMIT = 15;
    const [timeLeft, setTimeLeft] = useState<number>(TIME_LIMIT);
    const questionStartTime = useRef<number>(Date.now());
    const timerInterval = useRef<NodeJS.Timeout | null>(null);

    // Input Jawaban Siswa
    const [currentInput, setCurrentInput] = useState<string>('');
    const [submitting, setSubmitting] = useState<boolean>(false);
    const [showHint, setShowHint] = useState<boolean>(false);

    // Umpan Balik Instan (Feedback)
    const [feedback, setFeedback] = useState<{
        isCorrect: boolean;
        points: number;
        keyAnswer: string;
        explanation: string;
    } | null>(null);

    // Animasi Balapan (Canvas)
    const [playerProgress, setPlayerProgress] = useState<number>(20);
    const [bot1Progress, setBot1Progress] = useState<number>(20);
    const [bot2Progress, setBot2Progress] = useState<number>(20);
    const [isNitroActive, setIsNitroActive] = useState<boolean>(false);
    const [isStumbling, setIsStumbling] = useState<boolean>(false);

    // Hasil Akhir
    const [finishSummary, setFinishSummary] = useState<SessionFinishData | null>(null);

    // Toggle Audio
    const handleToggleAudio = () => {
        const nextMute = sound.toggleMute();
        setIsMuted(nextMute);
    };

    // Bersihkan timer saat unmount
    useEffect(() => {
        return () => {
            if (timerInterval.current) clearInterval(timerInterval.current);
        };
    }, []);

    // 1. Memulai Sesi Permainan
    const handleStartGame = async (levelId: string) => {
        setSelectedLevel(levelId);
        setLoading(true);

        try {
            const res = await axios.post('/api/game/session/start', {
                game_slug: 'balapan_perkalian',
                level: levelId,
            });

            if (res.data.success) {
                const data = res.data.data;
                setSessionId(data.session_id);
                setQuestions(data.questions);
                setCurrentIndex(0);
                setLives(3);
                setScore(0);
                setStreak(0);
                setPlayerProgress(15);
                setBot1Progress(15);
                setBot2Progress(15);

                // Jalankan hitungan mundur 3, 2, 1
                setPhase('COUNTDOWN');
                setCountdownNumber(3);

                let count = 3;
                const cd = setInterval(() => {
                    count--;
                    if (count > 0) {
                        setCountdownNumber(count);
                        sound.playTick();
                    } else {
                        clearInterval(cd);
                        setPhase('PLAYING');
                        startQuestionTimer();
                    }
                }, 900);
            }
        } catch (err: any) {
            alert('Gagal memulai sesi game. Silakan coba kembali.');
        } finally {
            setLoading(false);
        }
    };

    // 2. Timer Soal 15 Detik
    const startQuestionTimer = useCallback(() => {
        if (timerInterval.current) clearInterval(timerInterval.current);

        setTimeLeft(TIME_LIMIT);
        questionStartTime.current = Date.now();
        setCurrentInput('');
        setShowHint(false);
        setFeedback(null);
        setIsNitroActive(false);
        setIsStumbling(false);

        timerInterval.current = setInterval(() => {
            setTimeLeft((prev) => {
                if (prev <= 1) {
                    if (timerInterval.current) clearInterval(timerInterval.current);
                    handleTimeOut();
                    return 0;
                }
                if (prev <= 4) {
                    sound.playTick();
                }
                return prev - 1;
            });
        }, 1000);
    }, []);

    // 3. Waktu Habis (Dianggap salah oleh server)
    const handleTimeOut = () => {
        handleSubmitAnswer('TIMEOUT_NO_ANSWER');
    };

    // 4. Kirim Jawaban ke Server untuk Validasi
    const handleSubmitAnswer = async (answerValue: string) => {
        if (submitting || phase !== 'PLAYING') return;

        const answerToSubmit = answerValue.trim();
        if (!answerToSubmit) return;

        setSubmitting(true);
        if (timerInterval.current) clearInterval(timerInterval.current);

        const elapsedMs = Math.max(100, Date.now() - questionStartTime.current);
        const currentQ = questions[currentIndex];

        try {
            const res = await axios.post('/api/game/session/submit-answer', {
                session_id: sessionId,
                question_id: currentQ.id,
                jawaban_siswa: answerToSubmit === 'TIMEOUT_NO_ANSWER' ? '-' : answerToSubmit,
                waktu_jawab_ms: elapsedMs,
            });

            if (res.data.success) {
                const result = res.data.data;
                const isCorrect = result.benar;

                setScore(result.total_skor);
                setStreak(result.streak);

                // Efek Visual & Audio
                if (isCorrect) {
                    sound.playCorrect();
                    sound.playNitro();
                    setIsNitroActive(true);

                    // Mobil pemain melaju kencang
                    setPlayerProgress((prev) => Math.min(100, prev + 12));
                    // Bot maju normal
                    setBot1Progress((prev) => Math.min(100, prev + 6));
                    setBot2Progress((prev) => Math.min(100, prev + 7));
                } else {
                    sound.playWrong();
                    setIsStumbling(true);
                    setLives((prev) => Math.max(0, prev - 1));

                    // Mobil pemain melambat, bot mendahului
                    setPlayerProgress((prev) => Math.max(0, prev + 2));
                    setBot1Progress((prev) => Math.min(100, prev + 10));
                    setBot2Progress((prev) => Math.min(100, prev + 11));
                }

                // Tampilkan umpan balik
                setFeedback({
                    isCorrect,
                    points: result.poin_didapat,
                    keyAnswer: result.kunci_jawaban,
                    explanation: result.penjelasan || 'Tetap semangat!',
                });
                setPhase('FEEDBACK');

                // Lanjut ke soal berikutnya atau selesaikan setelah 2 detik
                setTimeout(() => {
                    const remainingLives = isCorrect ? lives : lives - 1;
                    const nextIdx = currentIndex + 1;

                    if (remainingLives <= 0 || nextIdx >= questions.length) {
                        finishGameSession();
                    } else {
                        setCurrentIndex(nextIdx);
                        setPhase('PLAYING');
                        startQuestionTimer();
                    }
                }, 2200);
            }
        } catch (err: any) {
            alert('Terjadi kendala saat memvalidasi jawaban.');
            setPhase('PLAYING');
        } finally {
            setSubmitting(false);
        }
    };

    // 5. Selesaikan Sesi Game & Dapatkan Pembahasan
    const finishGameSession = async () => {
        setLoading(true);
        if (timerInterval.current) clearInterval(timerInterval.current);

        try {
            const res = await axios.post('/api/game/session/finish', {
                session_id: sessionId,
            });

            if (res.data.success) {
                const summary: SessionFinishData = res.data.data;
                setFinishSummary(summary);
                setPhase('FINISHED');

                // Jika podium 1-3, semarakkan dengan konfeti dan fanfare
                if (summary.podium_rank <= 3 && summary.akurasi >= 50) {
                    sound.playVictory();
                    confetti({
                        particleCount: 120,
                        spread: 80,
                        origin: { y: 0.6 },
                    });
                }
            }
        } catch (err: any) {
            alert('Gagal menyelesaikan sesi game.');
        } finally {
            setLoading(false);
        }
    };

    // Dukungan Keyboard Fisik untuk Laptop/PC
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            if (phase !== 'PLAYING' || submitting) return;

            const currentQ = questions[currentIndex];
            if (!currentQ) return;

            // Jika tipe pilihan ganda, tombol A, B, C, D langsung kirim
            if (currentQ.tipe === 'pilihan_ganda') {
                const key = e.key.toUpperCase();
                if (['A', 'B', 'C', 'D'].includes(key) && currentQ.opsi && currentQ.opsi[key]) {
                    handleSubmitAnswer(key);
                }
                return;
            }

            // Jika isian singkat / angka (0-9, Enter, Backspace)
            if (/^[0-9]$/.test(e.key)) {
                setCurrentInput((prev) => (prev.length < 5 ? prev + e.key : prev));
            } else if (e.key === 'Backspace') {
                setCurrentInput((prev) => prev.slice(0, -1));
            } else if (e.key === 'Enter') {
                if (currentInput.trim()) {
                    handleSubmitAnswer(currentInput);
                }
            }
        };

        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [phase, submitting, currentIndex, questions, currentInput]);

    // Current Question
    const currentQ = questions[currentIndex];

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900 text-white flex flex-col justify-between select-none">
            <Head title="Balapan Perkalian - EduGame" />

            {/* Header Game Bar */}
            <header className="px-4 sm:px-8 py-3 border-b border-white/10 bg-slate-950/70 backdrop-blur-md sticky top-0 z-30">
                <div className="max-w-5xl mx-auto flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Link
                            href="/siswa/dashboard"
                            className="p-2 rounded-xl bg-white/10 hover:bg-white/20 transition text-slate-300 hover:text-white"
                            title="Kembali ke Dashboard"
                        >
                            <Home className="w-5 h-5" />
                        </Link>
                        <div>
                            <h1 className="text-base sm:text-lg font-black tracking-tight flex items-center gap-2">
                                <span>🏎️ Balapan Perkalian</span>
                                {streak > 1 && (
                                    <span className="flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-orange-500 text-white text-xs font-black animate-pulse shadow-md shadow-orange-500/30">
                                        <Flame className="w-3.5 h-3.5 fill-white" />
                                        <span>{streak} Streak!</span>
                                    </span>
                                )}
                            </h1>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        {/* Audio Mute Button */}
                        <button
                            onClick={handleToggleAudio}
                            className={`p-2 rounded-xl border transition flex items-center gap-1.5 text-xs font-bold ${
                                isMuted
                                    ? 'bg-rose-500/20 border-rose-500/40 text-rose-300 hover:bg-rose-500/30'
                                    : 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300 hover:bg-emerald-500/30'
                            }`}
                            title={isMuted ? 'Suara Senyap (Klik untuk aktifkan)' : 'Suara Aktif (Klik untuk senyap)'}
                        >
                            {isMuted ? <VolumeX className="w-4 h-4" /> : <Volume2 className="w-4 h-4" />}
                            <span className="hidden sm:inline">{isMuted ? 'Suara: Mati' : 'Suara: Hidup'}</span>
                        </button>

                        {/* Nyawa */}
                        {phase !== 'SELECT_LEVEL' && phase !== 'FINISHED' && (
                            <div className="flex items-center gap-1 px-3 py-1.5 rounded-xl bg-black/40 border border-white/10">
                                {[1, 2, 3].map((heart) => (
                                    <Heart
                                        key={heart}
                                        className={`w-4 h-4 transition-all ${
                                            heart <= lives
                                                ? 'text-rose-500 fill-rose-500 scale-105'
                                                : 'text-slate-600 fill-slate-800'
                                        }`}
                                    />
                                ))}
                            </div>
                        )}

                        {/* Skor */}
                        <div className="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-400/20 border border-amber-400/40 text-amber-300 font-black text-sm">
                            <Zap className="w-4 h-4 fill-amber-300" />
                            <span>{score} Pts</span>
                        </div>
                    </div>
                </div>
            </header>

            {/* MAIN CONTAINER */}
            <main className="max-w-4xl w-full mx-auto px-4 py-6 flex-1 flex flex-col justify-center">
                {/* 1. FASE PILIH LEVEL */}
                {phase === 'SELECT_LEVEL' && (
                    <div className="text-center py-6">
                        <div className="inline-block p-4 rounded-3xl bg-amber-400/20 text-amber-300 mb-3 border border-amber-400/30">
                            <Trophy className="w-12 h-12" />
                        </div>
                        <h2 className="text-2xl sm:text-3xl font-black mb-2">Pilih Arena Balapan</h2>
                        <p className="text-sm text-indigo-200 max-w-md mx-auto mb-8">
                            Adu cepat hitung matematika melawan bot Boni dan Cici di sirkuit 3 jalur!
                        </p>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl mx-auto mb-8 text-left">
                            {levels.map((lvl) => (
                                <button
                                    key={lvl.id}
                                    type="button"
                                    onClick={() => handleStartGame(lvl.id)}
                                    disabled={loading}
                                    className="p-5 rounded-3xl bg-slate-900/80 hover:bg-indigo-900/60 border-2 border-white/10 hover:border-amber-400 transition-all text-left flex flex-col justify-between group shadow-xl active:scale-98"
                                >
                                    <div>
                                        <div className="flex items-center justify-between mb-2">
                                            <span className="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                                {lvl.badge}
                                            </span>
                                            <span className="text-xs text-amber-400 font-bold group-hover:translate-x-1 transition-transform">
                                                Main &rarr;
                                            </span>
                                        </div>
                                        <h3 className="text-lg font-black text-white group-hover:text-amber-300 transition">
                                            {lvl.title}
                                        </h3>
                                        <p className="text-xs text-slate-300 mt-1">{lvl.description}</p>
                                    </div>
                                    <div className="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-xs font-semibold text-slate-400">
                                        <span>10 Soal &bull; 15s per Soal</span>
                                        <span className="text-amber-300 font-bold">+XP Hadiah</span>
                                    </div>
                                </button>
                            ))}
                        </div>
                    </div>
                )}

                {/* 2. FASE COUNTDOWN */}
                {phase === 'COUNTDOWN' && (
                    <div className="text-center py-20 flex flex-col items-center justify-center">
                        <span className="text-xs uppercase tracking-widest text-amber-400 font-black mb-4">
                            Siap di Garis Start...
                        </span>
                        <div className="w-36 h-36 rounded-full bg-gradient-to-tr from-amber-500 to-orange-500 flex items-center justify-center text-7xl font-black shadow-2xl shadow-orange-500/50 animate-ping">
                            {countdownNumber}
                        </div>
                        <p className="text-sm text-slate-300 mt-8 font-medium">
                            Nyalakan mesin dan fokus pada hitungan pertama!
                        </p>
                    </div>
                )}

                {/* 3 & 4. FASE MAIN & FEEDBACK */}
                {(phase === 'PLAYING' || phase === 'FEEDBACK') && currentQ && (
                    <div className="space-y-4">
                        {/* Area Visual Balapan (Canvas) */}
                        <RacingCanvas
                            playerProgress={playerProgress}
                            bot1Progress={bot1Progress}
                            bot2Progress={bot2Progress}
                            isNitroActive={isNitroActive}
                            isStumbling={isStumbling}
                        />

                        {/* Kartu Soal & Timer */}
                        <div className="p-6 rounded-3xl bg-slate-900/90 border border-white/15 backdrop-blur-md shadow-2xl relative overflow-hidden">
                            {/* Bar Timer 15 Detik */}
                            <div className="w-full h-2 rounded-full bg-slate-800 overflow-hidden mb-4">
                                <div
                                    className={`h-full transition-all duration-1000 ${
                                        timeLeft > 7
                                            ? 'bg-emerald-400'
                                            : timeLeft > 3
                                            ? 'bg-amber-400'
                                            : 'bg-rose-500 animate-pulse'
                                    }`}
                                    style={{ width: `${(timeLeft / TIME_LIMIT) * 100}%` }}
                                />
                            </div>

                            <div className="flex items-center justify-between text-xs text-indigo-300 mb-2">
                                <span className="font-bold">
                                    Soal {currentIndex + 1} dari {questions.length}
                                </span>
                                <span
                                    className={`font-black text-sm ${
                                        timeLeft <= 3 ? 'text-rose-400 animate-bounce' : 'text-slate-300'
                                    }`}
                                >
                                    ⏱️ {timeLeft} detik
                                </span>
                            </div>

                            {/* Teks Soal */}
                            <div className="text-center py-4">
                                <h2 className="text-3xl sm:text-4xl font-black text-white tracking-wide">
                                    {currentQ.teks}
                                </h2>

                                {/* Tombol Hint */}
                                {!showHint ? (
                                    <button
                                        type="button"
                                        onClick={() => setShowHint(true)}
                                        className="inline-flex items-center gap-1.5 text-xs text-amber-400/80 hover:text-amber-300 mt-2 font-semibold transition"
                                    >
                                        <HelpCircle className="w-3.5 h-3.5" />
                                        <span>Butuh Petunjuk?</span>
                                    </button>
                                ) : (
                                    <div className="mt-3 p-3 rounded-2xl bg-amber-400/10 border border-amber-400/30 text-xs text-amber-200 max-w-md mx-auto animate-fade-in">
                                        💡 <strong>Petunjuk:</strong> Pikirkan penjumlahan berulang atau tabel perkalian kelipatan terdekat!
                                    </div>
                                )}
                            </div>

                            {/* Overlay Feedback Umpan Balik */}
                            {phase === 'FEEDBACK' && feedback && (
                                <div
                                    className={`p-4 rounded-2xl text-center mb-4 transition-all duration-300 ${
                                        feedback.isCorrect
                                            ? 'bg-emerald-500/20 border-2 border-emerald-500 text-emerald-200'
                                            : 'bg-rose-500/20 border-2 border-rose-500 text-rose-200'
                                    }`}
                                >
                                    <div className="flex items-center justify-center gap-2 font-black text-lg mb-1">
                                        {feedback.isCorrect ? (
                                            <>
                                                <CheckCircle2 className="w-6 h-6 text-emerald-400" />
                                                <span>BENAR! +{feedback.points} POIN</span>
                                            </>
                                        ) : (
                                            <>
                                                <XCircle className="w-6 h-6 text-rose-400" />
                                                <span>KURANG TEPAT! KUNCI: {feedback.keyAnswer}</span>
                                            </>
                                        )}
                                    </div>
                                    <p className="text-xs text-slate-200">{feedback.explanation}</p>
                                </div>
                            )}

                            {/* Area Input Jawaban */}
                            {currentQ.tipe === 'pilihan_ganda' && currentQ.opsi ? (
                                /* Pilihan Ganda (Tombol Besar Touch-Friendly >= 48px) */
                                <div className="grid grid-cols-2 gap-3 pt-2">
                                    {Object.entries(currentQ.opsi).map(([key, val]) => (
                                        <button
                                            key={key}
                                            type="button"
                                            disabled={submitting || phase === 'FEEDBACK'}
                                            onClick={() => handleSubmitAnswer(key)}
                                            className="min-h-[56px] p-3 rounded-2xl bg-slate-800/90 hover:bg-indigo-600 border-2 border-white/10 hover:border-amber-400 font-black text-lg text-white flex items-center justify-start gap-3 transition-all active:scale-95 shadow-md"
                                        >
                                            <span className="w-8 h-8 rounded-xl bg-white/10 text-amber-300 flex items-center justify-center text-sm font-black">
                                                {key}
                                            </span>
                                            <span className="truncate">{val}</span>
                                        </button>
                                    ))}
                                </div>
                            ) : (
                                /* Isian Singkat / Angka dengan Virtual Keypad + Input Bar */
                                <div className="space-y-3 pt-2">
                                    <div className="flex items-center gap-2 max-w-sm mx-auto">
                                        <div className="flex-1 min-h-[52px] px-4 rounded-2xl bg-black/50 border-2 border-amber-400/50 flex items-center justify-center text-2xl font-black text-amber-300 tracking-wider">
                                            {currentInput || <span className="text-slate-600 text-sm">Ketik jawabanmu...</span>}
                                        </div>
                                        <button
                                            type="button"
                                            disabled={!currentInput || submitting || phase === 'FEEDBACK'}
                                            onClick={() => handleSubmitAnswer(currentInput)}
                                            className="min-h-[52px] px-6 rounded-2xl bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-300 hover:to-orange-400 text-indigo-950 font-black text-base shadow-lg shadow-orange-500/30 transition active:scale-95 disabled:opacity-40"
                                        >
                                            Kirim
                                        </button>
                                    </div>

                                    {/* Numeric Touch Keypad (0-9 & Clear) */}
                                    <div className="grid grid-cols-3 gap-2 max-w-xs mx-auto">
                                        {[1, 2, 3, 4, 5, 6, 7, 8, 9].map((num) => (
                                            <button
                                                key={num}
                                                type="button"
                                                disabled={submitting || phase === 'FEEDBACK'}
                                                onClick={() =>
                                                    setCurrentInput((prev) => (prev.length < 5 ? prev + num : prev))
                                                }
                                                className="min-h-[48px] rounded-2xl bg-slate-800/90 hover:bg-slate-700 active:bg-amber-400 active:text-indigo-950 text-white font-black text-xl border border-white/10 transition shadow-sm"
                                            >
                                                {num}
                                            </button>
                                        ))}
                                        <button
                                            type="button"
                                            disabled={submitting || phase === 'FEEDBACK'}
                                            onClick={() => setCurrentInput('')}
                                            className="min-h-[48px] rounded-2xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 font-bold text-sm border border-rose-500/30 transition"
                                        >
                                            Hapus
                                        </button>
                                        <button
                                            type="button"
                                            disabled={submitting || phase === 'FEEDBACK'}
                                            onClick={() =>
                                                setCurrentInput((prev) => (prev.length < 5 ? prev + '0' : prev))
                                            }
                                            className="min-h-[48px] rounded-2xl bg-slate-800/90 hover:bg-slate-700 active:bg-amber-400 active:text-indigo-950 text-white font-black text-xl border border-white/10 transition shadow-sm"
                                        >
                                            0
                                        </button>
                                        <button
                                            type="button"
                                            disabled={submitting || phase === 'FEEDBACK'}
                                            onClick={() => setCurrentInput((prev) => prev.slice(0, -1))}
                                            className="min-h-[48px] rounded-2xl bg-slate-800/90 hover:bg-slate-700 text-slate-300 font-bold text-sm border border-white/10 transition"
                                        >
                                            &larr;
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                )}

                {/* 5. FASE SELESAI / HASIL AKHIR & EVALUASI SOAL SALAH */}
                {phase === 'FINISHED' && finishSummary && (
                    <div className="space-y-6 animate-fade-in">
                        {/* Kartu Podium & Ringkasan Skor */}
                        <div className="p-8 rounded-3xl bg-gradient-to-b from-indigo-900/90 to-slate-900/90 border-2 border-amber-400/50 backdrop-blur-md shadow-2xl text-center">
                            <div className="inline-block p-4 rounded-3xl bg-amber-400 text-indigo-950 text-4xl mb-3 shadow-lg shadow-yellow-500/30">
                                {finishSummary.podium_rank === 1 ? '🥇' : finishSummary.podium_rank === 2 ? '🥈' : '🥉'}
                            </div>

                            <h2 className="text-2xl sm:text-3xl font-black text-white">
                                {finishSummary.podium_rank === 1
                                    ? 'Juara 1! Balapan Luar Biasa!'
                                    : finishSummary.podium_rank === 2
                                    ? 'Juara 2! Sangat Kencang!'
                                    : 'Finish! Tetap Hebat & Terus Latihan!'}
                            </h2>
                            <p className="text-xs text-indigo-200 mt-1">
                                Hasil terverifikasi langsung oleh server EduGame.
                            </p>

                            {/* Grid Metrik Hasil */}
                            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 my-6 max-w-xl mx-auto">
                                <div className="p-4 rounded-2xl bg-white/5 border border-white/10">
                                    <span className="text-[10px] uppercase font-bold text-slate-400 block">Skor Akhir</span>
                                    <span className="text-2xl font-black text-amber-300">{finishSummary.skor_akhir}</span>
                                </div>
                                <div className="p-4 rounded-2xl bg-white/5 border border-white/10">
                                    <span className="text-[10px] uppercase font-bold text-slate-400 block">Akurasi</span>
                                    <span className="text-2xl font-black text-emerald-400">{finishSummary.akurasi}%</span>
                                </div>
                                <div className="p-4 rounded-2xl bg-white/5 border border-white/10">
                                    <span className="text-[10px] uppercase font-bold text-slate-400 block">Benar / Soal</span>
                                    <span className="text-2xl font-black text-blue-300">
                                        {finishSummary.total_benar}/{finishSummary.total_soal}
                                    </span>
                                </div>
                                <div className="p-4 rounded-2xl bg-white/5 border border-white/10">
                                    <span className="text-[10px] uppercase font-bold text-slate-400 block">Hadiah XP</span>
                                    <span className="text-2xl font-black text-purple-300">+{finishSummary.xp_didapat} XP</span>
                                </div>
                            </div>

                            {/* Tombol Aksi */}
                            <div className="flex flex-col sm:flex-row items-center justify-center gap-3">
                                <button
                                    type="button"
                                    onClick={() => setPhase('SELECT_LEVEL')}
                                    className="w-full sm:w-auto min-h-[48px] px-6 rounded-2xl bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-300 hover:to-orange-400 text-indigo-950 font-black text-sm flex items-center justify-center gap-2 shadow-lg shadow-orange-500/30 transition active:scale-95"
                                >
                                    <RotateCcw className="w-4 h-4" />
                                    <span>Balapan Lagi</span>
                                </button>
                                <Link
                                    href="/siswa/dashboard"
                                    className="w-full sm:w-auto min-h-[48px] px-6 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-sm flex items-center justify-center gap-2 border border-white/15 transition active:scale-95"
                                >
                                    <Home className="w-4 h-4" />
                                    <span>Kembali ke Beranda</span>
                                </Link>
                            </div>
                        </div>

                        {/* FITUR KHUSUS M4: Evaluasi & Pembahasan Soal yang Salah */}
                        {finishSummary.soal_salah.length > 0 ? (
                            <div className="p-6 rounded-3xl bg-slate-900/80 border border-rose-500/30 shadow-xl">
                                <div className="flex items-center gap-2 mb-4">
                                    <div className="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center font-black">
                                        📝
                                    </div>
                                    <div>
                                        <h3 className="text-lg font-black text-white">
                                            Evaluasi Pintar: Pelajari Soal yang Belum Tepat
                                        </h3>
                                        <p className="text-xs text-slate-300">
                                            Berikut {finishSummary.soal_salah.length} soal yang perlu diperbaiki beserta cara penyelesaiannya.
                                        </p>
                                    </div>
                                </div>

                                <div className="space-y-4">
                                    {finishSummary.soal_salah.map((item, idx) => (
                                        <div
                                            key={item.question_id || idx}
                                            className="p-4 rounded-2xl bg-slate-950/80 border border-white/10"
                                        >
                                            <div className="flex items-start justify-between gap-2 mb-2">
                                                <h4 className="text-base font-black text-white">
                                                    {idx + 1}. {item.teks}
                                                </h4>
                                                <span className="px-2 py-0.5 rounded-md bg-rose-500/20 text-rose-300 text-[10px] font-black uppercase">
                                                    Salah
                                                </span>
                                            </div>

                                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs mb-3">
                                                <div className="p-2.5 rounded-xl bg-rose-950/40 border border-rose-500/20 text-rose-200">
                                                    <span className="font-bold text-rose-400 block mb-0.5">Jawabanmu:</span>
                                                    <span>{item.jawaban_siswa || '(Waktu Habis)'}</span>
                                                </div>
                                                <div className="p-2.5 rounded-xl bg-emerald-950/40 border border-emerald-500/20 text-emerald-200">
                                                    <span className="font-bold text-emerald-400 block mb-0.5">
                                                        Kunci Jawaban yang Benar:
                                                    </span>
                                                    <span>{item.kunci_jawaban}</span>
                                                </div>
                                            </div>

                                            {item.penjelasan && (
                                                <div className="p-3 rounded-xl bg-indigo-950/50 border border-indigo-500/20 text-xs text-indigo-200">
                                                    <strong className="text-amber-300 block mb-1">💡 Pembahasan:</strong>
                                                    <span>{item.penjelasan}</span>
                                                </div>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ) : (
                            <div className="p-6 rounded-3xl bg-emerald-900/30 border border-emerald-500/40 text-center shadow-xl">
                                <span className="text-4xl mb-2 block">🌟</span>
                                <h3 className="text-lg font-black text-emerald-300">Hebat! Semua Soal Dijawab Benar!</h3>
                                <p className="text-xs text-emerald-200 mt-1">
                                    Kamu berhasil menyelesaikan semua soal tanpa satupun kesalahan. Pertahankan prestasimu!
                                </p>
                            </div>
                        )}
                    </div>
                )}
            </main>

            {/* Footer */}
            <footer className="px-6 py-3 border-t border-white/10 text-center text-xs text-slate-400">
                EduGame Balapan Perkalian &bull; Kuis Matematika Cepat & Asyik
            </footer>
        </div>
    );
}
