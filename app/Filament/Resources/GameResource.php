<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameResource\Pages;
use App\Models\Game;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GameResource extends Resource
{
    protected static ?string $model = Game::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'Game & Gamifikasi';

    protected static ?string $navigationLabel = 'Manajer Game & Engine';

    protected static ?string $modelLabel = 'Game';

    protected static ?string $pluralModelLabel = 'Daftar Game Edukasi';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Game')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Game')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('kode_slug')
                            ->label('Kode Slug Game')
                            ->placeholder('balapan-perkalian, kuis-duel, puzzle-sains')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('jenjang')
                            ->label('Jenjang Target')
                            ->options([
                                'SD' => 'Sekolah Dasar (SD)',
                                'SMP' => 'Sekolah Menengah Pertama (SMP)',
                                'SMA' => 'Sekolah Menengah Atas (SMA)',
                            ])
                            ->default('SD')
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Parameter Mesin Permainan (Engine Rules)')
                    ->schema([
                        Forms\Components\Toggle::make('konfigurasi_json.is_active')
                            ->label('Status Game Aktif untuk Siswa')
                            ->default(true),

                        Forms\Components\TextInput::make('konfigurasi_json.nyawa')
                            ->label('Jumlah Nyawa / Kesempatan (Lives)')
                            ->numeric()
                            ->default(3)
                            ->required(),

                        Forms\Components\TextInput::make('konfigurasi_json.waktu_detik')
                            ->label('Durasi Timer per Pertanyaan (Detik)')
                            ->numeric()
                            ->default(15)
                            ->required(),

                        Forms\Components\TextInput::make('konfigurasi_json.poin_benar')
                            ->label('Skor Dasar Tiap Jawaban Benar')
                            ->numeric()
                            ->default(100)
                            ->required(),

                        Forms\Components\Toggle::make('konfigurasi_json.speed_bonus')
                            ->label('Aktifkan Speed Bonus (Bonus Menjawab Cepat)')
                            ->default(true),

                        Forms\Components\Toggle::make('konfigurasi_json.streak_bonus')
                            ->label('Aktifkan Streak Multiplier (Bonus Rentetan Benar)')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Game')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('kode_slug')
                    ->label('Slug')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('jenjang')
                    ->label('Jenjang')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('konfigurasi_json.nyawa')
                    ->label('Nyawa')
                    ->badge()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('konfigurasi_json.waktu_detik')
                    ->label('Timer (dtk)')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\IconColumn::make('konfigurasi_json.is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sessions_count')
                    ->label('Total Sesi Dimainkan')
                    ->counts('sessions')
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
            'index' => Pages\ListGames::route('/'),
            'create' => Pages\CreateGame::route('/create'),
            'edit' => Pages\EditGame::route('/{record}/edit'),
        ];
    }
}
