<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BadgeResource\Pages;
use App\Models\Badge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BadgeResource extends Resource
{
    protected static ?string $model = Badge::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Game & Gamifikasi';

    protected static ?string $navigationLabel = 'Lencana & Penghargaan';

    protected static ?string $modelLabel = 'Lencana';

    protected static ?string $pluralModelLabel = 'Daftar Lencana';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Lencana (Badge)')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Lencana')
                            ->placeholder('Contoh: Penjelajah Angka, Juara Bertahan')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('kode')
                            ->label('Kode Unik')
                            ->placeholder('math_explorer, speed_demon')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('ikon_url')
                            ->label('URL / Ikon SVG')
                            ->placeholder('/images/badges/gold_trophy.svg')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi Prestasi')
                            ->placeholder('Diberikan kepada siswa yang berhasil...')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\KeyValue::make('syarat_json')
                            ->label('Kriteria Syarat Perolehan (Rules Engine)')
                            ->keyLabel('Parameter (min_xp, min_streak, games_played)')
                            ->valueLabel('Target Nilai')
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Lencana')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(50),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Diraih Oleh')
                    ->counts('users')
                    ->badge()
                    ->color('success'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListBadges::route('/'),
            'create' => Pages\CreateBadge::route('/create'),
            'edit' => Pages\EditBadge::route('/{record}/edit'),
        ];
    }
}
