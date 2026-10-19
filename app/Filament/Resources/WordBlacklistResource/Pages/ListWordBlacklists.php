<?php

namespace App\Filament\Resources\WordBlacklistResource\Pages;

use App\Filament\Resources\WordBlacklistResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWordBlacklists extends ListRecords
{
    protected static string $resource = WordBlacklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Kata Terlarang'),
        ];
    }
}
