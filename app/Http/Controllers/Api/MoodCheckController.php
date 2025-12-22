<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\MoodCheck;
use App\Services\GeminiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MoodCheckController extends Controller
{
    protected GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Check mood hari ini
     * User pilih mood -> sistem deteksi jurnal sebelumnya -> Gemini beri response -> simpan
     */
    public function checkMood(Request $request)
    {
        $request->validate([
            'mood_level' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $user = Auth::guard('api')->user();
        $today = now()->toDateString();

        // Cek apakah sudah ada mood check hari ini
        $existingMoodCheck = MoodCheck::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existingMoodCheck) {
            return response()->json([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'mood_entry' => [
                        'id' => $existingMoodCheck->id,
                        'mood_type' => $this->getMoodType($existingMoodCheck->mood_level),
                        'label' => $this->getMoodLabel($existingMoodCheck->mood_level),
                        'mood_level' => $existingMoodCheck->mood_level,
                        'ai_response' => $existingMoodCheck->ai_response,
                        'timestamp' => $existingMoodCheck->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i:s') . '+07:00',
                    ],
                ],
            ], 200);
        }

        DB::beginTransaction();
        try {
            // Ambil jurnal kemarin saja
            $yesterday = Carbon::now()->subDay()->toDateString();
            $previousJournals = Journal::where('user_id', $user->id)
                ->where('date', $yesterday)
                ->orderBy('date', 'desc')
                ->get()
                ->map(function ($journal) {
                    return [
                        'date' => $journal->date->format('Y-m-d'),
                        'content' => $journal->content,
                    ];
                })
                ->toArray();

            // KODE LAMA: Ambil jurnal sebelumnya (maksimal 7 jurnal terakhir)
            // $previousJournals = Journal::where('user_id', $user->id)
            //     ->where('date', '<', $today)
            //     ->orderBy('date', 'desc')
            //     ->limit(7)
            //     ->get()
            //     ->map(function ($journal) {
            //         return [
            //             'date' => $journal->date->format('Y-m-d'),
            //             'content' => $journal->content,
            //         ];
            //     })
            //     ->toArray();

            // Tentukan apakah ini mood check pertama dan jarak hari sejak mood check terakhir
            $hasPreviousMoodChecks = \App\Models\MoodCheck::where('user_id', $user->id)->exists();
            $isFirstMoodCheck = !$hasPreviousMoodChecks;

            $lastMoodCheck = \App\Models\MoodCheck::where('user_id', $user->id)
                ->orderBy('date', 'desc')
                ->first();

            $daysSinceLastCheck = null;
            if ($lastMoodCheck) {
                $daysSinceLastCheck = Carbon::now()->startOfDay()->diffInDays(Carbon::parse($lastMoodCheck->date)->startOfDay());
            }

            // Generate AI response dari Gemini
            $aiResponse = $this->geminiService->generateSupportiveResponse(
                $request->mood_level,
                $previousJournals,
                $isFirstMoodCheck,
                $daysSinceLastCheck
            );

            // Simpan mood check dengan AI response
            $moodCheck = MoodCheck::create([
                'user_id' => $user->id,
                'mood_level' => $request->mood_level,
                'ai_response' => $aiResponse,
                'date' => $today,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'mood_entry' => [
                        'id' => $moodCheck->id,
                        'mood_type' => $this->getMoodType($moodCheck->mood_level),
                        'label' => $this->getMoodLabel($moodCheck->mood_level),
                        'mood_level' => $moodCheck->mood_level,
                        'ai_response' => $moodCheck->ai_response,
                        'timestamp' => $moodCheck->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i:s') . '+07:00',
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan mood check',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get mood type from mood level
     */
    private function getMoodType(int $moodLevel): string
    {
        return match ($moodLevel) {
            1 => 'SAD',
            2 => 'SAD',
            3 => 'NEUTRAL',
            4 => 'HAPPY',
            5 => 'HAPPY',
            default => 'NEUTRAL',
        };
    }

    /**
     * Get mood label from mood level
     */
    private function getMoodLabel(int $moodLevel): string
    {
        return match ($moodLevel) {
            1 => 'Sangat Sedih',
            2 => 'Sedih',
            3 => 'Netral',
            4 => 'Senang',
            5 => 'Sangat Senang',
            default => 'Netral',
        };
    }

    /**
     * Get mood check hari ini
     */
    public function getTodayMoodCheck(Request $request)
    {
        $user = Auth::guard('api')->user();
        $today = now()->toDateString();

        $moodCheck = MoodCheck::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$moodCheck) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada mood check untuk hari ini',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => [
                'mood_entry' => [
                    'id' => $moodCheck->id,
                    'mood_type' => $this->getMoodType($moodCheck->mood_level),
                    'label' => $this->getMoodLabel($moodCheck->mood_level),
                    'mood_level' => $moodCheck->mood_level,
                    'ai_response' => $moodCheck->ai_response,
                    'timestamp' => $moodCheck->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i:s') . '+07:00',
                ],
            ],
        ], 200);
    }

    /**
     * Get history mood checks (opsional, untuk tracking)
     */
    public function getMoodHistory(Request $request)
    {
        $user = Auth::guard('api')->user();
        $limit = $request->get('limit', 30);

        $moodChecks = MoodCheck::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($moodCheck) {
                return [
                    'id' => $moodCheck->id,
                    'mood_level' => $moodCheck->mood_level,
                    'ai_response' => $moodCheck->ai_response,
                    'date' => $moodCheck->date->format('Y-m-d'),
                    'created_at' => $moodCheck->created_at,
                ];
            });

        return response()->json([
            'message' => 'History mood check berhasil diambil',
            'mood_checks' => $moodChecks,
        ], 200);
    }

    /**
     * Reset/Delete mood check hari ini
     */
    public function resetMoodCheck(Request $request)
    {
        $user = Auth::guard('api')->user();
        $today = now()->toDateString();

        $moodCheck = MoodCheck::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$moodCheck) {
            return response()->json([
                'message' => 'Tidak ada mood check untuk hari ini',
                'success' => false,
            ], 404);
        }

        try {
            $moodCheck->delete();

            return response()->json([
                'message' => 'Mood check hari ini berhasil dihapus',
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus mood check',
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
