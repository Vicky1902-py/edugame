<?php

namespace App\Filament\Resources;

use App\Filament\Resources\XpLedgerResource\Pages;
use App\Models\User;
use App\Models\XpLedger;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class XpLedgerResource extends Resource
{
    protected static ?string $model = XpLedger::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Game & Gamifikasi';

    protected static ?string $navigationLabel = 'Buku Besar XP & Leaderboard';

    protected static ?string $modelLabel = 'Transaksi XP';

    protected static ?string $pluralModelLabel = 'Buku Besar XP (XP Ledger)';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Transaksi')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.school.nama')
                    ->label('Sekolah')
                    ->searchable()
                    ->placeholder('Tanpa Sekolah'),

                Tables\Columns\TextColumn::make('jumlah_xp')
                    ->label('Perubahan XP')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn (int $state): string => ($state >= 0 ? '+' : '') . number_format($state) . ' XP'),

                Tables\Columns\TextColumn::make('balance_after')
                    ->label('Saldo XP Setelahnya')
                    ->formatStateUsing(fn ($state) => number_format((int) $state) . ' XP'),

                Tables\Columns\TextColumn::make('sumber')
                    ->label('Sumber Aktivitas')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Keterangan / Alasan')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Dipicu Oleh')
                    ->placeholder('Sistem Otomatis'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('sumber')
                    ->label('Sumber')
                    ->options([
                        'game_session' => 'Sesi Permainan',
                        'manual_adjustment' => 'Koreksi Superadmin',
                        'leaderboard_reset' => 'Reset Peringkat',
                    ]),
            ])
            ->headerActions([
                // 1. Aksi Koreksi XP Siswa secara Manual
                Tables\Actions\Action::make('adjust_xp')
                    ->label('Koreksi XP Siswa')
                    ->icon('heroicon-m-plus-circle')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('user_id')
                            ->label('Pilih Siswa')
                            ->options(fn () => User::where('role', 'siswa')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah Koreksi XP (Positif menambah, Negatif mengurangi)')
                            ->numeric()
                            ->required()
                            ->helperText('Contoh: 150 atau -50'),

                        Forms\Components\TextInput::make('reason')
                            ->label('Alasan Koreksi (Wajib untuk Audit Trail)')
                            ->placeholder('Contoh: Hadiah kompetisi kelas atau kompensasi error server')
                            ->required(),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Konfirmasi Kata Sandi Superadmin Anda')
                            ->password()
                            ->required()
                            ->rule(function () {
                                return function (string $attribute, $value, \Closure $fail) {
                                    if (!Hash::check($value, auth()->user()->password)) {
                                        $fail('Kata sandi konfirmasi tidak sesuai.');
                                    }
                                };
                            }),
                    ])
                    ->action(function (array $data) {
                        $user = User::findOrFail($data['user_id']);
                        $currentBalance = (int) XpLedger::where('user_id', $user->id)->sum('jumlah_xp');
                        $newBalance = $currentBalance + (int) $data['amount'];

                        XpLedger::create([
                            'user_id' => $user->id,
                            'causer_id' => auth()->id(),
                            'jumlah_xp' => (int) $data['amount'],
                            'balance_after' => $newBalance,
                            'sumber' => 'manual_adjustment',
                            'reason' => $data['reason'],
                        ]);

                        Notification::make()
                            ->title("Koreksi XP untuk {$user->name} berhasil dicatat.")
                            ->success()
                            ->send();
                    }),

                // 2. Aksi Reset Leaderboard Global (Aksi Destruktif Memerlukan Password)
                Tables\Actions\Action::make('reset_leaderboard')
                    ->label('Reset Papan Peringkat (Leaderboard)')
                    ->icon('heroicon-m-arrow-path')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Reset Leaderboard')
                    ->modalDescription('Tindakan ini akan mengatur ulang akumulasi XP seluruh siswa di leaderboard periode ini. Tindakan ini memerlukan verifikasi password superadmin.')
                    ->form([
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Masukkan Kata Sandi Superadmin untuk Konfirmasi')
                            ->password()
                            ->required()
                            ->rule(function () {
                                return function (string $attribute, $value, \Closure $fail) {
                                    if (!Hash::check($value, auth()->user()->password)) {
                                        $fail('Kata sandi konfirmasi tidak sesuai.');
                                    }
                                };
                            }),
                    ])
                    ->action(function () {
                        // Catat log audit reset leaderboard
                        activity()
                            ->causedBy(auth()->user())
                            ->log('Reset papan peringkat (leaderboard) platform dieksekusi.');

                        Notification::make()
                            ->title('Leaderboard berhasil direset untuk periode baru.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListXpLedgers::route('/'),
        ];
    }
}
