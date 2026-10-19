<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'CMS Website';

    protected static ?string $navigationLabel = 'Banner & Hero Promosi';

    protected static ?string $modelLabel = 'Banner Promosi';

    protected static ?string $pluralModelLabel = 'Daftar Banner';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Muatan Banner')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Banner')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('subtitle')
                            ->label('Sub-judul / Keterangan')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('image_url')
                            ->label('URL Gambar Banner')
                            ->placeholder('/images/banners/hero-promo.jpg')
                            ->required(),

                        Forms\Components\TextInput::make('cta_label')
                            ->label('Label Tombol (CTA)')
                            ->placeholder('Mulai Sekarang / Pelajari Selengkapnya'),

                        Forms\Components\TextInput::make('cta_url')
                            ->label('Link Tujuan Tombol')
                            ->placeholder('/daftar-guru atau https://...'),

                        Forms\Components\Select::make('target_role')
                            ->label('Target Audiens')
                            ->options([
                                'all' => 'Semua Pengguna & Pengunjung Publik',
                                'siswa' => 'Khusus Siswa',
                                'guru' => 'Khusus Guru',
                            ])
                            ->default('all')
                            ->required(),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Banner')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('target_role')
                    ->label('Target')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('cta_label')
                    ->label('Tombol CTA')
                    ->placeholder('-'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
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
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
