<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolResource\Pages;
use App\Models\School;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SchoolResource extends Resource
{
    protected static ?string $model = School::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Manajemen Pengguna & Sekolah';

    protected static ?string $navigationLabel = 'Sekolah';

    protected static ?string $modelLabel = 'Sekolah';

    protected static ?string $pluralModelLabel = 'Daftar Sekolah';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Lembaga Sekolah')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Sekolah')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('jenjang')
                            ->label('Jenjang Pendidikan')
                            ->options([
                                'SD' => 'Sekolah Dasar (SD / MI)',
                                'SMP' => 'Sekolah Menengah Pertama (SMP / MTs)',
                                'SMA' => 'Sekolah Menengah Atas (SMA / MA / SMK)',
                            ])
                            ->default('SD')
                            ->required(),

                        Forms\Components\Select::make('status_lisensi')
                            ->label('Status Lisensi / Kuota')
                            ->options([
                                'trial' => 'Trial (Uji Coba)',
                                'active' => 'Aktif (Berlangganan)',
                                'expired' => 'Kedaluwarsa (Expired)',
                            ])
                            ->default('trial')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Sekolah')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('jenjang')
                    ->label('Jenjang')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('status_lisensi')
                    ->label('Lisensi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'trial' => 'warning',
                        'expired' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('classes_count')
                    ->label('Total Kelas')
                    ->counts('classes'),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Total Pengguna')
                    ->counts('users'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar Sejak')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_lisensi')
                    ->label('Status Lisensi')
                    ->options([
                        'trial' => 'Trial',
                        'active' => 'Aktif',
                        'expired' => 'Kedaluwarsa',
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
            'index' => Pages\ListSchools::route('/'),
            'create' => Pages\CreateSchool::route('/create'),
            'edit' => Pages\EditSchool::route('/{record}/edit'),
        ];
    }
}
