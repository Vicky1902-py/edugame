<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Sistem & Keamanan';

    protected static ?string $navigationLabel = 'Log Audit Aktivitas (Read-Only)';

    protected static ?string $modelLabel = 'Log Aktivitas';

    protected static ?string $pluralModelLabel = 'Log Audit Sistem';

    protected static ?int $navigationSort = 2;

    // Kebijakan Keamanan Ketat: Log bersifat READ-ONLY permanen tanpa opsi edit atau delete
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
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
                    ->label('Waktu Kejadian')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Pelaku (Operator)')
                    ->searchable()
                    ->placeholder('Sistem / Anonymous'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Aksi / Keterangan')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'info',
                    }),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Tipe Objek')
                    ->formatStateUsing(fn ($state) => class_basename($state)),

                Tables\Columns\TextColumn::make('subject_id')
                    ->label('ID Objek'),

                Tables\Columns\TextColumn::make('properties')
                    ->label('Data Payload (JSON)')
                    ->limit(40)
                    ->fontFamily('mono'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->options([
                        'created' => 'Created (Dibuat)',
                        'updated' => 'Updated (Diubah)',
                        'deleted' => 'Deleted (Dihapus)',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->infolist([
                        Infolists\Components\Section::make('Rincian Log Audit')
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')->label('Waktu')->dateTime('d M Y H:i:s'),
                                Infolists\Components\TextEntry::make('causer.name')->label('Pelaku')->placeholder('Sistem'),
                                Infolists\Components\TextEntry::make('description')->label('Deskripsi'),
                                Infolists\Components\TextEntry::make('subject_type')->label('Target Entitas'),
                                Infolists\Components\TextEntry::make('subject_id')->label('ID Target'),
                                Infolists\Components\KeyValueEntry::make('properties.attributes')->label('Data Nilai Baru (Attributes)')->columnSpanFull(),
                                Infolists\Components\KeyValueEntry::make('properties.old')->label('Data Nilai Lama (Old)')->columnSpanFull(),
                            ])->columns(2),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
