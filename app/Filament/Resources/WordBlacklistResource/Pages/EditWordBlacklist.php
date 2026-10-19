<?php

namespace App\Filament\Resources\WordBlacklistResource\Pages;

use App\Filament\Resources\WordBlacklistResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWordBlacklist extends EditRecord
{
    protected static string $resource = WordBlacklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
