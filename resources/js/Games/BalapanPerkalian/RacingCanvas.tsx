import React, { useEffect, useRef } from 'react';
import { defaultRacingTheme, CarTheme, GameThemeConfig } from '../theme.config';

interface RacingCanvasProps {
    playerProgress: number;     // 0 to 100 (%)
    bot1Progress: number;       // 0 to 100 (%)
    bot2Progress: number;       // 0 to 100 (%)
    isNitroActive: boolean;     // Efek nitro saat benar
    isStumbling: boolean;       // Efek asap/goyang saat salah
    theme?: GameThemeConfig;
}

export default function RacingCanvas({
    playerProgress,
    bot1Progress,
    bot2Progress,
    isNitroActive,
    isStumbling,
    theme = defaultRacingTheme,
}: RacingCanvasProps) {
    const canvasRef = useRef<HTMLCanvasElement | null>(null);

    // Animasi state ref untuk kelancaran rendering 60 FPS
    const animState = useRef({
        roadOffset: 0,
        playerY: 0,
        bot1Y: 0,
        bot2Y: 0,
        shakeX: 0,
        flameParticles: [] as Array<{ x: number; y: number; size: number; alpha: number; color: string }>,
        smokeParticles: [] as Array<{ x: number; y: number; size: number; alpha: number }>,
    });

    useEffect(() => {
        const canvas = canvasRef.current;
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        let animationFrameId: number;

        const render = () => {
            const width = canvas.width;
            const height = canvas.height;

            // 1. Bersihkan frame
            ctx.clearRect(0, 0, width, height);

            // 2. Kecepatan scrolling jalan (jika nitro lebih kencang, jika stumble lebih lambat)
            const speed = isNitroActive ? 12 : isStumbling ? 2 : 6;
            animState.current.roadOffset = (animState.current.roadOffset + speed) % 40;

            // 3. Gambar background rumput
            ctx.fillStyle = theme.track.grassColor;
            ctx.fillRect(0, 0, width, height);

            // 4. Gambar jalan aspal di tengah
            const roadWidth = Math.min(width * 0.85, 480);
            const roadX = (width - roadWidth) / 2;

            // Aspal
            ctx.fillStyle = theme.track.asphaltColor;
            ctx.fillRect(roadX, 0, roadWidth, height);

            // Kerb samping kiri & kanan (Merah-Putih bergantian)
            const curbWidth = 14;
            const curbSegment = 20;
            const curbCount = Math.ceil(height / curbSegment) + 2;

            for (let i = -1; i < curbCount; i++) {
                const y = i * curbSegment + (animState.current.roadOffset % (curbSegment * 2));
                const isRed = Math.floor(y / curbSegment) % 2 === 0;
                ctx.fillStyle = isRed ? theme.track.curbColorA : theme.track.curbColorB;

                // Kiri
                ctx.fillRect(roadX - curbWidth, y, curbWidth, curbSegment);
                // Kanan
                ctx.fillRect(roadX + roadWidth, y, curbWidth, curbSegment);
            }

            // Garis batas jalur (3 jalur = 2 garis putus-putus)
            const laneWidth = roadWidth / 3;
            ctx.strokeStyle = theme.track.laneColor;
            ctx.lineWidth = 4;
            ctx.setLineDash([20, 20]);
            ctx.lineDashOffset = -animState.current.roadOffset;

            // Garis pembatas 1 & 2
            ctx.beginPath();
            ctx.moveTo(roadX + laneWidth, 0);
            ctx.lineTo(roadX + laneWidth, height);
            ctx.moveTo(roadX + laneWidth * 2, 0);
            ctx.lineTo(roadX + laneWidth * 2, height);
            ctx.stroke();
            ctx.setLineDash([]); // Reset dash

            // 5. Hitung posisi mobil
            // Mobil bergerak dari bawah (start) mendekati atas (finish), namun tetap di viewport
            // Posisi Y dasar: Jalur tengah = Player, Kiri = Bot 1, Kanan = Bot 2
            const baseY = height * 0.65;
            const progressDelta = (playerProgress - 50) * 2; // relative shift

            // Lerp posisi target Y
            const targetPlayerY = baseY - (progressDelta * 0.8) - (isNitroActive ? 25 : 0) + (isStumbling ? 20 : 0);
            animState.current.playerY += (targetPlayerY - animState.current.playerY) * 0.1;

            const targetBot1Y = baseY - ((bot1Progress - 50) * 1.6);
            animState.current.bot1Y += (targetBot1Y - animState.current.bot1Y) * 0.1;

            const targetBot2Y = baseY - ((bot2Progress - 50) * 1.6);
            animState.current.bot2Y += (targetBot2Y - animState.current.bot2Y) * 0.1;

            // Goyangan saat stumble (salah)
            if (isStumbling) {
                animState.current.shakeX = (Math.random() - 0.5) * 8;
            } else {
                animState.current.shakeX = 0;
            }

            // Koordinat X tengah tiap jalur
            const bot1X = roadX + laneWidth * 0.5;
            const playerX = roadX + laneWidth * 1.5 + animState.current.shakeX;
            const bot2X = roadX + laneWidth * 2.5;

            // 6. Gambar Partikel Asap saat Stumble
            if (isStumbling) {
                animState.current.smokeParticles.push({
                    x: playerX + (Math.random() - 0.5) * 15,
                    y: animState.current.playerY + 25,
                    size: Math.random() * 8 + 6,
                    alpha: 0.8,
                });
            }

            // Render asap
            for (let i = animState.current.smokeParticles.length - 1; i >= 0; i--) {
                const p = animState.current.smokeParticles[i];
                p.y += 3;
                p.size += 0.4;
                p.alpha -= 0.03;

                if (p.alpha <= 0) {
                    animState.current.smokeParticles.splice(i, 1);
                    continue;
                }

                ctx.fillStyle = `rgba(160, 174, 192, ${p.alpha})`;
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                ctx.fill();
            }

            // 7. Gambar Partikel Api Nitro saat Benar
            if (isNitroActive) {
                for (let k = 0; k < 3; k++) {
                    const colors = theme.ui.nitroFlame;
                    animState.current.flameParticles.push({
                        x: playerX + (k === 0 ? -9 : k === 1 ? 9 : 0) + (Math.random() - 0.5) * 4,
                        y: animState.current.playerY + 38,
                        size: Math.random() * 7 + 4,
                        alpha: 1,
                        color: colors[Math.floor(Math.random() * colors.length)],
                    });
                }
            }

            // Render api nitro
            for (let i = animState.current.flameParticles.length - 1; i >= 0; i--) {
                const fp = animState.current.flameParticles[i];
                fp.y += 5;
                fp.size = Math.max(1, fp.size - 0.3);
                fp.alpha -= 0.06;

                if (fp.alpha <= 0) {
                    animState.current.flameParticles.splice(i, 1);
                    continue;
                }

                ctx.fillStyle = fp.color;
                ctx.globalAlpha = fp.alpha;
                ctx.beginPath();
                ctx.arc(fp.x, fp.y, fp.size, 0, Math.PI * 2);
                ctx.fill();
                ctx.globalAlpha = 1;
            }

            // 8. Gambar Mobil
            // Bot 1 (Kiri)
            drawCar(ctx, bot1X, animState.current.bot1Y, theme.botCars[0], 'Boni');
            // Bot 2 (Kanan)
            drawCar(ctx, bot2X, animState.current.bot2Y, theme.botCars[1], 'Cici');
            // Player (Tengah)
            drawCar(ctx, playerX, animState.current.playerY, theme.playerCar, 'Kamu (Juara)', true);

            // 9. Loop animasi
            animationFrameId = requestAnimationFrame(render);
        };

        // Mulai animasi
        animationFrameId = requestAnimationFrame(render);

        return () => {
            cancelAnimationFrame(animationFrameId);
        };
    }, [playerProgress, bot1Progress, bot2Progress, isNitroActive, isStumbling, theme]);

    /**
     * Helper menggambar mobil balap sport tampak atas
     */
    const drawCar = (
        ctx: CanvasRenderingContext2D,
        x: number,
        y: number,
        carTheme: CarTheme,
        name: string,
        isPlayer: boolean = false
    ) => {
        const w = 34;
        const h = 64;

        ctx.save();
        ctx.translate(x, y);

        // Bayangan mobil
        ctx.fillStyle = 'rgba(0, 0, 0, 0.35)';
        ctx.beginPath();
        ctx.roundRect(-w / 2 + 3, -h / 2 + 5, w, h, 8);
        ctx.fill();

        // 4 Roda ban hitam
        ctx.fillStyle = carTheme.wheelColor;
        // Roda depan
        ctx.fillRect(-w / 2 - 3, -h / 2 + 8, 4, 13);
        ctx.fillRect(w / 2 - 1, -h / 2 + 8, 4, 13);
        // Roda belakang
        ctx.fillRect(-w / 2 - 3, h / 2 - 20, 4, 13);
        ctx.fillRect(w / 2 - 1, h / 2 - 20, 4, 13);

        // Bodi utama mobil
        ctx.fillStyle = carTheme.bodyColor;
        ctx.beginPath();
        ctx.roundRect(-w / 2, -h / 2, w, h, [12, 12, 6, 6]);
        ctx.fill();

        // Garis balap (Racing Stripe) tengah
        ctx.fillStyle = carTheme.stripColor;
        ctx.fillRect(-3, -h / 2, 6, h);

        // Kaca depan (Windshield)
        ctx.fillStyle = carTheme.glassColor;
        ctx.beginPath();
        ctx.roundRect(-w / 2 + 5, -h / 2 + 14, w - 10, 15, 3);
        ctx.fill();

        // Kaca belakang
        ctx.fillStyle = carTheme.glassColor;
        ctx.beginPath();
        ctx.roundRect(-w / 2 + 6, h / 2 - 18, w - 12, 8, 2);
        ctx.fill();

        // Sayap spoiler belakang
        ctx.fillStyle = carTheme.stripColor;
        ctx.fillRect(-w / 2 + 2, h / 2 - 4, w - 4, 4);

        // Lampu depan menyala
        ctx.fillStyle = carTheme.lightColor;
        ctx.beginPath();
        ctx.arc(-w / 2 + 6, -h / 2 + 2, 2.5, 0, Math.PI * 2);
        ctx.arc(w / 2 - 6, -h / 2 + 2, 2.5, 0, Math.PI * 2);
        ctx.fill();

        // Label Nama Pengemudi di atas mobil
        ctx.font = isPlayer ? 'bold 11px sans-serif' : '10px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillStyle = isPlayer ? '#fef08a' : '#ffffff';
        ctx.shadowColor = 'rgba(0,0,0,0.8)';
        ctx.shadowBlur = 4;
        ctx.fillText(name, 0, -h / 2 - 8);

        ctx.restore();
    };

    return (
        <div className="relative w-full rounded-3xl overflow-hidden shadow-2xl border-4 border-slate-700/60 bg-slate-900 aspect-[16/9] max-h-[340px]">
            <canvas
                ref={canvasRef}
                width={640}
                height={360}
                className="w-full h-full object-cover block"
            />
            {/* Speedometer & Lap Bar Overlay */}
            <div className="absolute top-3 inset-x-4 flex items-center justify-between pointer-events-none">
                <div className="flex items-center gap-2 bg-slate-950/80 backdrop-blur-md px-3.5 py-1.5 rounded-full border border-white/20 text-xs font-black text-amber-300">
                    <span>🏁 FINISH</span>
                    <div className="w-24 sm:w-36 h-2.5 rounded-full bg-slate-800 overflow-hidden border border-white/10">
                        <div
                            className="h-full rounded-full bg-gradient-to-r from-amber-400 to-emerald-400 transition-all duration-300"
                            style={{ width: `${Math.min(100, Math.max(0, playerProgress))}%` }}
                        />
                    </div>
                    <span>{Math.round(playerProgress)}%</span>
                </div>

                {isNitroActive && (
                    <div className="animate-bounce bg-gradient-to-r from-amber-500 to-rose-600 text-white font-black text-xs px-3 py-1 rounded-full shadow-lg border border-amber-300">
                        🔥 NITRO SPEED BOOST!
                    </div>
                )}
            </div>
        </div>
    );
}
