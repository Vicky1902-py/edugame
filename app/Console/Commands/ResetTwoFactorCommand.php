<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetTwoFactorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-2fa {email : Alamat email akun pengguna atau superadmin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset konfigurasi 2FA TOTP akun pengguna / superadmin jika kehilangan akses perangkat';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Pengguna dengan email [{$email}] tidak ditemukan di sistem.");
            return Command::FAILURE;
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        $this->info("2FA TOTP untuk akun [{$user->name}] ({$email}) berhasil diatur ulang (dinonaktifkan).");
        $this->comment("Pengguna sekarang dapat login menggunakan email & kata sandi normal, lalu mengonfigurasi ulang 2FA di profil.");

        return Command::SUCCESS;
    }
}
