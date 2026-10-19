<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SystemSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Sistem & Keamanan';

    protected static ?string $navigationLabel = 'Pengaturan Platform';

    protected static ?string $title = 'Pengaturan Konfigurasi Sistem';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.system-settings';

    public ?array $data = [];
    public string $recentLog = '';

    public function mount(): void
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        $this->form->fill([
            'app_name' => $settings['app_name'] ?? 'EduGame Platform',
            'app_tagline' => $settings['app_tagline'] ?? 'Belajar Interaktif & Menyenangkan Berbasis Gamifikasi',
            'support_email' => $settings['support_email'] ?? 'bantuan@edugame.id',
            'maintenance_mode' => ($settings['maintenance_mode'] ?? 'false') === 'true',
            'pin_length' => $settings['pin_length'] ?? '4',
            'login_max_attempts' => $settings['login_max_attempts'] ?? '5',
            'require_2fa_superadmin' => ($settings['require_2fa_superadmin'] ?? 'true') === 'true',
            'mail_host' => $settings['mail_host'] ?? 'smtp.mailtrap.io',
            'mail_port' => $settings['mail_port'] ?? '2525',
            'mail_username' => $settings['mail_username'] ?? '',
            'mail_password' => $settings['mail_password'] ?? '',
            'mail_encryption' => $settings['mail_encryption'] ?? 'tls',
            'mail_from_address' => $settings['mail_from_address'] ?? 'noreply@edugame.id',
        ]);

        $this->loadRecentLog();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('SettingsTabs')
                    ->tabs([
                        // Tab 1: Identitas Platform
                        Forms\Components\Tabs\Tab::make('Identitas Platform')
                            ->icon('heroicon-m-globe-alt')
                            ->schema([
                                Forms\Components\TextInput::make('app_name')
                                    ->label('Nama Platform')
                                    ->required(),

                                Forms\Components\TextInput::make('app_tagline')
                                    ->label('Tagline / Slogan')
                                    ->required(),

                                Forms\Components\TextInput::make('support_email')
                                    ->label('Email Bantuan / CS')
                                    ->email()
                                    ->required(),

                                Forms\Components\Toggle::make('maintenance_mode')
                                    ->label('Mode Pemeliharaan (Maintenance Mode)')
                                    ->helperText('Jika aktif, halaman publik dan siswa akan menampilkan pesan pemeliharaan server.'),
                            ])->columns(2),

                        // Tab 2: Kebijakan Keamanan
                        Forms\Components\Tabs\Tab::make('Kebijakan Keamanan')
                            ->icon('heroicon-m-shield-check')
                            ->schema([
                                Forms\Components\TextInput::make('pin_length')
                                    ->label('Panjang Digit PIN Siswa')
                                    ->numeric()
                                    ->default(4)
                                    ->required(),

                                Forms\Components\TextInput::make('login_max_attempts')
                                    ->label('Batas Maksimal Percobaan Login Gagal (Rate Limit)')
                                    ->numeric()
                                    ->default(5)
                                    ->required(),

                                Forms\Components\Toggle::make('require_2fa_superadmin')
                                    ->label('Wajibkan 2FA (TOTP) untuk Seluruh Akun Superadmin')
                                    ->default(true),
                            ])->columns(3),

                        // Tab 3: SMTP Email
                        Forms\Components\Tabs\Tab::make('Konfigurasi SMTP Email')
                            ->icon('heroicon-m-envelope')
                            ->schema([
                                Forms\Components\TextInput::make('mail_host')->label('SMTP Host')->required(),
                                Forms\Components\TextInput::make('mail_port')->label('SMTP Port')->numeric()->required(),
                                Forms\Components\TextInput::make('mail_username')->label('SMTP Username'),
                                Forms\Components\TextInput::make('mail_password')->label('SMTP Password')->password(),
                                Forms\Components\Select::make('mail_encryption')
                                    ->label('Enkripsi')
                                    ->options([
                                        'tls' => 'TLS',
                                        'ssl' => 'SSL',
                                        'none' => 'None',
                                    ]),
                                Forms\Components\TextInput::make('mail_from_address')->label('Alamat Pengirim')->email()->required(),
                            ])->columns(3),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $formData = $this->form->getState();

        foreach ($formData as $key => $value) {
            Setting::set($key, is_bool($value) ? ($value ? 'true' : 'false') : (string) $value);
        }

        Notification::make()
            ->title('Pengaturan sistem berhasil diperbarui dan disimpan.')
            ->success()
            ->send();
    }

    public function clearSystemCache(): void
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');

        Notification::make()
            ->title('Cache sistem, konfigurasi, dan tampilan berhasil dibersihkan.')
            ->success()
            ->send();
    }

    public function triggerDatabaseBackup(): void
    {
        try {
            Artisan::call('backup:run', ['--only-db' => true]);
            Notification::make()
                ->title('Proses cadangan database berhasil dijalankan.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Peringatan Cadangan: ' . $e->getMessage())
                ->warning()
                ->send();
        }
    }

    public function loadRecentLog(): void
    {
        $logPath = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            $lines = file($logPath);
            $lastLines = array_slice($lines, -50);
            $this->recentLog = implode('', $lastLines);
        } else {
            $this->recentLog = 'Belum ada log aktivitas aplikasi (file log kosong).';
        }
    }
}
