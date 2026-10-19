import React, { useEffect, useRef, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import {
    Gamepad2,
    GraduationCap,
    Trophy,
    Sparkles,
    Users,
    Shield,
    Zap,
    Flame,
    ArrowRight,
    CheckCircle2,
    Play,
    Volume2,
    VolumeX,
    Star,
    Layers,
    Cpu,
    Compass,
    ChevronRight,
} from 'lucide-react';

interface Props {
    appName?: string;
    auth?: {
        user?: {
            id: number;
            name: string;
            role: 'siswa' | 'guru' | 'admin';
        } | null;
    };
    stats?: {
        schools: number;
        teachers: number;
        students: number;
        questions: number;
    };
}

// 3D Point interface
interface Point3D {
    x: number;
    y: number;
    z: number;
}

export default function Welcome({ appName = 'EduGame Indonesia', auth, stats }: Props) {
    const canvasRef = useRef<HTMLCanvasElement | null>(null);
    const [mousePos, setMousePos] = useState({ x: 0, y: 0 });
    const [soundEnabled, setSoundEnabled] = useState(false);
    const [nitroActive, setNitroActive] = useState(false);
    const [testAnswer, setTestAnswer] = useState<number | null>(null);
    const [testFeedback, setTestFeedback] = useState<string | null>(null);

    // Audio synthesizer Web Audio API
    const playNitroSound = () => {
        if (!soundEnabled) return;
        try {
            const AudioCtx = window.AudioContext || (window as any).webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(110, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(440, ctx.currentTime + 0.4);

            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);

            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.45);
        } catch (e) {
            // Audio context blocked or unsupported
        }
    };

    const playSuccessSound = () => {
        if (!soundEnabled) return;
        try {
            const AudioCtx = window.AudioContext || (window as any).webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(523.25, ctx.currentTime); // C5
            osc.frequency.setValueAtTime(659.25, ctx.currentTime + 0.1); // E5
            osc.frequency.setValueAtTime(783.99, ctx.currentTime + 0.2); // G5
            osc.frequency.setValueAtTime(1046.5, ctx.currentTime + 0.3); // C6

            gain.gain.setValueAtTime(0.2, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);

            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.55);
        } catch (e) {}
    };

    // Trigger Nitro Boost
    const triggerNitro = () => {
        setNitroActive(true);
        playNitroSound();
        setTimeout(() => setNitroActive(false), 800);
    };

    // Quick Multi Quiz Interaction
    const handleAnswerClick = (val: number) => {
        setTestAnswer(val);
        if (val === 56) {
            setTestFeedback('✨ BENAR! Kecepatan +120 Km/jam!');
            playSuccessSound();
            triggerNitro();
        } else {
            setTestFeedback('❌ Belum tepat. Coba lagi!');
        }
    };

    // 3D Canvas Animation Hook
    useEffect(() => {
        const canvas = canvasRef.current;
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        let animationFrameId: number;
        let width = (canvas.width = canvas.parentElement?.clientWidth || window.innerWidth);
        let height = (canvas.height = canvas.parentElement?.clientHeight || 600);

        const handleResize = () => {
            if (!canvas.parentElement) return;
            width = canvas.width = canvas.parentElement.clientWidth;
            height = canvas.height = canvas.parentElement.clientHeight;
        };
        window.addEventListener('resize', handleResize);

        // 3D Rotating Cube Vertices
        const size = 110;
        const baseVertices: Point3D[] = [
            { x: -size, y: -size, z: -size },
            { x: size, y: -size, z: -size },
            { x: size, y: size, z: -size },
            { x: -size, y: size, z: -size },
            { x: -size, y: -size, z: size },
            { x: size, y: -size, z: size },
            { x: size, y: size, z: size },
            { x: -size, y: size, z: size },
        ];

        // Cube Edges
        const edges = [
            [0, 1], [1, 2], [2, 3], [3, 0], // Belakang
            [4, 5], [5, 6], [6, 7], [7, 4], // Depan
            [0, 4], [1, 5], [2, 6], [3, 7], // Sisi
        ];

        // Background floating particles
        const particlesCount = 45;
        const particles: { x: number; y: number; z: number; vx: number; vy: number; radius: number; color: string }[] = [];
        const colors = ['#6366f1', '#a855f7', '#ec4899', '#f59e0b', '#10b981'];

        for (let i = 0; i < particlesCount; i++) {
            particles.push({
                x: (Math.random() - 0.5) * width * 1.5,
                y: (Math.random() - 0.5) * height * 1.5,
                z: Math.random() * 400 + 50,
                vx: (Math.random() - 0.5) * 0.8,
                vy: (Math.random() - 0.5) * 0.8,
                radius: Math.random() * 2.5 + 1.2,
                color: colors[Math.floor(Math.random() * colors.length)],
            });
        }

        let rotX = 0.4;
        let rotY = 0.6;
        let rotZ = 0.2;

        const render = () => {
            ctx.clearRect(0, 0, width, height);

            // Center of projection
            const cx = width * 0.75;
            const cy = height * 0.48;
            const fov = 420;

            // Increment rotation, influenced slightly by mouse position
            rotX += 0.007 + mousePos.y * 0.00004;
            rotY += 0.009 + mousePos.x * 0.00004;
            rotZ += 0.003;

            // 1. Draw floating constellation particles
            for (let i = 0; i < particles.length; i++) {
                const p = particles[i];
                p.x += p.vx;
                p.y += p.vy;

                if (p.x < -width / 2) p.x = width / 2;
                if (p.x > width / 2) p.x = -width / 2;
                if (p.y < -height / 2) p.y = height / 2;
                if (p.y > height / 2) p.y = -height / 2;

                const screenX = cx + (p.x * fov) / (p.z + fov);
                const screenY = cy + (p.y * fov) / (p.z + fov);
                const scale = fov / (p.z + fov);

                ctx.beginPath();
                ctx.arc(screenX, screenY, p.radius * scale, 0, Math.PI * 2);
                ctx.fillStyle = p.color;
                ctx.globalAlpha = Math.max(0.1, 0.6 * scale);
                ctx.fill();

                // Connect nearby particles with subtle light filaments
                for (let j = i + 1; j < particles.length; j++) {
                    const p2 = particles[j];
                    const dx = p.x - p2.x;
                    const dy = p.y - p2.y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < 100) {
                        const s2X = cx + (p2.x * fov) / (p2.z + fov);
                        const s2Y = cy + (p2.y * fov) / (p2.z + fov);
                        ctx.beginPath();
                        ctx.moveTo(screenX, screenY);
                        ctx.lineTo(s2X, s2Y);
                        ctx.strokeStyle = '#818cf8';
                        ctx.globalAlpha = (1 - dist / 100) * 0.18;
                        ctx.lineWidth = 0.8;
                        ctx.stroke();
                    }
                }
            }

            // 2. Rotate & Project 3D Mathematical Cube
            const radX = rotX;
            const radY = rotY;
            const radZ = rotZ;

            const cosX = Math.cos(radX), sinX = Math.sin(radX);
            const cosY = Math.cos(radY), sinY = Math.sin(radY);
            const cosZ = Math.cos(radZ), sinZ = Math.sin(radZ);

            const projected: { x: number; y: number; z: number }[] = [];

            for (const v of baseVertices) {
                // Rotation around X
                let y1 = v.y * cosX - v.z * sinX;
                let z1 = v.y * sinX + v.z * cosX;

                // Rotation around Y
                let x2 = v.x * cosY + z1 * sinY;
                let z2 = -v.x * sinY + z1 * cosY;

                // Rotation around Z
                let x3 = x2 * cosZ - y1 * sinZ;
                let y3 = x2 * sinZ + y1 * cosZ;

                const distance = 380;
                const pz = z2 + distance;
                const projX = cx + (x3 * fov) / pz;
                const projY = cy + (y3 * fov) / pz;

                projected.push({ x: projX, y: projY, z: pz });
            }

            // Draw glowing 3D Cube Edges
            ctx.lineCap = 'round';
            for (const edge of edges) {
                const p1 = projected[edge[0]];
                const p2 = projected[edge[1]];

                const grad = ctx.createLinearGradient(p1.x, p1.y, p2.x, p2.y);
                grad.addColorStop(0, '#f59e0b');
                grad.addColorStop(0.5, '#6366f1');
                grad.addColorStop(1, '#ec4899');

                ctx.beginPath();
                ctx.moveTo(p1.x, p1.y);
                ctx.lineTo(p2.x, p2.y);
                ctx.strokeStyle = grad;
                ctx.globalAlpha = 0.85;
                ctx.lineWidth = 2.8;
                ctx.shadowColor = '#6366f1';
                ctx.shadowBlur = 12;
                ctx.stroke();
                ctx.shadowBlur = 0;
            }

            // Draw corner vertex glow points
            for (let i = 0; i < projected.length; i++) {
                const p = projected[i];
                ctx.beginPath();
                ctx.arc(p.x, p.y, 4.5, 0, Math.PI * 2);
                ctx.fillStyle = '#38bdf8';
                ctx.globalAlpha = 0.95;
                ctx.shadowColor = '#38bdf8';
                ctx.shadowBlur = 15;
                ctx.fill();
                ctx.shadowBlur = 0;
            }

            // Inner glowing mathematical core
            ctx.beginPath();
            ctx.arc(cx, cy, 22, 0, Math.PI * 2);
            const coreGrad = ctx.createRadialGradient(cx, cy, 2, cx, cy, 28);
            coreGrad.addColorStop(0, '#fef08a');
            coreGrad.addColorStop(0.5, '#f59e0b');
            coreGrad.addColorStop(1, 'transparent');
            ctx.fillStyle = coreGrad;
            ctx.globalAlpha = 0.7;
            ctx.fill();

            // Label text floating near center
            ctx.font = 'bold 12px monospace';
            ctx.fillStyle = '#ffffff';
            ctx.globalAlpha = 0.9;
            ctx.textAlign = 'center';
            ctx.fillText('7 × 8 = 56', cx, cy + 4);

            animationFrameId = requestAnimationFrame(render);
        };

        render();

        return () => {
            window.removeEventListener('resize', handleResize);
            cancelAnimationFrame(animationFrameId);
        };
    }, [mousePos]);

    const handleMouseMove = (e: React.MouseEvent) => {
        const rect = e.currentTarget.getBoundingClientRect();
        setMousePos({
            x: e.clientX - rect.left - rect.width / 2,
            y: e.clientY - rect.top - rect.height / 2,
        });
    };

    return (
        <div
            onMouseMove={handleMouseMove}
            className="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-between selection:bg-indigo-500 selection:text-white font-sans overflow-x-hidden relative"
        >
            <Head title="EduGame Indonesia - Platform Game Edukasi Modern & Kurikulum Nasional" />

            {/* Glowing Ambient Backdrop Lights */}
            <div className="absolute top-0 left-1/4 w-[600px] h-[500px] bg-indigo-600/15 rounded-full blur-[140px] pointer-events-none -z-10" />
            <div className="absolute top-1/3 right-10 w-[500px] h-[500px] bg-purple-600/15 rounded-full blur-[150px] pointer-events-none -z-10" />
            <div className="absolute bottom-10 left-10 w-[600px] h-[400px] bg-amber-500/10 rounded-full blur-[140px] pointer-events-none -z-10" />

            {/* Top Navigation Bar */}
            <header className="px-6 py-4 max-w-7xl w-full mx-auto flex items-center justify-between sticky top-0 z-40 backdrop-blur-xl bg-slate-950/70 border-b border-slate-800/60">
                <Link href="/" className="flex items-center gap-3 group">
                    <div className="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-400 via-orange-500 to-rose-500 flex items-center justify-center shadow-lg shadow-orange-500/25 group-hover:scale-105 group-hover:rotate-3 transition duration-300">
                        <Gamepad2 className="w-6 h-6 text-slate-950" />
                    </div>
                    <div>
                        <div className="flex items-center gap-2">
                            <span className="text-xl font-black tracking-tight text-white">{appName}</span>
                            <span className="text-[10px] px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-bold uppercase tracking-wider">
                                V2.0 3D
                            </span>
                        </div>
                        <p className="text-[11px] text-slate-400 font-medium">Petualangan Belajar & Sains Interaktif</p>
                    </div>
                </Link>

                <div className="flex items-center gap-3">
                    {/* Audio Toggle Button */}
                    <button
                        onClick={() => setSoundEnabled(!soundEnabled)}
                        title={soundEnabled ? 'Matikan Suara SFX' : 'Nyalakan Suara SFX Game'}
                        className={`p-2.5 rounded-xl border text-xs font-bold transition flex items-center gap-1.5 ${
                            soundEnabled
                                ? 'bg-amber-500/20 border-amber-500/40 text-amber-300 shadow-md shadow-amber-500/20'
                                : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white'
                        }`}
                    >
                        {soundEnabled ? <Volume2 className="w-4 h-4 text-amber-400" /> : <VolumeX className="w-4 h-4" />}
                        <span className="hidden sm:inline">{soundEnabled ? 'SFX Aktif' : 'SFX Mati'}</span>
                    </button>

                    {auth?.user ? (
                        <Link
                            href={
                                auth.user.role === 'admin'
                                    ? '/admin/dashboard'
                                    : auth.user.role === 'guru'
                                    ? '/guru/dashboard'
                                    : '/siswa/dashboard'
                            }
                            className="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-300 hover:to-orange-400 text-slate-950 font-black text-xs tracking-wide shadow-lg shadow-orange-500/20 transition hover:scale-105"
                        >
                            Ke Dashboard ({auth.user.name})
                        </Link>
                    ) : (
                        <div className="flex items-center gap-2">
                            <Link
                                href="/masuk-siswa"
                                className="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white text-xs font-black shadow-lg shadow-emerald-500/20 transition hover:scale-105 flex items-center gap-1.5"
                            >
                                <Play className="w-3.5 h-3.5 fill-current" />
                                <span>Masuk Siswa</span>
                            </Link>
                            <Link
                                href="/login-guru"
                                className="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-200 hover:text-white text-xs font-bold border border-slate-800 transition flex items-center gap-1.5"
                            >
                                <GraduationCap className="w-4 h-4 text-indigo-400" />
                                <span>Portal Guru</span>
                            </Link>
                            <Link
                                href="/login-guru"
                                title="Login Superadmin Platform"
                                className="p-2 rounded-xl bg-slate-900 hover:bg-amber-500/20 text-slate-400 hover:text-amber-400 border border-slate-800 hover:border-amber-500/30 transition"
                            >
                                <Shield className="w-4 h-4" />
                            </Link>
                        </div>
                    )}
                </div>
            </header>

            {/* Hero Section with Interactive 3D Canvas */}
            <main className="max-w-7xl mx-auto px-6 py-10 flex-1 flex flex-col justify-center relative w-full">
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    {/* Left Hero Column */}
                    <div className="lg:col-span-7 space-y-6 text-left relative z-10">
                        <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-xs font-bold text-indigo-300 shadow-sm animate-pulse">
                            <Sparkles className="w-4 h-4 text-amber-400" />
                            <span>Generasi Baru Gaming Edukasi Interaktif Indonesia</span>
                        </div>

                        <h1 className="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.15] text-white">
                            Belajar Matematika Jadi{' '}
                            <span className="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-orange-400 to-rose-500 inline-block drop-shadow-sm">
                                Petualangan 3D
                            </span>{' '}
                            Paling Mengasyikkan!
                        </h1>

                        <p className="text-base sm:text-lg text-slate-300 leading-relaxed max-w-xl">
                            Selamat tinggal rasa bosan! Melaju kencang di trek balapan, jawab tantangan perkalian, raih
                            prestasi lencana XP, dan tantang teman sekelas dalam duel seru real-time.
                        </p>

                        {/* CTA Buttons */}
                        <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                            <Link
                                href="/masuk-siswa"
                                className="px-7 py-4 rounded-2xl bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 hover:from-amber-300 hover:to-orange-400 text-slate-950 font-black text-base shadow-xl shadow-orange-500/25 transition-all duration-300 hover:scale-105 flex items-center justify-center gap-3 group"
                            >
                                <Gamepad2 className="w-6 h-6 group-hover:rotate-12 transition" />
                                <span>Mulai Main Sekarang (Siswa)</span>
                                <ArrowRight className="w-5 h-5 group-hover:translate-x-1 transition" />
                            </Link>

                            <Link
                                href="/login-guru"
                                className="px-6 py-4 rounded-2xl bg-slate-900/90 hover:bg-slate-800 text-white font-bold text-sm border border-slate-700/80 shadow-lg backdrop-blur-md transition-all duration-300 hover:border-indigo-500/50 flex items-center justify-center gap-2.5"
                            >
                                <GraduationCap className="w-5 h-5 text-indigo-400" />
                                <span>Area Guru & Pembuat Soal</span>
                            </Link>
                        </div>

                        {/* Feature Badges List */}
                        <div className="pt-4 flex flex-wrap items-center gap-4 text-xs font-semibold text-slate-400">
                            <div className="flex items-center gap-1.5 text-emerald-400">
                                <CheckCircle2 className="w-4 h-4" />
                                <span>100% Bebas Iklan</span>
                            </div>
                            <div className="flex items-center gap-1.5 text-indigo-400">
                                <CheckCircle2 className="w-4 h-4" />
                                <span>Tanpa Email Siswa (Privasi Aman)</span>
                            </div>
                            <div className="flex items-center gap-1.5 text-amber-400">
                                <CheckCircle2 className="w-4 h-4" />
                                <span>Anti-Lag Shared Hosting</span>
                            </div>
                        </div>
                    </div>

                    {/* Right Hero Column: 3D Interactive Showcase */}
                    <div className="lg:col-span-5 relative">
                        {/* Background 3D HTML5 Canvas for Polyhedral Core */}
                        <div className="relative rounded-3xl border border-indigo-500/30 bg-slate-950/80 backdrop-blur-2xl p-6 shadow-2xl shadow-indigo-950/50 overflow-hidden group">
                            {/* Canvas Element for 3D Geometry */}
                            <canvas
                                ref={canvasRef}
                                className="absolute inset-0 w-full h-full pointer-events-none opacity-85"
                            />

                            {/* Foreground Live 3D Race Card Simulation */}
                            <div className="relative z-10 space-y-4">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <span className="w-3 h-3 rounded-full bg-rose-500 animate-ping" />
                                        <span className="text-xs font-black uppercase tracking-wider text-amber-400">
                                            Live Game Preview
                                        </span>
                                    </div>
                                    <span className="text-[10px] px-2.5 py-1 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-bold">
                                        Balapan Perkalian
                                    </span>
                                </div>

                                {/* 3D Perspective Race Track Simulation Box */}
                                <div
                                    className={`relative h-44 rounded-2xl bg-gradient-to-b from-slate-900 to-indigo-950/90 border border-indigo-500/20 overflow-hidden flex flex-col justify-end p-4 transition-all duration-300 ${
                                        nitroActive ? 'scale-[1.02] shadow-xl shadow-amber-500/30 border-amber-500/60' : ''
                                    }`}
                                    style={{
                                        perspective: '600px',
                                    }}
                                >
                                    {/* 3D Grid Floor */}
                                    <div
                                        className="absolute inset-0 opacity-30 pointer-events-none"
                                        style={{
                                            backgroundImage:
                                                'linear-gradient(to right, #6366f1 1px, transparent 1px), linear-gradient(to bottom, #6366f1 1px, transparent 1px)',
                                            backgroundSize: '24px 24px',
                                            transform: 'rotateX(60deg) scale(1.5)',
                                            transformOrigin: '50% 100%',
                                        }}
                                    />

                                    {/* Racing Car Model (CSS 3D Car with Nitro Boost Flames) */}
                                    <div
                                        className={`relative mx-auto transition-transform duration-200 ${
                                            nitroActive ? '-translate-y-4 scale-110' : 'translate-y-0'
                                        }`}
                                    >
                                        <div className="w-20 h-10 rounded-xl bg-gradient-to-r from-amber-400 via-orange-500 to-rose-600 shadow-2xl flex items-center justify-center font-black text-slate-950 text-xs border border-yellow-200 relative">
                                            🏎️ PLAYER 1
                                            {/* Nitro Flame Particles */}
                                            {nitroActive && (
                                                <div className="absolute -bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-1">
                                                    <span className="w-2 h-5 bg-gradient-to-b from-amber-300 to-rose-500 rounded-full animate-bounce" />
                                                    <span className="w-3 h-7 bg-gradient-to-b from-yellow-200 to-orange-500 rounded-full animate-pulse" />
                                                    <span className="w-2 h-5 bg-gradient-to-b from-amber-300 to-rose-500 rounded-full animate-bounce" />
                                                </div>
                                            )}
                                        </div>
                                    </div>

                                    {/* Velocity readout */}
                                    <div className="flex items-center justify-between text-[11px] text-slate-300 mt-2 font-mono z-10">
                                        <span className="flex items-center gap-1">
                                            <Zap className="w-3.5 h-3.5 text-amber-400" />
                                            {nitroActive ? 'Kecepatan: 195 Km/j (NITRO!)' : 'Kecepatan: 85 Km/j'}
                                        </span>
                                        <span className="text-emerald-400 font-bold">Posisi: Juara #1</span>
                                    </div>
                                </div>

                                {/* Interactive Mini Multiplication Challenge */}
                                <div className="p-3.5 bg-slate-900/90 rounded-2xl border border-slate-800 space-y-2.5">
                                    <div className="flex items-center justify-between">
                                        <span className="text-xs font-bold text-slate-300">
                                            Coba Jawab Tantangan: <span className="text-amber-400 font-black text-sm">7 × 8 = ?</span>
                                        </span>
                                        <button
                                            onClick={triggerNitro}
                                            className="px-2.5 py-1 rounded-lg bg-orange-500 hover:bg-orange-400 text-slate-950 text-[10px] font-black uppercase flex items-center gap-1 shadow transition active:scale-95"
                                        >
                                            <Flame className="w-3 h-3 fill-current" />
                                            Tekan Gas!
                                        </button>
                                    </div>

                                    {/* Multi Choice Buttons */}
                                    <div className="grid grid-cols-4 gap-2">
                                        {[48, 54, 56, 64].map((opt) => (
                                            <button
                                                key={opt}
                                                onClick={() => handleAnswerClick(opt)}
                                                className={`py-2 rounded-xl text-xs font-black transition-all duration-200 ${
                                                    testAnswer === opt
                                                        ? opt === 56
                                                            ? 'bg-emerald-500 text-slate-950 scale-105 shadow-lg shadow-emerald-500/30'
                                                            : 'bg-rose-500 text-white'
                                                        : 'bg-slate-800 hover:bg-indigo-600 text-slate-200 hover:text-white'
                                                }`}
                                            >
                                                {opt}
                                            </button>
                                        ))}
                                    </div>

                                    {testFeedback && (
                                        <div
                                            className={`text-xs font-bold text-center py-1 rounded-lg ${
                                                testAnswer === 56
                                                    ? 'text-emerald-400 bg-emerald-950/40 border border-emerald-500/30'
                                                    : 'text-rose-400 bg-rose-950/40 border border-rose-500/30'
                                            }`}
                                        >
                                            {testFeedback}
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* 3D Tilt Feature Highlights Cards */}
                <div className="mt-20 space-y-6">
                    <div className="text-center max-w-2xl mx-auto space-y-2">
                        <div className="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-amber-400">
                            <Sparkles className="w-4 h-4" />
                            <span>Fitur Utama Platform</span>
                        </div>
                        <h2 className="text-2xl sm:text-3xl font-black text-white">
                            Didesain Sesuai Kebutuhan Nyata Sekolah Indonesia
                        </h2>
                        <p className="text-xs sm:text-sm text-slate-400">
                            Platform ringan dan responsif, dibangun dengan arsitektur modern anti-beban untuk server shared hosting.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {/* Card 1 */}
                        <div className="group relative rounded-3xl bg-slate-900/60 hover:bg-slate-900/90 border border-slate-800 hover:border-amber-500/40 p-6 shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                            <div className="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center mb-4 group-hover:scale-110 transition duration-300">
                                <Gamepad2 className="w-6 h-6" />
                            </div>
                            <h3 className="text-lg font-black text-white mb-2 group-hover:text-amber-400 transition">
                                Balapan Perkalian SD
                            </h3>
                            <p className="text-xs text-slate-400 leading-relaxed">
                                Game balapan mobil seru untuk siswa SD (kelas 4-6). Jawaban operasi matematika yang akurat
                                dan cepat memberikan dorongan nitro akselerasi untuk menyalip mobil lawan.
                            </p>
                            <div className="mt-4 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-bold text-amber-400">
                                <span>Kurikulum SD Kelas 4-6</span>
                                <ChevronRight className="w-4 h-4 group-hover:translate-x-1 transition" />
                            </div>
                        </div>

                        {/* Card 2 */}
                        <div className="group relative rounded-3xl bg-slate-900/60 hover:bg-slate-900/90 border border-slate-800 hover:border-indigo-500/40 p-6 shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                            <div className="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center mb-4 group-hover:scale-110 transition duration-300">
                                <Users className="w-6 h-6" />
                            </div>
                            <h3 className="text-lg font-black text-white mb-2 group-hover:text-indigo-400 transition">
                                Kuis Duel Real-Time SMP
                            </h3>
                            <p className="text-xs text-slate-400 leading-relaxed">
                                Arena kompetisi adu cepat dan ketepatan berpikir kritis untuk siswa SMP. Berisi 2-4 pemain
                                dalam satu arena duel langsung dengan skor kilat dan reaksi emoji interaktif.
                            </p>
                            <div className="mt-4 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-bold text-indigo-400">
                                <span>Kurikulum SMP Fase D</span>
                                <ChevronRight className="w-4 h-4 group-hover:translate-x-1 transition" />
                            </div>
                        </div>

                        {/* Card 3 */}
                        <div className="group relative rounded-3xl bg-slate-900/60 hover:bg-slate-900/90 border border-slate-800 hover:border-emerald-500/40 p-6 shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                            <div className="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center mb-4 group-hover:scale-110 transition duration-300">
                                <Trophy className="w-6 h-6" />
                            </div>
                            <h3 className="text-lg font-black text-white mb-2 group-hover:text-emerald-400 transition">
                                Prestasi, XP & Gamifikasi
                            </h3>
                            <p className="text-xs text-slate-400 leading-relaxed">
                                Sistem level dinamis, 6 lencana penghargaan otomatis, daily streak belajar, dan leaderboard
                                kelas yang memotivasi siswa untuk terus berlatih tanpa merasa tertekan.
                            </p>
                            <div className="mt-4 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-bold text-emerald-400">
                                <span>Leaderboard & Badges</span>
                                <ChevronRight className="w-4 h-4 group-hover:translate-x-1 transition" />
                            </div>
                        </div>
                    </div>
                </div>

                {/* Live Stats Counters Bar */}
                {stats && (
                    <div className="mt-16 p-6 rounded-3xl bg-slate-950/80 border border-slate-800/80 shadow-2xl flex flex-wrap items-center justify-around gap-6 text-center">
                        <div className="space-y-1">
                            <div className="text-3xl font-black text-amber-400 font-mono">{stats.schools}</div>
                            <div className="text-xs text-slate-400 font-bold uppercase tracking-wider">Sekolah Terdaftar</div>
                        </div>
                        <div className="w-px h-10 bg-slate-800 hidden sm:block" />
                        <div className="space-y-1">
                            <div className="text-3xl font-black text-indigo-400 font-mono">{stats.teachers}</div>
                            <div className="text-xs text-slate-400 font-bold uppercase tracking-wider">Guru Penggerak</div>
                        </div>
                        <div className="w-px h-10 bg-slate-800 hidden sm:block" />
                        <div className="space-y-1">
                            <div className="text-3xl font-black text-emerald-400 font-mono">{stats.students}</div>
                            <div className="text-xs text-slate-400 font-bold uppercase tracking-wider">Siswa Aktif</div>
                        </div>
                        <div className="w-px h-10 bg-slate-800 hidden sm:block" />
                        <div className="space-y-1">
                            <div className="text-3xl font-black text-rose-400 font-mono">{stats.questions}</div>
                            <div className="text-xs text-slate-400 font-bold uppercase tracking-wider">Bank Soal Siap Pakai</div>
                        </div>
                    </div>
                )}
            </main>

            {/* Modern Footer with Quick Links */}
            <footer className="mt-16 border-t border-slate-800/80 bg-slate-950/90 py-8 px-6 text-xs text-slate-400 backdrop-blur-md">
                <div className="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <div className="w-7 h-7 rounded-xl bg-gradient-to-tr from-amber-400 to-orange-500 flex items-center justify-center text-slate-950 font-black text-xs">
                            🎮
                        </div>
                        <span>
                            &copy; {new Date().getFullYear()} <strong>{appName}</strong>. Seluruh Hak Cipta Dilindungi.
                        </span>
                    </div>

                    <div className="flex items-center gap-6 text-slate-400 font-medium">
                        <Link href="/masuk-siswa" className="hover:text-emerald-400 transition">
                            Masuk Siswa
                        </Link>
                        <Link href="/login-guru" className="hover:text-indigo-400 transition">
                            Portal Guru
                        </Link>
                        <Link href="/login-guru" className="hover:text-amber-400 transition flex items-center gap-1">
                            <Shield className="w-3.5 h-3.5" />
                            Superadmin
                        </Link>
                    </div>
                </div>
            </footer>
        </div>
    );
}
