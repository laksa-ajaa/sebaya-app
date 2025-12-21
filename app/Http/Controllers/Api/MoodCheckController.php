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
                'message' => 'Mood check untuk hari ini sudah dilakukan',
                'mood_check' => $existingMoodCheck,
            ], 200);
        }

        DB::beginTransaction();
        try {
            // Ambil jurnal sebelumnya (maksimal 7 jurnal terakhir)
            $previousJournals = Journal::where('user_id', $user->id)
                ->where('date', '<', $today)
                ->orderBy('date', 'desc')
                ->limit(7)
                ->get()
                ->map(function ($journal) {
                    return [
                        'date' => $journal->date->format('Y-m-d'),
                        'content' => $journal->content,
                    ];
                })
                ->toArray();

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
                'message' => 'Mood check berhasil disimpan',
                'mood_check' => [
                    'id' => $moodCheck->id,
                    'mood_level' => $moodCheck->mood_level,
                    'ai_response' => $moodCheck->ai_response,
                    'date' => $moodCheck->date->format('Y-m-d'),
                    'jurnals_used' => $previousJournals,
                    'created_at' => $moodCheck->created_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan mood check',
                'error' => $e->getMessage(),
            ], 500);
        }
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
                'message' => 'Belum ada mood check untuk hari ini',
                'mood_check' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Mood check ditemukan',
            'mood_check' => [
                'id' => $moodCheck->id,
                'mood_level' => $moodCheck->mood_level,
                'ai_response' => $moodCheck->ai_response,
                'date' => $moodCheck->date->format('Y-m-d'),
                'created_at' => $moodCheck->created_at,
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
}
