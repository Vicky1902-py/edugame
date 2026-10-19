<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationGroup = 'CMS Website';

    protected static ?string $navigationLabel = 'Menu Navigasi';

    protected static ?string $modelLabel = 'Menu Navigasi';

    protected static ?string $pluralModelLabel = 'Daftar Menu Navigasi';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Menu')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Menu')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('location')
                            ->label('Lokasi Penempatan')
                            ->options([
                                'header' => 'Navigasi Atas (Header Navbar)',
                                'footer_1' => 'Footer Kolom 1 (Tautan Cepat)',
                                'footer_2' => 'Footer Kolom 2 (Bantuan & Legal)',
                            ])
                            ->required()
                            ->unique(ignoreRecord: true),
                    ])->columns(2),

                Forms\Components\Section::make('Daftar Tautan Menu')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Label Menu')
                                    ->required(),

                                Forms\Components\TextInput::make('url')
                                    ->label('Tautan URL')
                                    ->placeholder('/tentang-kami atau https://...')
                                    ->required(),

                                Forms\Components\Select::make('target')
                                    ->label('Target Jendela')
                                    ->options([
                                        '_self' => 'Buka di tab yang sama (_self)',
                                        '_blank' => 'Buka di tab baru (_blank)',
                                    ])
                                    ->default('_self'),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Urutan')
                                    ->numeric()
                                    ->default(0),
                            ])->columns(4)
                            ->orderColumn('sort_order')
                            ->defaultItems(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Menu')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Total Tautan')
                    ->counts('items')
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
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
