<?php

namespace App\Http\Requests\Classroom;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['guru', 'admin']);
    }

    public function rules(): array
    {
        return [
            'nama' => ['sometimes', 'required', 'string', 'min:2', 'max:100'],
            'jenjang' => ['sometimes', 'required', 'string', 'in:SD,SMP,SMA,SMK'],
            'leaderboard_visible' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama kelas wajib diisi.',
            'jenjang.in' => 'Jenjang harus salah satu dari: SD, SMP, SMA, SMK.',
        ];
    }
}
