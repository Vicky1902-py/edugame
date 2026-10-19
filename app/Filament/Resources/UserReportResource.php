<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserReportResource\Pages;
use App\Models\User;
use App\Models\UserReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserReportResource extends Resource
{
    protected static ?string $model = UserReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Sistem & Keamanan';

    protected static ?string $navigationLabel = 'Laporan Pelanggaran (Moderasi)';

    protected static ?string $modelLabel = 'Laporan Pengguna';

    protected static ?string $pluralModelLabel = 'Antrean Laporan Pelanggaran';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Rincian Laporan')
                    ->schema([
                        Forms\Components\Select::make('reporter_id')
                            ->label('Pelapor')
                            ->relationship('reporter', 'name')
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('reported_user_id')
                            ->label('Pengguna Terlapor')
                            ->relationship('reportedUser', 'name')
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('report_type')
                            ->label('Jenis Pelanggaran')
                            ->options([
                                'cheating' => 'Kecurangan Skor / Waktu (Cheating)',
                                'bad_nickname' => 'Nama Panggilan Tidak Pantas (Bad Nickname)',
                                'inappropriate_content' => 'Konten / Komunikasi Tidak Sopan',
                            ])
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status Tindak Lanjut')
                            ->options([
                                'open' => 'Baru (Open)',
                                'investigating' => 'Dalam Investigasi',
                                'resolved' => 'Selesai / Ditindaklanjuti (Resolved)',
                                'dismissed' => 'Ditolak / Diabaikan (Dismissed)',
                            ])
                            ->default('open')
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Kronologi / Laporan')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Catatan Moderator / Putusan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Masuk')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reporter.name')
                    ->label('Pelapor')
                    ->searchable(),

                Tables\Columns\TextColumn::make('reportedUser.name')
                    ->label('Terlapor')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('report_type')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cheating' => 'danger',
                        'bad_nickname' => 'warning',
                        default => 'info',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'danger',
                        'investigating' => 'warning',
                        'resolved' => 'success',
                        'dismissed' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'investigating' => 'Investigating',
                        'resolved' => 'Resolved',
                        'dismissed' => 'Dismissed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    // Action Tindak Lanjut: Selesaikan Laporan + Opsi Suspend Terlapor
                    Tables\Actions\Action::make('resolve')
                        ->label('Selesaikan & Jatuhkan Tindakan')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->form([
                            Forms\Components\Textarea::make('admin_notes')
                                ->label('Catatan Tindakan')
                                ->required(),

                            Forms\Components\Toggle::make('suspend_user')
                                ->label('Tangguhkan (Suspend) Akun Terlapor Sekaligus?')
                                ->default(false),
                        ])
                        ->action(function (UserReport $record, array $data) {
                            $record->update([
                                'status' => 'resolved',
                                'admin_notes' => $data['admin_notes'],
                                'resolved_at' => now(),
                            ]);

                            if (!empty($data['suspend_user']) && $record->reportedUser) {
                                $record->reportedUser->update(['status' => 'suspended']);
                            }

                            Notification::make()
                                ->title('Laporan berhasil diselesaikan.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserReports::route('/'),
            'create' => Pages\CreateUserReport::route('/create'),
            'edit' => Pages\EditUserReport::route('/{record}/edit'),
        ];
    }
}
