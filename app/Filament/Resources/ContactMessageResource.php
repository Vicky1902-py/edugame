<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationGroup = 'CMS Website';

    protected static ?string $navigationLabel = 'Kotak Masuk Kontak';

    protected static ?string $modelLabel = 'Pesan Masuk';

    protected static ?string $pluralModelLabel = 'Kotak Masuk Formulir Kontak';

    protected static ?int $navigationSort = 7;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tindak Lanjut Pesan')
                    ->schema([
                        Forms\Components\TextInput::make('sender_name')->label('Nama Pengirim')->disabled(),
                        Forms\Components\TextInput::make('sender_email')->label('Email Pengirim')->disabled(),
                        Forms\Components\TextInput::make('phone')->label('Nomor WhatsApp / Telp')->disabled(),
                        Forms\Components\TextInput::make('subject')->label('Subjek')->disabled(),
                        Forms\Components\Textarea::make('message')->label('Isi Pesan')->disabled()->columnSpanFull(),
                        Forms\Components\Toggle::make('is_read')->label('Tandai Sudah Dibaca'),
                        Forms\Components\Textarea::make('admin_reply_notes')
                            ->label('Catatan Balasan Admin')
                            ->placeholder('Catatan tindak lanjut yang telah dilakukan...')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sender_name')
                    ->label('Pengirim')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('sender_email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Subjek')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\IconColumn::make('is_read')
                    ->label('Dibaca')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_read')
                    ->label('Status Baca'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Tinjau & Catat Balasan'),
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
            'index' => Pages\ListContactMessages::route('/'),
            'edit' => Pages\EditContactMessage::route('/{record}/edit'),
        ];
    }
}
