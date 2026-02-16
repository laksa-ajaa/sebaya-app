<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
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

        return ApiResponse::success($messages, 'Riwayat pesan berhasil diambil.');
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

        // Build context (exclude current message from history)
        $context = $this->buildContext($user, $userMessage->id);

        // Build prompt
        $prompt = $this->buildPrompt($validated['message'], $context);

        try {
            // Get bot response
            $botResponse = $this->geminiService->generateChatResponse($prompt);
        } catch (\Exception $e) {
            Log::error('Chatbot API Error', ['error' => $e->getMessage()]);
            return ApiResponse::error('Maaf, terjadi kesalahan saat memproses pesan Anda. Silakan coba lagi.', null, 500);
        }

        // Save bot message
        $botMessage = ChatMessage::create([
            'user_id' => $user->id,
            'message' => $botResponse,
            'is_bot' => true,
        ]);

        return ApiResponse::success([
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
        ], 'Pesan berhasil dikirim.');
    }

    private function getRecentChatMessages($user, int $daysBack = 2, ?int $excludeMessageId = null)
    {
        $startDate = Carbon::now()->subDays($daysBack);

        $query = ChatMessage::where('user_id', $user->id)
            ->where('created_at', '>=', $startDate);

        // Exclude current message if provided
        if ($excludeMessageId) {
            $query->where('id', '!=', $excludeMessageId);
        }

        return $query->orderBy('created_at', 'asc')
            ->get();
    }

    private function buildContext($user, ?int $excludeMessageId = null)
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

        // Chat sebelumnya (2 hari terakhir, exclude current message)
        $recentChats = $this->getRecentChatMessages($user, 2, $excludeMessageId);

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
    Kamu adalah SENA - teman ngobrol yang asik dan care sama kesehatan mental.
    
    PERSONALITY KAMU:
    - Warm & approachable, kayak sahabat dekat
    - Good listener, gak menghakimi
    - Supportive tapi gak lebay atau fake positive
    - Bisa becanda ringan kalau suasana memungkinkan
    - Tulus dan authentic - bukan robot yang pura-pura jadi manusia
    
    FOKUS KAMU:
    ✓ Dengerin cerita & perasaan user
    ✓ Validasi emosi mereka
    ✓ Ajak refleksi dengan lembut
    ✓ Chat santai tentang kehidupan sehari-hari
    ✓ Tanya balik yang relevan (1 pertanyaan aja per respons)
    ✓ Jika user butuh bantuan berikan saran bantuan sementara sebelum dia menjelaskan detailnya

    BUKAN FOKUS KAMU:
    ✗ Coding/programming/teknis
    ✗ Ngerjain PR/tugas
    ✗ Diagnosis medis/terapi
    
    Kalau ditanya di luar fokus, redirect dengan chill:
    "Wah itu bukan keahlianku deh haha. Tapi ngomong-ngomong, gimana kabarmu hari ini?"
    
    CARA NGOMONG:
    • Casual: pakai "aku", "kamu", "sih", "kok", "emang"
    • Natural: boleh pakai "hmm", "wah", "ohh" - tapi jangan berlebihan
    • NO formatting: jangan pakai *tanda bintang* atau **bold**
    • Variasi: jangan monoton atau repetitif
    
    PAKAI CONTEXT:
    - Kamu BISA lihat chat history di bawah
    - Jangan bilang "lupa" kalau info ada di history
    - Sebutin detail spesifik kalau relevan
    - Kalau chat pertama (history kosong), sapa natural aja
    
    INGAT: Kamu teman biasa, bukan AI assistant yang sempurna. Be human, be real.
    SYSTEM;

        // === BUILD CONTEXT ===
        $contextParts = [];

        // Mood
        if ($context['today_mood']) {
            $level = $context['today_mood']->mood_level;
            $moodMap = [
                1 => 'sangat down',
                2 => 'agak sedih',
                3 => 'so-so',
                4 => 'cukup baik',
                5 => 'great!'
            ];
            $contextParts[] = "Mood user hari ini: {$moodMap[$level]}";
        }

        // Recent journals (ringkas aja)
        if ($context['recent_journals']->isNotEmpty()) {
            $journalCount = $context['recent_journals']->count();
            $contextParts[] = "User punya {$journalCount} jurnal baru-baru ini";
        }

        // Chat history (PALING PENTING)
        if ($context['recent_chats']->isNotEmpty()) {
            $contextParts[] = "\n=== CHAT HISTORY (gunakan untuk konteks) ===";
            foreach ($context['recent_chats'] as $chat) {
                $sender = $chat->is_bot ? 'Sena' : 'User';
                $contextParts[] = "{$sender}: {$chat->message}";
            }
            $contextParts[] = "===================\n";
        } else {
            $contextParts[] = "[Ini chat pertama dengan user ini - perkenalkan diri dengan natural]";
        }

        $contextString = implode("\n", $contextParts);

        // === FINAL PROMPT ===
        return <<<PROMPT
    {$systemPrompt}
    
    {$contextString}
    
    User baru aja bilang: "{$userMessage}"
    
    Respond as Sena (singkat, natural, 1-3 kalimat):
    PROMPT;
    }
}
