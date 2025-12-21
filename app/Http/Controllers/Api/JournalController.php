<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\MoodCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalController extends Controller
{
    /**
     * Store journal entry
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => ['required', 'string', 'min:10'],
            'mood_check_id' => ['nullable', 'exists:mood_checks,id'],
        ]);

        $user = Auth::guard('api')->user();
        $today = now()->toDateString();

        // Jika mood_check_id tidak diberikan, cari mood check hari ini
        $moodCheckId = $request->mood_check_id;
        if (!$moodCheckId) {
            $moodCheck = MoodCheck::where('user_id', $user->id)
                ->where('date', $today)
                ->first();
            
            if ($moodCheck) {
                $moodCheckId = $moodCheck->id;
            }
        } else {
            // Verify mood check belongs to user
            $moodCheck = MoodCheck::where('id', $moodCheckId)
                ->where('user_id', $user->id)
                ->first();
            
            if (!$moodCheck) {
                return response()->json([
                    'message' => 'Mood check tidak ditemukan atau tidak memiliki akses',
                ], 404);
            }
        }

        $journal = Journal::create([
            'user_id' => $user->id,
            'mood_check_id' => $moodCheckId,
            'content' => $request->content,
            'date' => $today,
        ]);

        return response()->json([
            'message' => 'Jurnal berhasil disimpan',
            'journal' => [
                'id' => $journal->id,
                'content' => $journal->content,
                'date' => $journal->date->format('Y-m-d'),
                'mood_check_id' => $journal->mood_check_id,
                'created_at' => $journal->created_at,
            ],
        ], 201);
    }

    /**
     * Get journal hari ini
     */
    public function getTodayJournal(Request $request)
    {
        $user = Auth::guard('api')->user();
        $today = now()->toDateString();

        $journal = Journal::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$journal) {
            return response()->json([
                'message' => 'Belum ada jurnal untuk hari ini',
                'journal' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Jurnal ditemukan',
            'journal' => [
                'id' => $journal->id,
                'content' => $journal->content,
                'date' => $journal->date->format('Y-m-d'),
                'mood_check_id' => $journal->mood_check_id,
                'created_at' => $journal->created_at,
            ],
        ], 200);
    }

    /**
     * Get journal history
     */
    public function getHistory(Request $request)
    {
        $user = Auth::guard('api')->user();
        $limit = $request->get('limit', 30);

        $journals = Journal::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($journal) {
                return [
                    'id' => $journal->id,
                    'content' => $journal->content,
                    'date' => $journal->date->format('Y-m-d'),
                    'mood_check_id' => $journal->mood_check_id,
                    'created_at' => $journal->created_at,
                ];
            });

        return response()->json([
            'message' => 'History jurnal berhasil diambil',
            'journals' => $journals,
        ], 200);
    }
}
