<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperadminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-superadmin
                            {--name= : Nama lengkap superadmin}
                            {--email= : Alamat email superadmin}
                            {--password= : Kata sandi superadmin}';

    protected $aliases = ['make:superadmin'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat atau memperbarui akun Superadmin Platform EduGame Indonesia';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Masukkan Nama Superadmin', 'Administrator Utama');
        $email = $this->option('email') ?: $this->ask('Masukkan Email Superadmin', 'admin@edugame.id');
        $password = $this->option('password') ?: $this->secret('Masukkan Kata Sandi Superadmin (default: admin12345)') ?: 'admin12345';

        // Pastikan ada sekolah default untuk relasi jika diperlukan
        $defaultSchool = School::firstOrCreate(
            ['nama' => 'Pusat Kendali EduGame Indonesia'],
            ['jenjang' => 'SMA', 'status_lisensi' => 'aktif']
        );

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update([
                'name' => $name,
                'role' => 'admin',
                'status' => 'active',
                'password' => Hash::make($password),
                'school_id' => $user->school_id ?: $defaultSchool->id,
            ]);

            $this->info("Akun Superadmin [{$email}] berhasil diperbarui dan kata sandi di-reset!");
        } else {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'status' => 'active',
                'school_id' => $defaultSchool->id,
            ]);

            $this->info("Akun Superadmin [{$email}] berhasil dibuat!");
        }

        // Assign Spatie Role superadmin jika role terdaftar
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
            $user->assignRole('superadmin');
        }

        $this->table(
            ['Parameter', 'Nilai'],
            [
                ['Nama', $user->name],
                ['Email', $user->email],
                ['Peran (Role)', 'admin (Superadmin)'],
                ['Kata Sandi', '(Sesuai yang dimasukkan / admin12345)'],
                ['URL Login', url('/login-guru')],
            ]
        );

        return Command::SUCCESS;
    }
}
