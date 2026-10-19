<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;

class ImportQuestionCsvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['guru', 'admin']);
    }

    public function rules(): array
    {
        return [
            'file_csv' => ['required', 'file', 'mimes:csv,txt', 'max:5120'], // Max 5MB
            'default_topic_id' => ['nullable', 'exists:topics,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'file_csv.required' => 'Silakan pilih berkas CSV yang akan diunggah.',
            'file_csv.file' => 'Berkas yang diunggah tidak valid.',
            'file_csv.mimes' => 'Format berkas harus berekstensi .csv atau berkas teks terstruktur.',
            'file_csv.max' => 'Ukuran berkas CSV maksimal 5 MB.',
            'default_topic_id.exists' => 'Topik default yang dipilih tidak valid.',
        ];
    }
}
