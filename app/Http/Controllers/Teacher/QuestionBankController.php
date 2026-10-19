<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Question\ImportQuestionCsvRequest;
use App\Http\Requests\Question\StoreQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Models\Question;
use App\Services\Question\CsvQuestionImporter;
use App\Services\Question\QuestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuestionBankController extends Controller
{
    public function __construct(
        protected QuestionService $questionService
    ) {}

    /**
     * Tampilkan daftar bank soal dengan filter & paginasi.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'subject_id', 'topic_id', 'tipe', 'kesulitan']);
        $questions = $this->questionService->listQuestions($filters, 15);
        $curriculum = $this->questionService->getCurriculumDropdownData();

        return Inertia::render('Teacher/Questions/Index', [
            'questions' => $questions,
            'filters' => $filters,
            'subjects' => $curriculum['subjects'],
            'topics' => $curriculum['topics'],
        ]);
    }

    /**
     * Tampilkan formulir pembuatan soal baru.
     */
    public function create(): Response
    {
        $curriculum = $this->questionService->getCurriculumDropdownData();

        return Inertia::render('Teacher/Questions/Create', [
            'subjects' => $curriculum['subjects'],
            'topics' => $curriculum['topics'],
        ]);
    }

    /**
     * Simpan soal baru.
     */
    public function store(StoreQuestionRequest $request): RedirectResponse
    {
        $this->questionService->createQuestion($request->validated());

        return redirect()->route('teacher.questions.index')
            ->with('success', 'Soal baru berhasil ditambahkan ke Bank Soal.');
    }

    /**
     * Tampilkan formulir edit soal.
     */
    public function edit(int $id): Response
    {
        $question = Question::with('topic.subject')->findOrFail($id);
        $curriculum = $this->questionService->getCurriculumDropdownData();

        return Inertia::render('Teacher/Questions/Edit', [
            'question' => $question,
            'subjects' => $curriculum['subjects'],
            'topics' => $curriculum['topics'],
        ]);
    }

    /**
     * Perbarui data soal.
     */
    public function update(int $id, UpdateQuestionRequest $request): RedirectResponse
    {
        $question = Question::findOrFail($id);
        $this->questionService->updateQuestion($question, $request->validated());

        return redirect()->route('teacher.questions.index')
            ->with('success', 'Soal berhasil diperbarui.');
    }

    /**
     * Hapus soal dari bank soal.
     */
    public function destroy(int $id): RedirectResponse
    {
        $question = Question::findOrFail($id);
        $this->questionService->deleteQuestion($question);

        return redirect()->back()
            ->with('success', 'Soal berhasil dihapus dari Bank Soal.');
    }

    /**
     * Impor soal massal dari berkas CSV.
     */
    public function importCsv(ImportQuestionCsvRequest $request, CsvQuestionImporter $importer): RedirectResponse
    {
        $file = $request->file('file_csv');
        $defaultTopicId = $request->input('default_topic_id') ? (int) $request->input('default_topic_id') : null;

        $result = $importer->import($file, $defaultTopicId);

        if ($result['imported'] > 0) {
            $msg = "Berhasil mengimpor {$result['imported']} soal ke Bank Soal.";
            if ($result['failed'] > 0) {
                $msg .= " ({$result['failed']} baris gagal/dilewati).";
            }
            return redirect()->route('teacher.questions.index')->with('success', $msg);
        }

        $errorMsg = !empty($result['errors']) ? implode(' ', array_slice($result['errors'], 0, 3)) : 'Tidak ada soal yang berhasil diimpor.';
        return redirect()->back()->with('error', "Gagal mengimpor CSV: {$errorMsg}");
    }

    /**
     * Unduh template CSV contoh.
     */
    public function downloadTemplate(CsvQuestionImporter $importer): StreamedResponse
    {
        $content = $importer->getTemplateCsvContent();

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'template_bank_soal_edugame.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
