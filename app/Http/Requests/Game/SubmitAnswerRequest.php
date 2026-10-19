<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'session_id' => ['required', 'integer', 'exists:game_sessions,id'],
            'question_id' => ['required', 'integer', 'exists:questions,id'],
            'jawaban_siswa' => ['required', 'string', 'max:255'],
            'waktu_jawab_ms' => ['required', 'integer', 'min:0', 'max:60000'],
        ];
    }

    /**
     * Custom message for validation.
     */
    public function messages(): array
    {
        return [
            'session_id.required' => 'ID sesi permainan wajib disertakan.',
            'session_id.exists' => 'Sesi permainan tidak ditemukan.',
            'question_id.required' => 'ID soal wajib disertakan.',
            'question_id.exists' => 'Soal tidak ditemukan di bank soal.',
            'jawaban_siswa.required' => 'Jawaban siswa tidak boleh kosong.',
            'waktu_jawab_ms.required' => 'Waktu pengerjaan harus dicatat.',
        ];
    }
}
