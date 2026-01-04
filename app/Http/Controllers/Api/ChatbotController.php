<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\MoodCheck;
use App\Models\Journal;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChatbotController extends Controller
{
    protected GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Get all chat messages for the authenticated user
     */
    public function index()
    {
        $user = Auth::guard('api')->user();

        $messages = ChatMessage::where('user_id', $user->id)
            ->where('is_bot', true)  // Only show bot messages in history
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'is_bot' => $message->is_bot,
                    'created_at' => $message->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Send a message and get bot response
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::guard('api')->user();

        // Save user message
        $userMessage = ChatMessage::create([
            'user_id' => $user->id,
            'message' => $validated['message'],
            'is_bot' => false,
        ]);

        // Build context
        $context = $this->buildContext($user);

        // Build prompt
        $prompt = $this->buildPrompt($validated['message'], $context);

        try {
            // Get bot response
            $botResponse = $this->geminiService->generateChatResponse($prompt);
        } catch (\Exception $e) {
            Log::error('Chatbot API Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Maaf, terjadi kesalahan saat memproses pesan Anda. Silakan coba lagi.',
            ], 500);
        }

        // Save bot message
        $botMessage = ChatMessage::create([
            'user_id' => $user->id,
            'message' => $botResponse,
            'is_bot' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'user_message' => [
                    'id' => $userMessage->id,
                    'message' => $userMessage->message,
                    'is_bot' => false,
                    'created_at' => $userMessage->created_at,
                ],
                'bot_response' => [
                    'id' => $botMessage->id,
                    'message' => $botResponse,
                    'is_bot' => true,
                    'created_at' => $botMessage->created_at,
                ],
            ],
        ]);
    }

    private function getRecentChatMessages($user, int $limit = 6)
    {
        return ChatMessage::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->reverse(); // penting: urutkan ulang
    }

    private function buildContext($user)
    {
        $today = Carbon::today();

        // Mood hari ini
        $todayMood = MoodCheck::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->latest()
            ->first();

        // Jurnal sebelumnya (dalam 7 hari terakhir)
        $recentJournals = Journal::where('user_id', $user->id)
            ->where('created_at', '>=', $today->copy()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get(['content', 'created_at']);

        // Chat sebelumnya (6 pesan terakhir)
        $recentChats = $this->getRecentChatMessages($user);

        return [
            'today_mood' => $todayMood,
            'recent_journals' => $recentJournals,
            'recent_chats' => $recentChats,
        ];
    }

    private function buildPrompt(string $userMessage, array $context): string
    {
        // === SYSTEM PROMPT ===
        $systemPrompt = <<<SYSTEM
Kamu adalah teman cerita yang empatik dan hangat.
Jawaban 3–5 kalimat, bahasa Indonesia natural.
Tidak menggurui, tidak menyebutkan kamu AI.
SYSTEM;

        // === MOOD ===
        $moodPrompt = '';
        if ($context['today_mood']) {
            $level = $context['today_mood']->mood_level;
            $moodPrompt = "Mood pengguna hari ini berada di level {$level}/5.\n";
        }

        // === JOURNAL CONTEXT ===
        $journalPrompt = '';
        if ($context['recent_journals']->isNotEmpty()) {
            $journalPrompt = "Catatan jurnal terakhir pengguna:\n";
            foreach ($context['recent_journals'] as $journal) {
                $content = substr(strip_tags($journal->content), 0, 120);
                $journalPrompt .= "- {$content}\n";
            }
        }

        // === CHAT HISTORY (PALING PENTING) ===
        $chatHistoryPrompt = '';
        if ($context['recent_chats']->isNotEmpty()) {
            foreach ($context['recent_chats'] as $chat) {
                $role = $chat->is_bot ? 'Assistant' : 'User';
                $chatHistoryPrompt .= "{$role}: {$chat->message}\n";
            }
        }

        return <<<PROMPT
{$systemPrompt}

{$moodPrompt}
{$journalPrompt}

Percakapan sebelumnya:
{$chatHistoryPrompt}

User: {$userMessage}
Assistant:
PROMPT;
    }
}
