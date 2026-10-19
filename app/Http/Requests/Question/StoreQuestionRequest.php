<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['guru', 'admin']);
    }

    public function rules(): array
    {
        return [
            'topic_id' => ['required', 'exists:topics,id'],
            'tipe' => ['required', 'in:pilihan_ganda,isian_singkat,benar_salah'],
            'teks' => ['required', 'string', 'min:3'],
            'opsi' => ['nullable', 'array'],
            'jawaban' => ['required', 'string'],
            'penjelasan' => ['nullable', 'string'],
            'kesulitan' => ['required', 'in:mudah,sedang,sulit'],
        ];
    }

    public function messages(): array
    {
        return [
            'topic_id.required' => 'Topik materi wajib dipilih.',
            'topic_id.exists' => 'Topik yang dipilih tidak valid.',
            'tipe.required' => 'Tipe soal wajib ditentukan.',
            'tipe.in' => 'Tipe soal harus pilihan ganda, isian singkat, atau benar/salah.',
            'teks.required' => 'Teks pertanyaan tidak boleh kosong.',
            'teks.min' => 'Teks pertanyaan minimal 3 karakter.',
            'jawaban.required' => 'Kunci jawaban wajib diisi.',
            'kesulitan.required' => 'Tingkat kesulitan wajib dipilih.',
        ];
    }
}
