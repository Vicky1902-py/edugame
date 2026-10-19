<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?string $navigationGroup = 'CMS Website';

    protected static ?string $navigationLabel = 'Testimoni Pendidik';

    protected static ?string $modelLabel = 'Testimoni';

    protected static ?string $pluralModelLabel = 'Daftar Testimoni Guru & Sekolah';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Testimoni')
                    ->schema([
                        Forms\Components\TextInput::make('author_name')
                            ->label('Nama Pemberi Testimoni')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('author_title')
                            ->label('Jabatan / Asal Sekolah')
                            ->placeholder('Guru Kelas 4 SDN 1 Merdeka')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('avatar_url')
                            ->label('URL Foto Profil')
                            ->placeholder('/images/avatars/guru1.jpg'),

                        Forms\Components\Select::make('rating')
                            ->label('Bintang Penilaian')
                            ->options([
                                5 => '⭐⭐⭐⭐⭐ (5 Bintang)',
                                4 => '⭐⭐⭐⭐ (4 Bintang)',
                                3 => '⭐⭐⭐ (3 Bintang)',
                            ])
                            ->default(5)
                            ->required(),

                        Forms\Components\Textarea::make('quote')
                            ->label('Kutipan Testimoni')
                            ->rows(3)
                            ->columnSpanFull()
                            ->required(),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Tampilkan di Halaman Depan (Featured)')
                            ->default(true),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),

                Tables\Columns\TextColumn::make('author_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('author_title')
                    ->label('Sekolah / Jabatan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn ($state) => str_repeat('⭐', (int) $state)),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
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
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
