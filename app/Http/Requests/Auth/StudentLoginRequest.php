<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StudentLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_kelas' => ['required', 'string', 'min:4', 'max:10'],
            'nama_panggilan' => ['required', 'string', 'min:2', 'max:30'],
            'pin' => ['required', 'string', 'regex:/^\d{4}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode_kelas.required' => 'Kode kelas wajib diisi.',
            'nama_panggilan.required' => 'Nama panggilan wajib diisi.',
            'nama_panggilan.min' => 'Nama panggilan minimal 2 huruf.',
            'nama_panggilan.max' => 'Nama panggilan maksimal 30 huruf.',
            'pin.required' => 'PIN 4 angka wajib diisi.',
            'pin.regex' => 'PIN harus terdiri dari tepat 4 digit angka (contoh: 1234).',
        ];
    }
}
