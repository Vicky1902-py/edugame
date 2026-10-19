<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Question;
use App\Models\Topic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Bank Soal & Kurikulum';

    protected static ?string $navigationLabel = 'Bank Soal Global & Moderasi';

    protected static ?string $modelLabel = 'Butir Soal';

    protected static ?string $pluralModelLabel = 'Bank Soal Global';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi & Muatan Soal')
                    ->schema([
                        Forms\Components\Select::make('topic_id')
                            ->label('Topik / Capaian Pembelajaran')
                            ->relationship('topic', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('tipe')
                            ->label('Tipe Soal')
                            ->options([
                                'pilihan_ganda' => 'Pilihan Ganda',
                                'isian_singkat' => 'Isian Singkat',
                                'benar_salah' => 'Benar / Salah',
                            ])
                            ->default('pilihan_ganda')
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('kesulitan')
                            ->label('Tingkat Kesulitan')
                            ->options([
                                'mudah' => 'Mudah',
                                'sedang' => 'Sedang',
                                'sulit' => 'Sulit (HOTS)',
                            ])
                            ->default('sedang')
                            ->required(),

                        Forms\Components\Select::make('status_moderasi')
                            ->label('Status Moderasi')
                            ->options([
                                'approved' => 'Disetujui (Bank Soal Resmi)',
                                'pending' => 'Menunggu Moderasi',
                                'rejected' => 'Ditolak',
                            ])
                            ->default('approved')
                            ->required(),

                        Forms\Components\Textarea::make('teks')
                            ->label('Pertanyaan / Stimulus')
                            ->placeholder('Tuliskan teks pertanyaan...')
                            ->rows(3)
                            ->columnSpanFull()
                            ->required(),

                        Forms\Components\KeyValue::make('opsi_json')
                            ->label('Pilihan Jawaban (Khusus Pilihan Ganda)')
                            ->keyLabel('Kunci (A, B, C, D)')
                            ->valueLabel('Teks Opsi')
                            ->visible(fn (Forms\Get $get) => $get('tipe') === 'pilihan_ganda')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('jawaban')
                            ->label('Kunci Jawaban Benar')
                            ->placeholder('Contoh: A (untuk PG) atau 42 (untuk isian)')
                            ->required(),

                        Forms\Components\Textarea::make('penjelasan')
                            ->label('Penjelasan / Pembahasan Soal')
                            ->placeholder('Pembahasan langkah demi langkah untuk siswa...')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('moderation_notes')
                            ->label('Catatan Moderasi (Jika Ditolak / Revisi)')
                            ->rows(2)
                            ->columnSpanFull()
                            ->visible(fn (Forms\Get $get) => $get('status_moderasi') === 'rejected'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),

                Tables\Columns\TextColumn::make('teks')
                    ->label('Pertanyaan')
                    ->limit(60)
                    ->searchable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('topic.nama')
                    ->label('Topik')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pilihan_ganda' => 'primary',
                        'isian_singkat' => 'info',
                        'benar_salah' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('kesulitan')
                    ->label('Kesulitan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'mudah' => 'success',
                        'sedang' => 'warning',
                        'sulit' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status_moderasi')
                    ->label('Moderasi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('author.name')
                    ->label('Author')
                    ->placeholder('Platform Resmi'),

                Tables\Columns\TextColumn::make('versi')
                    ->label('v')
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_moderasi')
                    ->label('Status Moderasi')
                    ->options([
                        'approved' => 'Disetujui',
                        'pending' => 'Menunggu Review',
                        'rejected' => 'Ditolak',
                    ]),

                Tables\Filters\SelectFilter::make('kesulitan')
                    ->label('Kesulitan')
                    ->options([
                        'mudah' => 'Mudah',
                        'sedang' => 'Sedang',
                        'sulit' => 'Sulit',
                    ]),

                Tables\Filters\SelectFilter::make('topic_id')
                    ->label('Topik')
                    ->relationship('topic', 'nama')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    // Action Moderasi: Setujui Soal
                    Tables\Actions\Action::make('approve')
                        ->label('Setujui Soal (Approve)')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->visible(fn (Question $record) => $record->status_moderasi !== 'approved')
                        ->action(function (Question $record) {
                            $record->update([
                                'status_moderasi' => 'approved',
                                'moderation_notes' => null,
                            ]);

                            Notification::make()
                                ->title('Soal berhasil disetujui menjadi bank soal resmi.')
                                ->success()
                                ->send();
                        }),

                    // Action Moderasi: Tolak Soal
                    Tables\Actions\Action::make('reject')
                        ->label('Tolak Soal (Reject)')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->visible(fn (Question $record) => $record->status_moderasi !== 'rejected')
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->label('Alasan Penolakan')
                                ->required(),
                        ])
                        ->action(function (Question $record, array $data) {
                            $record->update([
                                'status_moderasi' => 'rejected',
                                'moderation_notes' => $data['reason'],
                            ]);

                            Notification::make()
                                ->title('Soal ditolak dengan catatan evaluasi.')
                                ->danger()
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
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
