<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $endpoint;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY'));

        // Model ringan & hemat token (recommended)
        $this->endpoint =
            'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash-lite:generateContent';

        if (empty($this->apiKey)) {
            Log::warning('GEMINI_API_KEY tidak ditemukan');
        }
    }

    /**
     * Generate AI response untuk mood check
     */
    public function generateSupportiveResponse(
        int $moodLevel,
        array $previousJournals,
        bool $isFirstMoodCheck,
        ?int $daysSinceLastCheck
    ): string {
        $prompt = $this->buildPrompt(
            $moodLevel,
            $previousJournals,
            $isFirstMoodCheck,
            $daysSinceLastCheck
        );

        Log::debug('Gemini Prompt Built', [
            'moodLevel' => $moodLevel,
            'isFirstMoodCheck' => $isFirstMoodCheck,
            'daysSinceLastCheck' => $daysSinceLastCheck,
            'prompt' => $prompt,
        ]);

        try {
            $response = Http::timeout(30)->post(
                $this->endpoint . '?key=' . $this->apiKey,
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topP' => 0.9,
                        'maxOutputTokens' => 150,
                    ]
                ]
            );

            if (!$response->successful()) {
                Log::error('Gemini HTTP Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->fallback($moodLevel);
            }

            $parts = $response->json()['candidates'][0]['content']['parts'] ?? [];

            $text = collect($parts)
                ->pluck('text')
                ->filter()
                ->implode('');

            // Guard: cegah jawaban terlalu pendek / tidak manusiawi
            if (str_word_count($text) < 12) {
                Log::warning('Gemini response terlalu pendek', ['text' => $text]);
                return $this->fallback($moodLevel);
            }

            return $this->cleanAsteriskFormatting(trim($text));
        } catch (\Throwable $e) {
            Log::error('Gemini Exception', [
                'message' => $e->getMessage(),
            ]);

            return $this->fallback($moodLevel);
        }
    }

    /**
     * Generate AI response untuk chatbot
     */
    public function generateChatResponse(string $prompt): string
    {
        try {
            $response = Http::timeout(30)->post(
                $this->endpoint . '?key=' . $this->apiKey,
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topP' => 0.9,
                        'maxOutputTokens' => 500,
                    ]
                ]
            );

            if (!$response->successful()) {
                Log::error('Gemini Chat HTTP Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Gemini API returned error: ' . $response->status());
            }

            $parts = $response->json()['candidates'][0]['content']['parts'] ?? [];

            $text = collect($parts)
                ->pluck('text')
                ->filter()
                ->implode('');

            return $this->cleanAsteriskFormatting(trim($text));
        } catch (\Throwable $e) {
            Log::error('Gemini Chat Exception', [
                'message' => $e->getMessage(),
            ]);

            throw new \Exception('Gemini API exception: ' . $e->getMessage());
        }
    }

    private function buildPrompt(
        int $moodLevel,
        array $previousJournals,
        bool $isFirstMoodCheck,
        ?int $daysSinceLastCheck
    ): string {
        $moodDescription = match ($moodLevel) {
            1 => 'sangat buruk',
            2 => 'buruk',
            3 => 'netral / biasa saja',
            4 => 'baik',
            5 => 'sangat baik',
            default => 'tidak diketahui',
        };

        // Konteks waktu
        if ($isFirstMoodCheck) {
            $timeContext = 'Ini adalah pertama kalinya pengguna melakukan mood check.';
        } elseif ($daysSinceLastCheck !== null && $daysSinceLastCheck > 1) {
            $timeContext = "Pengguna terakhir melakukan mood check {$daysSinceLastCheck} hari yang lalu.";
        } else {
            $timeContext = 'Pengguna rutin melakukan mood check.';
        }

        // Ringkas jurnal (BUKAN raw text)
        $journalSummary = $this->summarizeJournal($previousJournals);

        return <<<PROMPT
            Kamu adalah teman cerita yang empatik dan hangat, berbicara seperti manusia biasa.

            KONDISI:
            - Mood hari ini: {$moodDescription} (level {$moodLevel}/5)
            - Konteks waktu: {$timeContext}

            KONTEKS JURNAL SEBELUMNYA:
            Pengguna pernah menulis tentang: {$journalSummary}.

            ATURAN WAJIB:
            - Jawaban maksimal 220 karakter
            - Tulis dalam 2-3 kalimat yang menyatu dan mengalir
            - Jangan gunakan baris baru
            - Jangan menjelaskan alasan atau proses berpikir
            - Respon harus alami, dan hangat
            - Jangan pernah mention terkait jurnal sebelumnya jika tidak ada jurnal dalam 7 hari terakhir.
            - JANGAN PERNAH gunakan tanda bintang (*) untuk penekanan atau formatting apapun

            RESPON:
            PROMPT;
    }

    /**
     * =========================
     * RINGKAS JURNAL
     * =========================
     */
    private function summarizeJournal(array $previousJournals): string
    {
        if (empty($previousJournals)) {
            return 'Tidak ada jurnal dalam 7 hari terakhir.';
        }

        return collect($previousJournals)
            ->map(function ($journal) {
                $date = $journal['date'] ?? '-';
                $content = trim(strip_tags($journal['content'] ?? ''));

                // Batasi panjang per jurnal (biar token hemat)
                if (strlen($content) > 256) {
                    $content = substr($content, 0, 256) . '…';
                }

                return "- ({$date}) {$content}";
            })
            ->implode("\n");
    }

    /**
     * =========================
     * FALLBACK RESPONSE
     * =========================
     */
    private function fallback(int $moodLevel): string
    {
        return match ($moodLevel) {
            1, 2 =>
            "Terima kasih sudah jujur dengan perasaanmu hari ini. Tidak apa-apa jika hari ini terasa berat, kamu tidak sendirian. Kalau kamu mau, menuliskan sedikit isi hatimu di jurnal bisa membantu.",

            3 =>
            "Hari yang terasa biasa saja juga tetap berarti. Kamu sudah melakukan hal baik dengan menyadari perasaanmu hari ini. Kalau berkenan, kamu bisa menuliskan sedikit tentang apa yang kamu rasakan.",

            4, 5 =>
            "Senang mendengar suasana hatimu hari ini cukup baik. Semoga hal-hal positif ini bisa terus berlanjut. Kalau kamu mau, catat momen baik hari ini di jurnal agar bisa dikenang nanti.",

            default =>
            "Terima kasih sudah melakukan check-in hari ini. Aku ada di sini kalau kamu ingin berbagi cerita 🤍",
        };
    }

    /**
     * =========================
     * CLEAN ASTERISK FORMATTING
     * =========================
     * Menghilangkan formatting markdown dengan asterisk (*)
     * Contoh: "Aku *sangat* senang" -> "Aku sangat senang"
     */
    private function cleanAsteriskFormatting(string $text): string
    {
        // Remove bold (**text**) dan italic (*text*)
        $text = preg_replace('/\*\*(.+?)\*\*/', '$1', $text); // **bold** -> bold
        $text = preg_replace('/\*(.+?)\*/', '$1', $text);     // *italic* -> italic

        // Remove standalone asterisks yang tersisa
        $text = str_replace('*', '', $text);

        return $text;
    }
}
