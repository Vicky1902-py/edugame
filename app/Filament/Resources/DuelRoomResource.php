<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DuelRoomResource\Pages;
use App\Models\DuelRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DuelRoomResource extends Resource
{
    protected static ?string $model = DuelRoom::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Game & Gamifikasi';

    protected static ?string $navigationLabel = 'Pemantau Kuis Duel (Live)';

    protected static ?string $modelLabel = 'Ruang Duel';

    protected static ?string $pluralModelLabel = 'Daftar Ruang Duel';

    protected static ?int $navigationSort = 4;

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

                Tables\Columns\TextColumn::make('code')
                    ->label('Kode Room')
                    ->badge()
                    ->color('primary')
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('class.nama')
                    ->label('Kelas')
                    ->placeholder('Lintas Kelas'),

                Tables\Columns\TextColumn::make('createdByStudent.name')
                    ->label('Inisiator / Host')
                    ->searchable(),

                Tables\Columns\TextColumn::make('players_count')
                    ->label('Pemain Aktif')
                    ->counts('players')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status Duel')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'waiting' => 'warning',
                        'active' => 'success',
                        'finished' => 'info',
                        'expired' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_teacher_session')
                    ->label('Sesi Guru')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'waiting' => 'Menunggu Lawan (Waiting)',
                        'active' => 'Sedang Berlangsung (Active)',
                        'finished' => 'Selesai (Finished)',
                        'expired' => 'Kedaluwarsa (Expired)',
                    ]),
            ])
            ->actions([
                // Tutup Paksa Ruang Duel yang Macet / Ditinggalkan
                Tables\Actions\Action::make('force_close')
                    ->label('Tutup Paksa Ruang')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn (DuelRoom $record) => in_array($record->status, ['waiting', 'active']))
                    ->requiresConfirmation()
                    ->action(function (DuelRoom $record) {
                        $record->update(['status' => 'expired']);

                        Notification::make()
                            ->title("Ruang duel [{$record->code}] berhasil ditutup secara paksa.")
                            ->danger()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDuelRooms::route('/'),
        ];
    }
}
