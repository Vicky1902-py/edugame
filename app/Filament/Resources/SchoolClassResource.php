<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolClassResource\Pages;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SchoolClassResource extends Resource
{
    protected static ?string $model = SchoolClass::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Manajemen Pengguna & Sekolah';

    protected static ?string $navigationLabel = 'Kelas & Rombel';

    protected static ?string $modelLabel = 'Kelas';

    protected static ?string $pluralModelLabel = 'Daftar Kelas';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kelas')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Kelas')
                            ->placeholder('Contoh: 4-A (Kelas Al-Farabi)')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('school_id')
                            ->label('Sekolah')
                            ->relationship('school', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('guru_id')
                            ->label('Guru Wali / Pengampu')
                            ->options(fn () => User::where('role', 'guru')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('jenjang')
                            ->label('Tingkat Kelas')
                            ->options([
                                '1' => 'Kelas 1 SD',
                                '2' => 'Kelas 2 SD',
                                '3' => 'Kelas 3 SD',
                                '4' => 'Kelas 4 SD',
                                '5' => 'Kelas 5 SD',
                                '6' => 'Kelas 6 SD',
                            ])
                            ->default('4')
                            ->required(),

                        Forms\Components\TextInput::make('kode_gabung')
                            ->label('Kode Gabung Kelas')
                            ->default(fn () => strtoupper(Str::random(6)))
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\Toggle::make('leaderboard_visible')
                            ->label('Tampilkan Papan Peringkat (Leaderboard) ke Siswa')
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
                    ->label('Nama Kelas')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('kode_gabung')
                    ->label('Kode Masuk')
                    ->badge()
                    ->color('primary')
                    ->copyable(),

                Tables\Columns\TextColumn::make('school.nama')
                    ->label('Sekolah')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('guru.name')
                    ->label('Guru Pengampu')
                    ->searchable(),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('Siswa Terdaftar')
                    ->counts('students')
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('leaderboard_visible')
                    ->label('Leaderboard')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('school_id')
                    ->label('Sekolah')
                    ->relationship('school', 'nama')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    // Action: Gabung / Pindah Siswa ke Kelas Lain
                    Tables\Actions\Action::make('transfer_students')
                        ->label('Pindahkan Semua Siswa ke Kelas Lain')
                        ->icon('heroicon-m-arrows-right-left')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Select::make('target_class_id')
                                ->label('Pilih Kelas Tujuan')
                                ->options(fn (SchoolClass $record) => SchoolClass::where('id', '!=', $record->id)->pluck('nama', 'id'))
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function (SchoolClass $record, array $data) {
                            $targetClassId = $data['target_class_id'];
                            $targetClass = SchoolClass::find($targetClassId);

                            $count = Enrollment::where('class_id', $record->id)->count();

                            // Update enrollment ke kelas tujuan jika siswa belum terdaftar
                            $enrollments = Enrollment::where('class_id', $record->id)->get();
                            foreach ($enrollments as $enrollment) {
                                $exists = Enrollment::where('class_id', $targetClassId)
                                    ->where('student_id', $enrollment->student_id)
                                    ->exists();

                                if (!$exists) {
                                    $enrollment->update(['class_id' => $targetClassId]);
                                } else {
                                    $enrollment->delete(); // hindari duplikasi
                                }
                            }

                            Notification::make()
                                ->title("Berhasil memindahkan {$count} siswa dari {$record->nama} ke {$targetClass->nama}")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteAction::make(),
                ]),
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
            'index' => Pages\ListSchoolClasses::route('/'),
            'create' => Pages\CreateSchoolClass::route('/create'),
            'edit' => Pages\EditSchoolClass::route('/{record}/edit'),
        ];
    }
}
