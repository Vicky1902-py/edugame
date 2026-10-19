<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        if (!empty($data['pin'])) {
            $data['pin_hash'] = Hash::make($data['pin']);
            unset($data['pin']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        if ($user->role === 'admin') {
            $user->assignRole('superadmin');
        } elseif ($user->role === 'guru') {
            $user->assignRole('guru');
        } elseif ($user->role === 'siswa') {
            $user->assignRole('siswa');
        }
    }
}
