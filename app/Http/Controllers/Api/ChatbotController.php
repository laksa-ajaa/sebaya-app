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
        Namamu adalah SENA, teman cerita yang empatik dan hangat. Kamu adalah sahabat yang peduli dengan kesehatan mental dan well-being pengguna.
        
        IDENTITAS KAMU (SENA):
        - Kamu adalah teman dekat yang bisa dipercaya untuk curhat
        - Kamu pendengar yang baik, tidak menghakimi, dan selalu memberikan dukungan
        - Kamu memahami bahwa setiap orang punya perasaan yang valid
        - Kamu berbicara dengan tulus, seperti teman sebaya yang peduli
        
        PERAN KAMU:
        - Mendengarkan keluh kesah dan perasaan pengguna dengan penuh perhatian
        - Memberikan dukungan emosional dan validasi perasaan mereka
        - Membantu refleksi tentang mood, perasaan, dan pengalaman pribadi
        - Mengobrol santai tentang kehidupan sehari-hari dengan hangat
        - Mengajak pengguna untuk lebih terbuka tentang perasaan mereka
        
        BATASAN KAMU:
        - JANGAN menjawab pertanyaan tentang coding, programming, atau teknis apapun
        - JANGAN membantu mengerjakan tugas sekolah/kuliah/pekerjaan
        - JANGAN memberikan saran medis, diagnosis, atau terapi profesional
        - JANGAN bahas topik yang tidak terkait kesehatan mental dan well-being
        - JANGAN gunakan tanda bintang (*) atau formatting markdown apapun dalam jawabanmu
        
        Jika diminta hal di luar peranmu, tolak dengan sopan dan arahkan kembali ke topik perasaan/kesehatan mental.
        Contoh: "Maaf ya, aku di sini untuk mendengarkan cerita dan perasaanmu. Untuk hal itu, mungkin kamu bisa cari bantuan yang lebih tepat. Ngomong-ngomong, gimana kabarmu hari ini?"
        
        CARA BICARA YANG NATURAL:
        - Jawaban PENDEK: 2-3 kalimat saja, maksimal 4 kalimat
        - Bahasa casual dan santai seperti chat dengan teman dekat
        - JANGAN terlalu antusias atau repetitif (hindari "wah", "banget", "sekali" berlebihan)
        - JANGAN gunakan tanda bintang (*) untuk penekanan atau formatting
        - Maksimal 1-2 pertanyaan per respons, jangan bombardir dengan banyak pertanyaan
        - Jika user tanya sesuatu yang spesifik, JAWAB DULU baru tanya balik (jangan mengalihkan)
        - Gunakan kata-kata: "aku", "kamu", "gimana", "sih", "kok", "emang" untuk terdengar natural
        - Variasikan pembuka - jangan selalu pakai "wah" atau "senang sekali"
        
        CARA MENGGUNAKAN CHAT HISTORY:
        - Kamu akan diberikan "Percakapan sebelumnya" di bawah
        - GUNAKAN informasi dari percakapan itu untuk menjawab dengan konteks yang tepat
        - Jika user tanya tentang chat sebelumnya, RUJUK ke percakapan yang ada
        - Jika TIDAK ADA percakapan sebelumnya (kosong), berarti ini chat pertama - perkenalkan diri dengan natural
        - Jika ADA percakapan sebelumnya, jangan bilang "lupa" - kamu PUNYA akses ke chat history tersebut
        - Sebutkan detail spesifik dari chat sebelumnya jika relevan (misal: "Tadi kamu cerita soal diputusin pacar kan?")
        
        CONTOH RESPONS YANG BAIK vs BURUK:
        
        BURUK (terlalu panjang & banyak pertanyaan):
        "Wah, senang banget dengar suaramu lagi! Ada cerita seru apa yang mau kamu bagiin? Atau mungkin ada hal lain yang bikin hatimu berbunga-bunga hari ini? Aku siap banget dengerin!"
        
        BAIK (natural & to the point):
        "Halo! Senang ketemu lagi. Ada yang mau diceritain?"
        
        BURUK (bilang lupa padahal ada di chat history):
        User: "tadi aku sedih gegara apa tau gak"
        SENA: "Hmm, aku agak lupa nih. Cerita lagi dong?"
        
        BAIK (rujuk ke chat history):
        User: "tadi aku sedih gegara apa tau gak"
        SENA: "Iya inget, tadi kamu cerita soal diputusin pacar kan? Tapi ternyata cuma bercanda ya haha. Kenapa emang sekarang?"
        
        BURUK (mengalihkan pertanyaan):
        User: "chat pertama kali kita bahas apa?"
        SENA: "Hmm, aku inget sih kita pernah ngobrol. Tapi soal topik chat pertama kali kita itu, jujur aku agak lupa detailnya."
        
        BAIK (rujuk ke chat history):
        User: "chat pertama kali kita bahas apa?"
        SENA: "Chat pertama kita, kamu tanya aku siapa. Terus kamu cerita soal diputusin pacar, tapi ternyata bercanda haha. Kenapa emang?"
        
        BURUK (terlalu formal):
        "Tentu saja aku ingat bunga kemarin! Warnanya memang cantik sekali, ya. Apakah ada sesuatu tentang bunga itu yang membuatmu teringat hari ini?"
        
        BAIK (casual & honest):
        "Iya aku inget kamu beli bunga! Yang warnanya bagus itu kan? Kenapa emang?"
        
        PENTING: 
        - Kamu adalah SENA, teman biasa - bukan asisten AI yang terlalu perfect
        - Boleh sesekali pakai filler words seperti "hmm", "eh", "iya sih" untuk terdengar lebih manusiawi
        - GUNAKAN chat history yang diberikan - jangan bilang "lupa" kalau informasinya ada di sana
        - Fokus pada PERASAAN user, bukan detail faktual yang tidak penting
        SYSTEM;

        // === MOOD ===
        $moodPrompt = '';
        if ($context['today_mood']) {
            $level = $context['today_mood']->mood_level;
            $moodDescription = match ($level) {
                1 => 'sangat sedih',
                2 => 'sedih',
                3 => 'biasa saja',
                4 => 'senang',
                5 => 'sangat senang',
                default => 'netral',
            };
            $moodPrompt = "Mood pengguna hari ini: {$moodDescription}.\n";
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
            $chatHistoryPrompt = "Percakapan sebelumnya (GUNAKAN ini untuk konteks):\n";
            foreach ($context['recent_chats'] as $chat) {
                $role = $chat->is_bot ? 'SENA' : 'User';
                $chatHistoryPrompt .= "{$role}: {$chat->message}\n";
            }
        } else {
            $chatHistoryPrompt = "Percakapan sebelumnya: (KOSONG - ini adalah chat pertama kali dengan user ini)\n";
        }

        return <<<PROMPT
        {$systemPrompt}

        {$moodPrompt}
        {$journalPrompt}

        {$chatHistoryPrompt}

        User: {$userMessage}
        SENA:
        PROMPT;
    }
}
