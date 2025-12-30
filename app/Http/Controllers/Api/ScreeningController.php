<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScreeningAnswer;
use App\Models\ScreeningDimension;
use App\Models\ScreeningOption;
use App\Models\ScreeningPackage;
use App\Models\ScreeningQuestion;
use App\Models\ScreeningSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScreeningController extends Controller
{
    /**
     * List all active screening packages
     * GET /api/screening/packages
     */
    public function listPackages()
    {
        $packages = ScreeningPackage::where('is_active', true)
            ->with([
                'questions',
                'dimensions',
            ])
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'code' => $package->code,
                    'name' => $package->name,
                    'description' => $package->description,
                    'is_active' => $package->is_active,
                    'question_count' => $package->questions->count(),
                    'dimension_count' => $package->dimensions->count(),
                    'created_at' => $package->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'data' => $packages,
            'meta' => [
                'total' => $packages->count(),
            ],
        ]);
    }

    /**
     * Get package details with all questions and options
     * GET /api/screening/packages/{packageId}
     */
    public function showPackage($packageId)
    {
        $package = ScreeningPackage::with([
            'dimensions',
            'questions.options',
            'questions.dimensions',
        ])->findOrFail($packageId);

        return response()->json([
            'data' => [
                'id' => $package->id,
                'code' => $package->code,
                'name' => $package->name,
                'description' => $package->description,
                'is_active' => $package->is_active,
                'dimensions' => $package->dimensions->map(function ($dim) {
                    return [
                        'id' => $dim->id,
                        'code' => $dim->code,
                        'name' => $dim->name,
                        'description' => $dim->description,
                        'multiplier' => $dim->multiplier,
                    ];
                }),
                'questions' => $package->questions->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'question_text' => $question->question_text,
                        'order' => $question->order,
                        'options' => $question->options->map(function ($option) {
                            return [
                                'id' => $option->id,
                                'label' => $option->label,
                                'value' => $option->value,
                                'order' => $option->order,
                            ];
                        }),
                        'dimensions' => $question->dimensions->map(function ($dimension) {
                            return [
                                'id' => $dimension->id,
                                'code' => $dimension->code,
                                'name' => $dimension->name,
                                'weight' => $dimension->pivot->weight,
                            ];
                        }),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Start new screening session
     * POST /api/screening/sessions
     */
    public function startSession(Request $request)
    {
        $validated = $request->validate([
            'screening_package_id' => 'required|integer|exists:screening_packages,id',
        ]);

        $package = ScreeningPackage::where('id', $validated['screening_package_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $user = Auth::user();

        // Cek apakah user sudah punya sesi aktif (belum disubmit) untuk paket ini
        $existingSession = ScreeningSession::where('user_id', $user->id)
            ->where('screening_package_id', $package->id)
            ->whereNull('submitted_at')
            ->first();

        if ($existingSession) {
            return response()->json([
                'message' => 'You already have an active session for this package',
                'data' => [
                    'existing_session_id' => $existingSession->id,
                ],
            ], 409);
        }

        // Buat sesi baru
        $session = ScreeningSession::create([
            'user_id' => $user->id,
            'screening_package_id' => $package->id,
            'started_at' => now(),
            'submitted_at' => null,
        ]);

        return response()->json([
            'data' => [
                'id' => $session->id,
                'user_id' => $session->user_id,
                'screening_package_id' => $session->screening_package_id,
                'package_code' => $package->code,
                'started_at' => $session->started_at->toIso8601String(),
                'submitted_at' => $session->submitted_at,
                'created_at' => $session->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Get session questions with pagination
     * GET /api/screening/sessions/{sessionId}
     */
    public function getSessionQuestions($sessionId, Request $request)
    {
        $user = Auth::user();
        $session = ScreeningSession::with('package')
            ->where('id', $sessionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $perPage = $request->get('per_page', 10);

        // Get questions paginated
        $questions = $session->package->questions()
            ->with('options')
            ->orderBy('order')
            ->paginate($perPage);

        // Get user answers untuk sesi ini
        $userAnswers = ScreeningAnswer::where('screening_session_id', $sessionId)
            ->pluck('screening_option_id', 'screening_question_id');

        // Load option values untuk jawaban
        $optionValues = ScreeningOption::whereIn('id', $userAnswers->values())
            ->pluck('value', 'id');

        // Calculate progress
        $totalQuestions = $session->package->questions->count();
        $answeredCount = $userAnswers->count();

        $questionsData = $questions->items();
        $questionsData = array_map(function ($question) use ($userAnswers, $optionValues) {
            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'order' => $question->order,
                'user_answer' => $userAnswers->get($question->id),
                'user_answer_value' => isset($userAnswers[$question->id]) ? $optionValues[$userAnswers[$question->id]] : null,
                'options' => $question->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'label' => $option->label,
                        'value' => $option->value,
                    ];
                }),
            ];
        }, $questionsData);

        return response()->json([
            'data' => [
                'session_id' => $session->id,
                'package_code' => $session->package->code,
                'started_at' => $session->started_at->toIso8601String(),
                'submitted_at' => $session->submitted_at?->toIso8601String(),
                'progress' => [
                    'answered' => $answeredCount,
                    'total' => $totalQuestions,
                    'percentage' => $totalQuestions > 0 ? round(($answeredCount / $totalQuestions) * 100) : 0,
                ],
                'questions' => $questionsData,
            ],
            'meta' => [
                'current_page' => $questions->currentPage(),
                'per_page' => $questions->perPage(),
                'total' => $questions->total(),
                'last_page' => $questions->lastPage(),
            ],
        ]);
    }

    /**
     * Save answer for a question
     * POST /api/screening/sessions/{sessionId}/answers
     */
    public function saveAnswer($sessionId, Request $request)
    {
        $user = Auth::user();
        $session = ScreeningSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Cek apakah session sudah disubmit
        if ($session->submitted_at) {
            return response()->json([
                'message' => 'Cannot modify answers for submitted session',
                'errors' => [
                    'session_id' => ['Session ini sudah disubmit'],
                ],
            ], 422);
        }

        $validated = $request->validate([
            'screening_question_id' => 'required|integer',
            'screening_option_id' => 'required|integer',
        ]);

        // Validasi question ada di package ini
        $question = ScreeningQuestion::where('id', $validated['screening_question_id'])
            ->where('screening_package_id', $session->screening_package_id)
            ->firstOrFail();

        // Validasi option ada untuk question ini
        $option = ScreeningOption::where('id', $validated['screening_option_id'])
            ->where('screening_question_id', $validated['screening_question_id'])
            ->firstOrFail();

        // Save atau update answer
        $answer = ScreeningAnswer::updateOrCreate(
            [
                'screening_session_id' => $sessionId,
                'screening_question_id' => $validated['screening_question_id'],
            ],
            [
                'screening_option_id' => $validated['screening_option_id'],
            ]
        );

        return response()->json([
            'message' => 'Answer saved successfully',
            'data' => [
                'session_id' => $session->id,
                'question_id' => $answer->screening_question_id,
                'option_id' => $answer->screening_option_id,
                'option_value' => $option->value,
                'option_label' => $option->label,
                'saved_at' => $answer->updated_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Submit screening session
     * POST /api/screening/sessions/{sessionId}/submit
     */
    public function submitSession($sessionId)
    {
        $user = Auth::user();
        $session = ScreeningSession::with('package')
            ->where('id', $sessionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Cek apakah sudah submit
        if ($session->submitted_at) {
            return response()->json([
                'message' => 'Session already submitted',
                'data' => [
                    'submitted_at' => $session->submitted_at->toIso8601String(),
                ],
            ], 409);
        }

        $totalQuestions = $session->package->questions->count();
        $answeredCount = ScreeningAnswer::where('screening_session_id', $sessionId)->count();

        // Validasi semua soal sudah dijawab
        if ($answeredCount < $totalQuestions) {
            $answeredIds = ScreeningAnswer::where('screening_session_id', $sessionId)
                ->pluck('screening_question_id')
                ->toArray();

            $unansweredIds = $session->package->questions()
                ->whereNotIn('id', $answeredIds)
                ->pluck('id')
                ->toArray();

            return response()->json([
                'message' => 'All questions must be answered before submitting',
                'data' => [
                    'answered' => $answeredCount,
                    'total' => $totalQuestions,
                    'unanswered_question_ids' => $unansweredIds,
                ],
            ], 422);
        }

        // Submit session
        $session->update(['submitted_at' => now()]);

        return response()->json([
            'message' => 'Screening session submitted successfully',
            'data' => [
                'session_id' => $session->id,
                'submitted_at' => $session->submitted_at->toIso8601String(),
                'total_answered' => $answeredCount,
                'total_questions' => $totalQuestions,
                'completion_percentage' => 100,
            ],
        ]);
    }

    /**
     * Get screening result (after submit)
     * GET /api/screening/sessions/{sessionId}/result
     */
    public function getResult($sessionId)
    {
        $user = Auth::user();
        $session = ScreeningSession::with('package.dimensions')
            ->where('id', $sessionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Pastikan session sudah disubmit
        if (! $session->submitted_at) {
            return response()->json([
                'message' => 'Screening result not found or session not yet submitted',
            ], 404);
        }

        // Calculate scores
        $scores = $this->calculateScores($sessionId, $session->package);

        // Prepare response
        $scoresData = [];
        foreach ($scores as $code => $scoreData) {
            $scoresData[$code] = $scoreData;
        }

        // Calculate overall interpretation
        $totalScore = collect($scores)->sum('multiplied_score');
        $overallInterpretation = $this->getOverallInterpretation($session->package->code, $scores, $totalScore);

        return response()->json([
            'data' => [
                'session_id' => $session->id,
                'package_code' => $session->package->code,
                'submitted_at' => $session->submitted_at->toIso8601String(),
                'scores' => $scoresData,
                'overall' => $overallInterpretation,
            ],
        ]);
    }

    /**
     * Get user sessions history
     * GET /api/screening/sessions
     */
    public function userSessions(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 10);
        $packageCode = $request->get('package_code');
        $status = $request->get('status'); // submitted, active

        $query = $user->screeningSessions()
            ->with('package')
            ->orderByDesc('created_at');

        // Filter by package code
        if ($packageCode) {
            $query->whereHas('package', function ($q) use ($packageCode) {
                $q->where('code', $packageCode);
            });
        }

        // Filter by status
        if ($status === 'submitted') {
            $query->whereNotNull('submitted_at');
        } elseif ($status === 'active') {
            $query->whereNull('submitted_at');
        }

        $sessions = $query->paginate($perPage);

        $sessionsData = $sessions->items();
        $sessionsData = array_map(function ($session) {
            $duration = null;
            if ($session->submitted_at) {
                $duration = $session->started_at->diffInMinutes($session->submitted_at);
            }

            return [
                'id' => $session->id,
                'package_code' => $session->package->code,
                'package_name' => $session->package->name,
                'started_at' => $session->started_at->toIso8601String(),
                'submitted_at' => $session->submitted_at?->toIso8601String(),
                'duration_minutes' => $duration,
                'status' => $session->submitted_at ? 'submitted' : 'active',
            ];
        }, $sessionsData);

        return response()->json([
            'data' => $sessionsData,
            'meta' => [
                'total' => $sessions->total(),
                'page' => $sessions->currentPage(),
                'per_page' => $sessions->perPage(),
                'last_page' => $sessions->lastPage(),
            ],
        ]);
    }

    /**
     * Calculate scores per dimension
     */
    private function calculateScores($sessionId, $package)
    {
        $answers = ScreeningAnswer::where('screening_session_id', $sessionId)
            ->with(['option', 'question.dimensions'])
            ->get();

        $scores = [];

        foreach ($package->dimensions as $dimension) {
            $dimensionAnswers = $answers->filter(function ($answer) use ($dimension) {
                return $answer->question->dimensions->contains('id', $dimension->id);
            });

            $rawScore = $dimensionAnswers->sum(function ($answer) {
                return $answer->option->value;
            });

            $multipliedScore = $rawScore * $dimension->multiplier;

            // Get interpretation (ini bisa di-customize per package)
            $interpretation = $this->getInterpretation($dimension->code, $multipliedScore);

            $scores[$dimension->code] = [
                'dimension_name' => $dimension->name,
                'raw_score' => $rawScore,
                'multiplied_score' => $multipliedScore,
                'interpretation' => $interpretation,
            ];
        }

        return $scores;
    }

    /**
     * Get interpretation untuk score tertentu
     * Customize per package/dimension jika diperlukan
     */
    private function getInterpretation($dimensionCode, $score)
    {
        // DASS-21 interpretation
        if (in_array($dimensionCode, ['D', 'A', 'S'])) {
            // Based on DASS-21 severity rating
            if ($score <= 9) {
                return 'Normal';
            } elseif ($score <= 13) {
                return 'Mild';
            } elseif ($score <= 20) {
                return 'Moderate';
            } elseif ($score <= 27) {
                return 'Severe';
            } else {
                return 'Extremely Severe';
            }
        }

        return 'Normal';
    }

    /**
     * Get overall interpretation
     */
    private function getOverallInterpretation($packageCode, $scores, $totalScore)
    {
        if ($packageCode === 'DASS21') {
            // DASS-21 overall interpretation
            if ($totalScore <= 28) {
                $interpretation = 'Normal';
                $recommendation = 'Your mental health appears to be in good condition. Continue with regular self-care and healthy lifestyle.';
            } elseif ($totalScore <= 40) {
                $interpretation = 'Mild psychological distress';
                $recommendation = 'Monitor your mental health; consider self-care strategies and relaxation techniques.';
            } elseif ($totalScore <= 60) {
                $interpretation = 'Moderate psychological distress';
                $recommendation = 'Consider seeking support from a counselor or therapist; implement stress management.';
            } else {
                $interpretation = 'Severe psychological distress';
                $recommendation = 'Please seek professional help from a mental health provider as soon as possible.';
            }

            return [
                'total_score' => $totalScore,
                'interpretation' => $interpretation,
                'recommendation' => $recommendation,
            ];
        }

        // Default for other packages
        return [
            'total_score' => $totalScore,
            'interpretation' => 'Assessment completed',
            'recommendation' => 'Please consult with a mental health professional for detailed interpretation.',
        ];
    }
}
