<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Bank Soal & Kurikulum';

    protected static ?string $navigationLabel = 'Mata Pelajaran';

    protected static ?string $modelLabel = 'Mata Pelajaran';

    protected static ?string $pluralModelLabel = 'Daftar Mata Pelajaran';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Mata Pelajaran')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Mata Pelajaran')
                            ->placeholder('Contoh: Matematika, IPAS, Bahasa Indonesia')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('jenjang')
                            ->label('Jenjang Pendidikan')
                            ->options([
                                'SD' => 'Sekolah Dasar (SD)',
                                'SMP' => 'Sekolah Menengah Pertama (SMP)',
                                'SMA' => 'Sekolah Menengah Atas (SMA)',
                                'SMK' => 'Sekolah Menengah Kejuruan (SMK)',
                            ])
                            ->default('SD')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Mapel')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('jenjang')
                    ->label('Jenjang')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('topics_count')
                    ->label('Total Topik / CP')
                    ->counts('topics'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
