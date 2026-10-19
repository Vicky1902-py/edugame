<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WordBlacklistResource\Pages;
use App\Models\WordBlacklist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WordBlacklistResource extends Resource
{
    protected static ?string $model = WordBlacklist::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationGroup = 'Sistem & Keamanan';

    protected static ?string $navigationLabel = 'Kata Terlarang (Blacklist Nickname)';

    protected static ?string $modelLabel = 'Kata Terlarang';

    protected static ?string $pluralModelLabel = 'Daftar Kata Terlarang';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Entri Moderasi Kata')
                    ->schema([
                        Forms\Components\TextInput::make('word')
                            ->label('Kata Terlarang (Sensitif)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('severity')
                            ->label('Tindakan / Tingkat Keparahan')
                            ->options([
                                'block' => 'Blokir Total (Tolak Pendaftaran Nickname)',
                                'warning' => 'Peringatan Saja',
                            ])
                            ->default('block')
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),

                Tables\Columns\TextColumn::make('word')
                    ->label('Kata')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('severity')
                    ->label('Tindakan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'block' => 'danger',
                        'warning' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->dateTime('d M Y'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('severity')
                    ->options([
                        'block' => 'Block',
                        'warning' => 'Warning',
                    ]),
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
            'index' => Pages\ListWordBlacklists::route('/'),
            'create' => Pages\CreateWordBlacklist::route('/create'),
            'edit' => Pages\EditWordBlacklist::route('/{record}/edit'),
        ];
    }
}
