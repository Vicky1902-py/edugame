<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['guru', 'admin']);
    }

    public function rules(): array
    {
        return [
            'topic_id' => ['sometimes', 'required', 'exists:topics,id'],
            'tipe' => ['sometimes', 'required', 'in:pilihan_ganda,isian_singkat,benar_salah'],
            'teks' => ['sometimes', 'required', 'string', 'min:3'],
            'opsi' => ['nullable', 'array'],
            'jawaban' => ['sometimes', 'required', 'string'],
            'penjelasan' => ['nullable', 'string'],
            'kesulitan' => ['sometimes', 'required', 'in:mudah,sedang,sulit'],
        ];
    }
}
