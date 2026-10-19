<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\ImpersonationLog;
use App\Models\School;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Manajemen Pengguna & Sekolah';

    protected static ?string $navigationLabel = 'Semua Pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Daftar Pengguna';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Akun')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email / Username')
                            ->email(fn (Forms\Get $get) => $get('role') !== 'siswa')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('role')
                            ->label('Peran (Role)')
                            ->options([
                                'admin' => 'Superadmin / Admin Platform',
                                'guru' => 'Guru (Pendidik)',
                                'siswa' => 'Siswa',
                            ])
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('status')
                            ->label('Status Akun')
                            ->options([
                                'active' => 'Aktif',
                                'suspended' => 'Ditangguhkan (Suspended)',
                            ])
                            ->default('active')
                            ->required(),

                        Forms\Components\Select::make('school_id')
                            ->label('Sekolah')
                            ->relationship('school', 'nama')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\TextInput::make('password')
                            ->label('Kata Sandi Baru')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create' && fn (Forms\Get $get) => $get('role') !== 'siswa')
                            ->visible(fn (Forms\Get $get) => $get('role') !== 'siswa'),

                        Forms\Components\TextInput::make('pin')
                            ->label('PIN Siswa (4 Digit Angka)')
                            ->numeric()
                            ->length(4)
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $record) {
                                // Pin disimpan sebagai hash, form tidak menampilkan nilai asli
                            })
                            ->visible(fn (Forms\Get $get) => $get('role') === 'siswa')
                            ->helperText('Hanya angka 4 digit, contoh: 1234'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email / Username')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'guru' => 'warning',
                        'siswa' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('school.nama')
                    ->label('Sekolah')
                    ->searchable()
                    ->placeholder('Tanpa Sekolah'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Login Terakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('Belum pernah'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Filter Peran')
                    ->options([
                        'admin' => 'Superadmin',
                        'guru' => 'Guru',
                        'siswa' => 'Siswa',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'active' => 'Aktif',
                        'suspended' => 'Ditangguhkan',
                    ]),

                Tables\Filters\SelectFilter::make('school_id')
                    ->label('Filter Sekolah')
                    ->relationship('school', 'nama')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    // 1. Impersonate (Masuk sebagai pengguna lain)
                    Tables\Actions\Action::make('impersonate')
                        ->label('Impersonate')
                        ->icon('heroicon-m-arrow-right-on-rectangle')
                        ->color('warning')
                        ->visible(fn (User $record) => !$record->isSuperadmin() && auth()->user()->isSuperadmin())
                        ->form([
                            Forms\Components\TextInput::make('reason')
                                ->label('Alasan Impersonasi (Wajib dicatat)')
                                ->placeholder('Contoh: Bantuan teknis konfigurasi kelas')
                                ->required(),
                        ])
                        ->action(function (User $record, array $data) {
                            $superadmin = auth()->user();

                            // Catat log impersonasi
                            $log = ImpersonationLog::create([
                                'impersonator_id' => $superadmin->id,
                                'impersonated_id' => $record->id,
                                'started_at' => now(),
                                'ip_address' => request()->ip(),
                                'reason' => $data['reason'],
                            ]);

                            // Simpan session
                            session()->put('impersonator_id', $superadmin->id);
                            session()->put('impersonation_log_id', $log->id);

                            // Login sebagai user target
                            Auth::login($record);

                            // Redirect ke dashboard yang sesuai
                            if ($record->role === 'guru') {
                                return redirect()->route('teacher.dashboard');
                            }

                            if ($record->role === 'siswa') {
                                return redirect()->route('student.dashboard');
                            }

                            return redirect()->route('home');
                        }),

                    // 2. Toggle Status (Aktif / Suspend)
                    Tables\Actions\Action::make('toggle_status')
                        ->label(fn (User $record) => $record->status === 'active' ? 'Tangguhkan (Suspend)' : 'Aktifkan Akun')
                        ->icon(fn (User $record) => $record->status === 'active' ? 'heroicon-m-no-symbol' : 'heroicon-m-check-circle')
                        ->color(fn (User $record) => $record->status === 'active' ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->action(function (User $record) {
                            $newStatus = $record->status === 'active' ? 'suspended' : 'active';
                            $record->update(['status' => $newStatus]);

                            Notification::make()
                                ->title("Status akun {$record->name} diubah menjadi {$newStatus}")
                                ->success()
                                ->send();
                        }),

                    // 3. Reset Password (untuk Guru / Admin)
                    Tables\Actions\Action::make('reset_password')
                        ->label('Reset Kata Sandi')
                        ->icon('heroicon-m-key')
                        ->color('info')
                        ->visible(fn (User $record) => $record->role !== 'siswa')
                        ->form([
                            Forms\Components\TextInput::make('new_password')
                                ->label('Kata Sandi Baru')
                                ->password()
                                ->required()
                                ->minLength(6),
                        ])
                        ->action(function (User $record, array $data) {
                            $record->update([
                                'password' => Hash::make($data['new_password']),
                            ]);

                            Notification::make()
                                ->title("Kata sandi {$record->name} berhasil diubah.")
                                ->success()
                                ->send();
                        }),

                    // 4. Reset PIN Siswa (hanya siswa)
                    Tables\Actions\Action::make('reset_pin')
                        ->label('Reset PIN Siswa')
                        ->icon('heroicon-m-lock-closed')
                        ->color('info')
                        ->visible(fn (User $record) => $record->role === 'siswa')
                        ->form([
                            Forms\Components\TextInput::make('pin')
                                ->label('PIN Baru (4 Digit Angka)')
                                ->numeric()
                                ->length(4)
                                ->required(),
                        ])
                        ->action(function (User $record, array $data) {
                            $record->update([
                                'pin_hash' => Hash::make($data['pin']),
                            ]);

                            Notification::make()
                                ->title("PIN 4 digit untuk siswa {$record->name} berhasil diperbarui.")
                                ->success()
                                ->send();
                        }),

                    // 5. Reset 2FA
                    Tables\Actions\Action::make('reset_2fa')
                        ->label('Reset 2FA TOTP')
                        ->icon('heroicon-m-shield-exclamation')
                        ->color('danger')
                        ->visible(fn (User $record) => filled($record->two_factor_secret) || filled($record->two_factor_confirmed_at))
                        ->requiresConfirmation()
                        ->action(function (User $record) {
                            $record->update([
                                'two_factor_secret' => null,
                                'two_factor_recovery_codes' => null,
                                'two_factor_confirmed_at' => null,
                            ]);

                            Notification::make()
                                ->title("2FA untuk {$record->name} telah direset.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
