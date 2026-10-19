<?php

namespace App\Http\Requests\Classroom;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['guru', 'admin']);
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'min:2', 'max:100'],
            'jenjang' => ['required', 'string', 'in:SD,SMP,SMA,SMK'],
            'leaderboard_visible' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama kelas wajib diisi (contoh: Kelas 5-A).',
            'nama.min' => 'Nama kelas minimal 2 karakter.',
            'jenjang.required' => 'Jenjang pendidikan wajib dipilih.',
            'jenjang.in' => 'Jenjang harus salah satu dari: SD, SMP, SMA, SMK.',
        ];
    }
}
