/**
 * Web Audio API Sound Synthesizer
 *
 * Menghasilkan efek audio prosedural tanpa file audio eksternal (0 KB aset jaringan, 0 ms loading).
 * Sesuai aturan: Audio mati secara default sampai pengguna mengaktifkannya, dan terdapat tombol toggle mute.
 */

class AudioSynthesizer {
    private ctx: AudioContext | null = null;
    private isMuted: boolean = true; // Default: muted sesuai instruksi

    constructor() {
        // Jangan auto-play audio sampai interaksi pertama
    }

    private initContext(): AudioContext | null {
        if (typeof window === 'undefined') return null;

        if (!this.ctx) {
            const AudioCtx = window.AudioContext || (window as unknown as { webkitAudioContext: typeof AudioContext }).webkitAudioContext;
            if (AudioCtx) {
                this.ctx = new AudioCtx();
            }
        }

        if (this.ctx && this.ctx.state === 'suspended') {
            this.ctx.resume();
        }

        return this.ctx;
    }

    public toggleMute(): boolean {
        this.isMuted = !this.isMuted;
        if (!this.isMuted) {
            this.initContext();
            // Mainkan nada aktivasi lembut
            this.playTick();
        }
        return this.isMuted;
    }

    public setMuted(muted: boolean): void {
        this.isMuted = muted;
        if (!this.isMuted) {
            this.initContext();
        }
    }

    public getIsMuted(): boolean {
        return this.isMuted;
    }

    /**
     * Nada benar: arpeggio ceria (Do - Mi - Sol - Do Tinggi)
     */
    public playCorrect(): void {
        if (this.isMuted) return;
        const ctx = this.initContext();
        if (!ctx) return;

        const now = ctx.currentTime;
        const notes = [523.25, 659.25, 783.99, 1046.5]; // C5, E5, G5, C6

        notes.forEach((freq, idx) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'triangle';
            osc.frequency.setValueAtTime(freq, now + idx * 0.07);

            gain.gain.setValueAtTime(0.001, now + idx * 0.07);
            gain.gain.exponentialRampToValueAtTime(0.25, now + idx * 0.07 + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.001, now + idx * 0.07 + 0.22);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start(now + idx * 0.07);
            osc.stop(now + idx * 0.07 + 0.25);
        });
    }

    /**
     * Nada salah: nada rendah menurun (Sawtooth buzz)
     */
    public playWrong(): void {
        if (this.isMuted) return;
        const ctx = this.initContext();
        if (!ctx) return;

        const now = ctx.currentTime;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = 'sawtooth';
        osc.frequency.setValueAtTime(220, now);
        osc.frequency.linearRampToValueAtTime(110, now + 0.35);

        gain.gain.setValueAtTime(0.2, now);
        gain.gain.exponentialRampToValueAtTime(0.01, now + 0.35);

        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.start(now);
        osc.stop(now + 0.38);
    }

    /**
     * Efek Nitro: Whoosh deru angin berakselerasi
     */
    public playNitro(): void {
        if (this.isMuted) return;
        const ctx = this.initContext();
        if (!ctx) return;

        const now = ctx.currentTime;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = 'sine';
        osc.frequency.setValueAtTime(150, now);
        osc.frequency.exponentialRampToValueAtTime(800, now + 0.4);

        gain.gain.setValueAtTime(0.05, now);
        gain.gain.linearRampToValueAtTime(0.3, now + 0.15);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.5);

        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.start(now);
        osc.stop(now + 0.52);
    }

    /**
     * Efek Detik Countdown: Ketukan kayu lembut
     */
    public playTick(): void {
        if (this.isMuted) return;
        const ctx = this.initContext();
        if (!ctx) return;

        const now = ctx.currentTime;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, now);

        gain.gain.setValueAtTime(0.15, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.05);

        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.start(now);
        osc.stop(now + 0.06);
    }

    /**
     * Efek Juara: Fanfare meriah
     */
    public playVictory(): void {
        if (this.isMuted) return;
        const ctx = this.initContext();
        if (!ctx) return;

        const now = ctx.currentTime;
        const melody = [
            { f: 523.25, d: 0.15 }, // C5
            { f: 659.25, d: 0.15 }, // E5
            { f: 783.99, d: 0.15 }, // G5
            { f: 1046.5, d: 0.45 }, // C6
        ];

        let offset = 0;
        melody.forEach((note) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'triangle';
            osc.frequency.setValueAtTime(note.f, now + offset);

            gain.gain.setValueAtTime(0.3, now + offset);
            gain.gain.exponentialRampToValueAtTime(0.001, now + offset + note.d);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start(now + offset);
            osc.stop(now + offset + note.d + 0.05);

            offset += note.d * 0.9;
        });
    }
}

export const sound = new AudioSynthesizer();
