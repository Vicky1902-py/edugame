<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'CMS Website';

    protected static ?string $navigationLabel = 'Pengumuman & Siaran In-App';

    protected static ?string $modelLabel = 'Pengumuman';

    protected static ?string $pluralModelLabel = 'Daftar Pengumuman Siaran';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Muatan Pengumuman')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Pengumuman')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->label('Jenis / Tingkat Urgensi')
                            ->options([
                                'info' => 'Informasi Umum (Biru)',
                                'warning' => 'Peringatan / Maintenance (Kuning)',
                                'danger' => 'Penting / Darurat (Merah)',
                            ])
                            ->default('info')
                            ->required(),

                        Forms\Components\Select::make('target_role')
                            ->label('Sasaran Pengguna')
                            ->options([
                                'all' => 'Semua Pengguna (Guru & Siswa)',
                                'guru' => 'Hanya Guru',
                                'siswa' => 'Hanya Siswa',
                            ])
                            ->default('all')
                            ->required(),

                        Forms\Components\Select::make('school_id')
                            ->label('Target Sekolah Tertentu (Kosongkan jika untuk semua)')
                            ->relationship('school', 'nama')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\Textarea::make('message')
                            ->label('Isi Pesan Pengumuman')
                            ->rows(3)
                            ->columnSpanFull()
                            ->required(),

                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Mulai Ditampilkan')
                            ->default(now()),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Berakhir Pada')
                            ->nullable(),

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
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'danger' => 'danger',
                        'warning' => 'warning',
                        default => 'info',
                    }),

                Tables\Columns\TextColumn::make('target_role')
                    ->label('Target')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('school.nama')
                    ->label('Sekolah')
                    ->placeholder('Semua Sekolah'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Berakhir')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Tanpa Batas'),
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
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
