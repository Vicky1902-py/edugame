<?php

namespace App\Services\Question;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class QuestionService
{
    /**
     * Ambil daftar soal dengan filter pencarian dan paginasi.
     */
    public function listQuestions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Question::with(['topic.subject'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('teks', 'like', $search)
                  ->orWhere('penjelasan', 'like', $search);
            });
        }

        if (!empty($filters['subject_id'])) {
            $query->whereHas('topic', function ($q) use ($filters) {
                $q->where('subject_id', $filters['subject_id']);
            });
        }

        if (!empty($filters['topic_id'])) {
            $query->where('topic_id', $filters['topic_id']);
        }

        if (!empty($filters['tipe'])) {
            $query->where('tipe', $filters['tipe']);
        }

        if (!empty($filters['kesulitan'])) {
            $query->where('kesulitan', $filters['kesulitan']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Format opsi jawaban sesuai tipe soal.
     */
    public function formatOptions(string $type, ?array $options = null): ?array
    {
        if ($type === 'pilihan_ganda') {
            return [
                'A' => trim($options['A'] ?? $options['opsi_a'] ?? ''),
                'B' => trim($options['B'] ?? $options['opsi_b'] ?? ''),
                'C' => trim($options['C'] ?? $options['opsi_c'] ?? ''),
                'D' => trim($options['D'] ?? $options['opsi_d'] ?? ''),
            ];
        }

        if ($type === 'benar_salah') {
            return ['Benar', 'Salah'];
        }

        return null; // isian_singkat tidak memiliki opsi pilihan
    }

    /**
     * Buat soal baru.
     */
    public function createQuestion(array $data): Question
    {
        $tipe = $data['tipe'];
        $options = $this->formatOptions($tipe, $data['opsi'] ?? $data['opsi_json'] ?? null);

        return Question::create([
            'topic_id' => $data['topic_id'],
            'tipe' => $tipe,
            'teks' => trim($data['teks']),
            'opsi_json' => $options,
            'jawaban' => trim($data['jawaban']),
            'penjelasan' => trim($data['penjelasan'] ?? ''),
            'kesulitan' => $data['kesulitan'] ?? 'sedang',
            'versi' => 1,
        ]);
    }

    /**
     * Perbarui data soal.
     */
    public function updateQuestion(Question $question, array $data): Question
    {
        $tipe = $data['tipe'] ?? $question->tipe;
        $options = $this->formatOptions($tipe, $data['opsi'] ?? $data['opsi_json'] ?? $question->opsi_json);

        // Jika teks atau jawaban diubah, naikkan versi soal untuk integritas analitik
        $versionChanged = (isset($data['teks']) && $data['teks'] !== $question->teks) ||
                         (isset($data['jawaban']) && $data['jawaban'] !== $question->jawaban);

        $question->update([
            'topic_id' => $data['topic_id'] ?? $question->topic_id,
            'tipe' => $tipe,
            'teks' => isset($data['teks']) ? trim($data['teks']) : $question->teks,
            'opsi_json' => $options,
            'jawaban' => isset($data['jawaban']) ? trim($data['jawaban']) : $question->jawaban,
            'penjelasan' => isset($data['penjelasan']) ? trim($data['penjelasan']) : $question->penjelasan,
            'kesulitan' => $data['kesulitan'] ?? $question->kesulitan,
            'versi' => $versionChanged ? $question->versi + 1 : $question->versi,
        ]);

        return $question;
    }

    /**
     * Hapus soal.
     */
    public function deleteQuestion(Question $question): bool
    {
        return (bool) $question->delete();
    }

    /**
     * Ambil semua mata pelajaran dan topik untuk form dropdown.
     */
    public function getCurriculumDropdownData(): array
    {
        $subjects = Subject::with('topics')->get();
        return [
            'subjects' => $subjects,
            'topics' => Topic::all(['id', 'subject_id', 'nama']),
        ];
    }
}
