/**
 * Konfigurasi Tema Game EduGame Indonesia
 *
 * Seluruh aset visual, warna, karakter, dan referensi suara dirujuk lewat file konfigurasi ini
 * agar mudah diganti dengan ilustrasi/skin lain di masa depan tanpa mengubah logika inti game.
 */

export interface CarTheme {
    id: string;
    name: string;
    bodyColor: string;
    stripColor: string;
    glassColor: string;
    wheelColor: string;
    lightColor: string;
}

export interface TrackTheme {
    id: string;
    name: string;
    asphaltColor: string;
    asphaltDarkColor: string;
    laneColor: string;
    curbColorA: string;
    curbColorB: string;
    grassColor: string;
    skyGradient: [string, string];
}

export interface GameThemeConfig {
    playerCar: CarTheme;
    botCars: CarTheme[];
    track: TrackTheme;
    ui: {
        primary: string;
        success: string;
        danger: string;
        warning: string;
        nitroFlame: string[];
        smokeColor: string;
    };
    audio: {
        defaultMuted: boolean;
        masterVolume: number;
    };
}

export const defaultRacingTheme: GameThemeConfig = {
    playerCar: {
        id: 'player_red_blaze',
        name: 'Mobil Merah Juara',
        bodyColor: '#ef4444',     // Crimson Red
        stripColor: '#facc15',    // Gold Yellow
        glassColor: '#38bdf8',    // Cyan windshield
        wheelColor: '#1e293b',    // Slate Black
        lightColor: '#fef08a',    // Warm light
    },
    botCars: [
        {
            id: 'bot_boni',
            name: 'Boni (Bot Biru)',
            bodyColor: '#3b82f6', // Cobalt Blue
            stripColor: '#ffffff',
            glassColor: '#7dd3fc',
            wheelColor: '#1e293b',
            lightColor: '#fef08a',
        },
        {
            id: 'bot_cici',
            name: 'Cici (Bot Hijau)',
            bodyColor: '#10b981', // Emerald Green
            stripColor: '#fbbf24',
            glassColor: '#6ee7b7',
            wheelColor: '#1e293b',
            lightColor: '#fef08a',
        },
    ],
    track: {
        id: 'circuit_nusantara',
        name: 'Sirkuit Juara Nusantara',
        asphaltColor: '#334155',      // Slate 700
        asphaltDarkColor: '#1e293b',  // Slate 800
        laneColor: '#f8fafc',         // White dash
        curbColorA: '#dc2626',        // Red
        curbColorB: '#ffffff',        // White
        grassColor: '#15803d',        // Green 700
        skyGradient: ['#0f172a', '#1e1b4b'], // Night/dawn glow
    },
    ui: {
        primary: '#6366f1',
        success: '#10b981',
        danger: '#ef4444',
        warning: '#f59e0b',
        nitroFlame: ['#38bdf8', '#60a5fa', '#f59e0b', '#ef4444'],
        smokeColor: 'rgba(148, 163, 184, 0.7)',
    },
    audio: {
        defaultMuted: true, // Sesuai kriteria: audio mati secara default sampai pengguna mengaktifkan
        masterVolume: 0.7,
    },
};
