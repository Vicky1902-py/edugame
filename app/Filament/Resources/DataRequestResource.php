<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataRequestResource\Pages;
use App\Models\DataRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DataRequestResource extends Resource
{
    protected static ?string $model = DataRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?string $navigationGroup = 'Sistem & Keamanan';

    protected static ?string $navigationLabel = 'Kepatuhan Privasi (UU PDP)';

    protected static ?string $modelLabel = 'Permintaan Data';

    protected static ?string $pluralModelLabel = 'Antrean Permintaan Data Pribadi';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Rincian Permintaan Perlindungan Data Pribadi')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('Siswa Subjek Data')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('requested_by')
                            ->label('Pihak Pemohon (Guru / Orang Tua)')
                            ->relationship('requester', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('request_type')
                            ->label('Jenis Permintaan')
                            ->options([
                                'export' => 'Ekspor Seluruh Data Siswa (Portabilitas Data)',
                                'delete' => 'Penghapusan Data Permanen (Right to be Forgotten)',
                            ])
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status Pemrosesan')
                            ->options([
                                'pending' => 'Menunggu (Pending)',
                                'processing' => 'Sedang Diproses (Processing)',
                                'completed' => 'Selesai (Completed)',
                                'rejected' => 'Ditolak (Rejected)',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\DateTimePicker::make('deadline_at')
                            ->label('Batas Tenggat Waktu (SLA PDP)')
                            ->default(now()->addDays(3))
                            ->required(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Hukum / Keterangan Pemrosesan')
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

                Tables\Columns\TextColumn::make('student.name')
                    ->label('Siswa Terkait')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Pemohon')
                    ->searchable(),

                Tables\Columns\TextColumn::make('request_type')
                    ->label('Jenis Permintaan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'export' => 'info',
                        'delete' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => $state === 'export' ? 'Ekspor Data' : 'Hapus Data Siswa'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'processing' => 'warning',
                        'pending' => 'gray',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('deadline_at')
                    ->label('Tenggat SLA')
                    ->dateTime('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('request_type')
                    ->options([
                        'export' => 'Ekspor Data',
                        'delete' => 'Penghapusan Data',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    // Tandai Selesai
                    Tables\Actions\Action::make('mark_completed')
                        ->label('Tandai Selesai Diproses')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (DataRequest $record) {
                            $record->update([
                                'status' => 'completed',
                                'completed_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Permintaan PDP berhasil ditandai selesai.')
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
            'index' => Pages\ListDataRequests::route('/'),
            'create' => Pages\CreateDataRequest::route('/create'),
            'edit' => Pages\EditDataRequest::route('/{record}/edit'),
        ];
    }
}
