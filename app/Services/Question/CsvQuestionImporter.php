<?php

namespace App\Services\Question;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\UploadedFile;

class CsvQuestionImporter
{
    /**
     * Impor soal dari berkas CSV.
     *
     * @return array{imported: int, failed: int, errors: array}
     */
    public function import(UploadedFile|string $file, ?int $defaultTopicId = null): array
    {
        $filePath = is_string($file) ? $file : $file->getRealPath();

        $imported = 0;
        $failed = 0;
        $errors = [];

        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [
                'imported' => 0,
                'failed' => 1,
                'errors' => ['Berkas CSV tidak dapat dibaca atau rusak.'],
            ];
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return [
                'imported' => 0,
                'failed' => 1,
                'errors' => ['Gagal membuka berkas CSV.'],
            ];
        }

        // 1. Baca header
        $rawHeader = fgetcsv($handle, 4096, ',');
        if (!$rawHeader || count($rawHeader) < 3) {
            // Coba delimiter titik koma (;)
            rewind($handle);
            $rawHeader = fgetcsv($handle, 4096, ';');
        }

        if (!$rawHeader) {
            fclose($handle);
            return [
                'imported' => 0,
                'failed' => 1,
                'errors' => ['Format header berkas CSV kosong atau tidak valid.'],
            ];
        }

        // Hapus BOM UTF-8 pada kolom pertama jika ada
        $rawHeader[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $rawHeader[0]);
        $headers = array_map(fn ($h) => strtolower(trim($h)), $rawHeader);

        // Ambil pemetaan indeks kolom
        $colTipe = $this->findColumnIndex($headers, ['tipe', 'type']);
        $colTeks = $this->findColumnIndex($headers, ['teks', 'pertanyaan', 'soal', 'question']);
        $colOpsiA = $this->findColumnIndex($headers, ['opsi_a', 'pilihan_a', 'a']);
        $colOpsiB = $this->findColumnIndex($headers, ['opsi_b', 'pilihan_b', 'b']);
        $colOpsiC = $this->findColumnIndex($headers, ['opsi_c', 'pilihan_c', 'c']);
        $colOpsiD = $this->findColumnIndex($headers, ['opsi_d', 'pilihan_d', 'd']);
        $colKunci = $this->findColumnIndex($headers, ['kunci_jawaban', 'kunci', 'jawaban', 'answer']);
        $colKesulitan = $this->findColumnIndex($headers, ['kesulitan', 'tingkat', 'level', 'difficulty']);
        $colPenjelasan = $this->findColumnIndex($headers, ['penjelasan', 'pembahasan', 'explanation']);
        $colTopik = $this->findColumnIndex($headers, ['topik', 'topic']);

        if ($colTeks === null || $colKunci === null) {
            fclose($handle);
            return [
                'imported' => 0,
                'failed' => 1,
                'errors' => ['Kolom wajib "teks" dan "kunci_jawaban" tidak ditemukan pada header CSV.'],
            ];
        }

        // Pastikan ada subjek default jika topik baru perlu dibuat
        $defaultSubject = Subject::firstOrCreate(
            ['nama' => 'Pengetahuan Umum'],
            ['jenjang' => 'SD']
        );

        $rowNumber = 1;
        while (($row = fgetcsv($handle, 4096, ',')) !== false) {
            $rowNumber++;

            // Abaikan baris kosong
            if (empty(array_filter($row))) {
                continue;
            }

            // Tangani pemisah titik koma jika seluruh baris berada di kolom 0
            if (count($row) === 1 && str_contains($row[0], ';')) {
                $row = str_getcsv($row[0], ';');
            }

            $teks = trim($row[$colTeks] ?? '');
            $kunci = trim($row[$colKunci] ?? '');

            if ($teks === '' || $kunci === '') {
                $failed++;
                $errors[] = "Baris #{$rowNumber}: Teks pertanyaan atau kunci jawaban tidak boleh kosong.";
                continue;
            }

            // Normalisasi tipe
            $rawTipe = strtolower(trim(str_replace(['-', ' '], '_', $row[$colTipe] ?? 'pilihan_ganda')));
            $tipe = match ($rawTipe) {
                'pilihan_ganda', 'pg', 'multiple_choice' => 'pilihan_ganda',
                'isian_singkat', 'isian', 'short_answer' => 'isian_singkat',
                'benar_salah', 'tf', 'true_false' => 'benar_salah',
                default => 'pilihan_ganda',
            };

            // Normalisasi tingkat kesulitan
            $rawKesulitan = strtolower(trim($row[$colKesulitan] ?? 'sedang'));
            $kesulitan = in_array($rawKesulitan, ['mudah', 'sedang', 'sulit']) ? $rawKesulitan : 'sedang';

            // Tentukan topik
            $topicId = $defaultTopicId;
            $topicName = trim($row[$colTopik] ?? '');
            if ($topicName !== '') {
                $topic = Topic::firstOrCreate(
                    ['nama' => $topicName],
                    ['subject_id' => $defaultSubject->id]
                );
                $topicId = $topic->id;
            }

            if (!$topicId) {
                // Gunakan topik pertama yang ada di database
                $fallbackTopic = Topic::first();
                if (!$fallbackTopic) {
                    $fallbackTopic = Topic::create([
                        'subject_id' => $defaultSubject->id,
                        'nama' => 'Topik Umum',
                    ]);
                }
                $topicId = $fallbackTopic->id;
            }

            // Format opsi jawaban
            $opsiJson = null;
            if ($tipe === 'pilihan_ganda') {
                $opsiA = trim($row[$colOpsiA] ?? '');
                $opsiB = trim($row[$colOpsiB] ?? '');
                $opsiC = trim($row[$colOpsiC] ?? '');
                $opsiD = trim($row[$colOpsiD] ?? '');

                if ($opsiA === '' || $opsiB === '') {
                    $failed++;
                    $errors[] = "Baris #{$rowNumber}: Soal pilihan ganda wajib memiliki minimal opsi A dan opsi B.";
                    continue;
                }

                $opsiJson = [
                    'A' => $opsiA,
                    'B' => $opsiB,
                    'C' => $opsiC,
                    'D' => $opsiD,
                ];

                // Normalisasi kunci jawaban: jika guru menulis "A", simpan "A", jika menulis teksnya, simpan hurufnya jika cocok
                $kunciUpper = strtoupper($kunci);
                if (in_array($kunciUpper, ['A', 'B', 'C', 'D'])) {
                    $kunci = $kunciUpper;
                }
            } elseif ($tipe === 'benar_salah') {
                $opsiJson = ['Benar', 'Salah'];
                $kunciLower = strtolower($kunci);
                $kunci = in_array($kunciLower, ['benar', 'true', 'b', '1']) ? 'Benar' : 'Salah';
            }

            $penjelasan = trim($row[$colPenjelasan] ?? '');

            // Simpan soal
            try {
                Question::create([
                    'topic_id' => $topicId,
                    'tipe' => $tipe,
                    'teks' => $teks,
                    'opsi_json' => $opsiJson,
                    'jawaban' => $kunci,
                    'penjelasan' => $penjelasan !== '' ? $penjelasan : 'Jawaban yang benar adalah ' . $kunci,
                    'kesulitan' => $kesulitan,
                    'versi' => 1,
                ]);

                $imported++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Baris #{$rowNumber}: Gagal menyimpan ke database ({$e->getMessage()}).";
            }
        }

        fclose($handle);

        return [
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * Cari indeks kolom berdasarkan beberapa kemungkinan nama kolom.
     */
    protected function findColumnIndex(array $headers, array $candidates): ?int
    {
        foreach ($candidates as $cand) {
            $idx = array_search($cand, $headers, true);
            if ($idx !== false) {
                return $idx;
            }
        }
        return null;
    }

    /**
     * Hasilkan template CSV siap pakai untuk diunduh guru.
     */
    public function getTemplateCsvContent(): string
    {
        $headers = ['tipe', 'teks', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'kunci_jawaban', 'kesulitan', 'penjelasan', 'topik'];

        $samples = [
            [
                'pilihan_ganda',
                'Berapakah hasil dari 7 x 8?',
                '54',
                '56',
                '58',
                '64',
                'B',
                'sedang',
                '7 dikali 8 sama dengan 56.',
                'Tabel Perkalian 6-9',
            ],
            [
                'benar_salah',
                'Matahari terbit dari sebelah timur.',
                '',
                '',
                '',
                '',
                'Benar',
                'mudah',
                'Bumi berotasi dari barat ke timur sehingga matahari tampak terbit dari timur.',
                'Tata Surya & Bumi',
            ],
            [
                'isian_singkat',
                'Ibu kota negara Indonesia saat ini adalah...',
                '',
                '',
                '',
                '',
                'Jakarta',
                'mudah',
                'Ibu kota negara Indonesia adalah Jakarta.',
                'Geografi Indonesia',
            ],
        ];

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        foreach ($samples as $sample) {
            fputcsv($output, $sample);
        }
        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content ?: '';
    }
}
