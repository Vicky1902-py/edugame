<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if (!empty($data['pin'])) {
            $data['pin_hash'] = Hash::make($data['pin']);
            unset($data['pin']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $user = $this->record;

        if ($user->role === 'admin' && !$user->hasRole('superadmin')) {
            $user->syncRoles(['superadmin']);
        } elseif ($user->role === 'guru' && !$user->hasRole('guru')) {
            $user->syncRoles(['guru']);
        } elseif ($user->role === 'siswa' && !$user->hasRole('siswa')) {
            $user->syncRoles(['siswa']);
        }
    }
}
