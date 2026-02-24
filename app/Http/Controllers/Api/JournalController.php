<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\TodoItem;
use App\Models\Habit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JournalController extends Controller
{
    /**
     * Create Journal Entry
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:1', 'max:200'],
            'content' => [
                'required_if:type,TEXT',
                'nullable',
                'string',
                'max:10000'
            ],
            'type' => ['required', Rule::in(['TEXT', 'TODO_LIST', 'HABITS_TRACKER'])],
            'todo_items' => ['array', 'required_if:type,TODO_LIST'],
            'todo_items.*.text' => ['required_with:todo_items', 'string'],
            'todo_items.*.is_completed' => ['boolean'],
            'todo_items.*.reminder_time' => ['nullable', 'date'],
            'todo_items.*.reminder_label' => ['nullable', 'string'],
            'todo_items.*.order' => ['nullable', 'integer'],
            'habits' => ['array', 'required_if:type,HABITS_TRACKER'],
            'habits.*.habit_type_id' => ['nullable', 'integer', 'exists:habit_types,id'],
            'habits.*.name' => ['required_without:habits.*.habit_type_id', 'nullable', 'string'],
            'habits.*.description' => ['nullable', 'string'],
            'habits.*.is_completed_today' => ['boolean'],
        ]);

        $user = Auth::guard('api')->user();

        DB::beginTransaction();
        try {
            $journal = Journal::create([
                'user_id' => $user->id,
                'title' => $validated['title'],
                'content' => $validated['content'] ?? '',
                'type' => $validated['type'],
                'date' => now()->toDateString(),
            ]);

            // Create todo items if type is TODO_LIST
            if ($validated['type'] === 'TODO_LIST' && isset($validated['todo_items'])) {
                foreach ($validated['todo_items'] as $index => $todoData) {
                    TodoItem::create([
                        'journal_id' => $journal->id,
                        'text' => $todoData['text'],
                        'is_completed' => $todoData['is_completed'] ?? false,
                        'reminder_time' => $todoData['reminder_time'] ?? null,
                        'reminder_label' => $todoData['reminder_label'] ?? null,
                        'order' => $todoData['order'] ?? $index,
                    ]);
                }
            }

            // Create habits if type is HABITS_TRACKER
            if ($validated['type'] === 'HABITS_TRACKER' && isset($validated['habits'])) {
                foreach ($validated['habits'] as $habitData) {
                    $isCompletedToday = $habitData['is_completed_today'] ?? false;
                    $habitTypeId = $habitData['habit_type_id'] ?? null;

                    // Jika pakai predefined habit type, gunakan nama & deskripsi dari sana
                    $name = $habitData['name'] ?? null;
                    $description = $habitData['description'] ?? null;
                    if ($habitTypeId) {
                        $habitType = \App\Models\HabitType::find($habitTypeId);
                        if ($habitType) {
                            $name = $name ?? $habitType->name;
                            $description = $description ?? $habitType->description;
                        }
                    }

                    $newHabit = Habit::create([
                        'journal_id' => $journal->id,
                        'habit_type_id' => $habitTypeId,
                        'name' => $name,
                        'description' => $description,
                        'streak' => $isCompletedToday ? 1 : 0,
                    ]);

                    if ($isCompletedToday) {
                        \App\Models\HabitLog::create([
                            'habit_id' => $newHabit->id,
                            'date' => now()->toDateString(),
                            'is_completed' => true
                        ]);
                    }
                }
            }

            DB::commit();

            $journal->load(['todoItems', 'habits.logs', 'habits.habitType']);

            return ApiResponse::success([
                'journal_entry' => $this->formatJournalEntry($journal),
            ], 'Entri jurnal berhasil dibuat.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Gagal membuat entri jurnal.', $e->getMessage(), 500);
        }
    }

    /**
     * Get All Journal Entries
     */
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();
        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);

        $journals = Journal::where('user_id', $user->id)
            ->with(['todoItems', 'habits.logs', 'habits.habitType'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return ApiResponse::success([
            'journal_entries' => $journals->map(function ($journal) {
                return $this->formatJournalEntry($journal);
            }),
            'pagination' => [
                'current_page' => $journals->currentPage(),
                'per_page' => $journals->perPage(),
                'total_items' => $journals->total(),
                'total_pages' => $journals->lastPage(),
            ],
        ], 'Daftar entri jurnal berhasil diambil.');
    }

    /**
     * Get Single Journal Entry
     */
    public function show($id)
    {
        $user = Auth::guard('api')->user();

        $journal = Journal::where('user_id', $user->id)
            ->with(['todoItems', 'habits.logs', 'habits.habitType'])
            ->find($id);

        if (!$journal) {
            return ApiResponse::error('Jurnal tidak ditemukan.', null, 404);
        }

        return ApiResponse::success([
            'journal_entry' => $this->formatJournalEntry($journal),
        ], 'Detail entri jurnal berhasil diambil.');
    }

    /**
     * Update Journal Entry
     */
    public function update(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        $journal = Journal::where('user_id', $user->id)->find($id);

        if (!$journal) {
            return ApiResponse::error('Entri jurnal tidak ditemukan.', null, 404);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'min:1', 'max:200'],
            'content' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'todo_items' => ['sometimes', 'array'],
            'todo_items.*.id' => ['sometimes', 'integer', 'exists:todo_items,id'],
            'todo_items.*.text' => ['required_with:todo_items', 'string'],
            'todo_items.*.is_completed' => ['boolean'],
            'todo_items.*.reminder_time' => ['nullable', 'date'],
            'todo_items.*.reminder_label' => ['nullable', 'string'],
            'todo_items.*.order' => ['nullable', 'integer'],
            'habits' => ['sometimes', 'array'],
            'habits.*.id' => ['sometimes', 'integer', 'exists:habits,id'],
            'habits.*.habit_type_id' => ['nullable', 'integer', 'exists:habit_types,id'],
            'habits.*.name' => ['nullable', 'string'],
            'habits.*.description' => ['nullable', 'string'],
            'habits.*.is_completed_today' => ['boolean'],
        ]);

        DB::beginTransaction();
        try {
            // Update journal basic fields
            if (isset($validated['title'])) {
                $journal->title = $validated['title'];
            }
            if (isset($validated['content'])) {
                $journal->content = $validated['content'];
            }
            $journal->save();

            // Update todo items
            if (isset($validated['todo_items'])) {
                $existingTodoIds = [];
                foreach ($validated['todo_items'] as $todoData) {
                    if (isset($todoData['id'])) {
                        // Update existing todo item
                        $todoItem = TodoItem::where('journal_id', $journal->id)
                            ->find($todoData['id']);
                        if (!$todoItem) {
                            DB::rollBack();
                            return ApiResponse::error('Item todo tidak ditemukan atau tidak termasuk dalam jurnal ini.', null, 404);
                        }
                        $todoItem->update([
                            'text' => $todoData['text'],
                            'is_completed' => $todoData['is_completed'] ?? $todoItem->is_completed,
                            'reminder_time' => $todoData['reminder_time'] ?? null,
                            'reminder_label' => $todoData['reminder_label'] ?? null,
                            'order' => $todoData['order'] ?? $todoItem->order,
                        ]);
                        $existingTodoIds[] = $todoItem->id;
                    } else {
                        // Create new todo item
                        $newTodo = TodoItem::create([
                            'journal_id' => $journal->id,
                            'text' => $todoData['text'],
                            'is_completed' => $todoData['is_completed'] ?? false,
                            'reminder_time' => $todoData['reminder_time'] ?? null,
                            'reminder_label' => $todoData['reminder_label'] ?? null,
                            'order' => $todoData['order'] ?? 0,
                        ]);
                        $existingTodoIds[] = $newTodo->id;
                    }
                }
                // Delete todo items that are not in the request
                TodoItem::where('journal_id', $journal->id)
                    ->whereNotIn('id', $existingTodoIds)
                    ->delete();
            }

            // Update habits
            if (isset($validated['habits'])) {
                $existingHabitIds = [];
                foreach ($validated['habits'] as $habitData) {
                    if (isset($habitData['id'])) {
                        // Update existing habit
                        $habit = Habit::where('journal_id', $journal->id)
                            ->find($habitData['id']);
                        if (!$habit) {
                            DB::rollBack();
                            return ApiResponse::error('Kebiasaan tidak ditemukan atau tidak termasuk dalam jurnal ini.', null, 404);
                        }
                        $habit->update([
                            'name' => $habitData['name'],
                            'description' => $habitData['description'] ?? $habit->description,
                        ]);

                        if (isset($habitData['is_completed_today'])) {
                            \App\Models\HabitLog::updateOrCreate(
                                ['habit_id' => $habit->id, 'date' => now()->toDateString()],
                                ['is_completed' => $habitData['is_completed_today']]
                            );
                        }
                        $existingHabitIds[] = $habit->id;
                    } else {
                        // Create new habit
                        $isCompletedToday = $habitData['is_completed_today'] ?? false;
                        $habitTypeId = $habitData['habit_type_id'] ?? null;
                        $name = $habitData['name'] ?? null;
                        $description = $habitData['description'] ?? null;
                        if ($habitTypeId) {
                            $habitType = \App\Models\HabitType::find($habitTypeId);
                            if ($habitType) {
                                $name = $name ?? $habitType->name;
                                $description = $description ?? $habitType->description;
                            }
                        }
                        $newHabit = Habit::create([
                            'journal_id' => $journal->id,
                            'habit_type_id' => $habitTypeId,
                            'name' => $name,
                            'description' => $description,
                            'streak' => $isCompletedToday ? 1 : 0,
                        ]);

                        if ($isCompletedToday) {
                            \App\Models\HabitLog::create([
                                'habit_id' => $newHabit->id,
                                'date' => now()->toDateString(),
                                'is_completed' => true
                            ]);
                        }

                        $existingHabitIds[] = $newHabit->id;
                    }
                }
                // Delete habits that are not in the request
                Habit::where('journal_id', $journal->id)
                    ->whereNotIn('id', $existingHabitIds)
                    ->delete();
            }

            DB::commit();

            $journal->refresh();
            $journal->load(['todoItems', 'habits.logs', 'habits.habitType']);

            return ApiResponse::success([
                'journal_entry' => $this->formatJournalEntry($journal),
            ], 'Entri jurnal berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Gagal memperbarui entri jurnal.', $e->getMessage(), 500);
        }
    }

    /**
     * Delete Journal Entry
     */
    public function destroy($id)
    {
        $user = Auth::guard('api')->user();

        $journal = Journal::where('user_id', $user->id)->find($id);

        if (!$journal) {
            return ApiResponse::error('Entri jurnal tidak ditemukan.', null, 404);
        }

        DB::beginTransaction();
        try {
            $journal->todoItems()->delete();
            $journal->habits()->delete();

            $journal->delete();

            DB::commit();

            return ApiResponse::success(null, 'Jurnal berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Gagal menghapus jurnal.', $e->getMessage(), 500);
        }
    }

    /**
     * Format journal entry for response
     */
    private function formatJournalEntry(Journal $journal): array
    {
        return [
            'id' => $journal->id,
            'title' => $journal->title,
            'content' => $journal->content ?? '',
            'type' => $journal->type,
            'todo_items' => $journal->todoItems->map(function ($todo) {
                return [
                    'id' => $todo->id,
                    'text' => $todo->text,
                    'is_completed' => $todo->is_completed,
                    'reminder_time' => $todo->reminder_time ? $todo->reminder_time->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i:s') . '+07:00' : null,
                    'reminder_label' => $todo->reminder_label,
                    'order' => $todo->order,
                ];
            })->toArray(),
            'habits' => $journal->habits->map(function ($habit) {
                // Cek status khusus hari ini dari relasi logs
                $todayLog = $habit->logs ? $habit->logs->firstWhere('date', now()->toDateString()) : null;
                $isCompletedToday = $todayLog ? $todayLog->is_completed : false;

                return [
                    'id' => $habit->id,
                    'habit_type' => $habit->habitType ? [
                        'id' => $habit->habitType->id,
                        'name' => $habit->habitType->name,
                    ] : null,
                    'name' => $habit->name,
                    'description' => $habit->description,
                    'is_completed_today' => $isCompletedToday,
                    'streak' => $habit->streak,
                    'logs' => $habit->logs ? $habit->logs->map(function ($log) {
                        return [
                            'date' => $log->date->toDateString(),
                            'is_completed' => $log->is_completed,
                        ];
                    })->toArray() : [],
                ];
            })->toArray(),
            'created_at' => $journal->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i:s') . '+07:00',
            'updated_at' => $journal->updated_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i:s') . '+07:00',
        ];
    }

    /**
     * Check-in / Toggle State of a Habit
     */
    public function checkInHabit(Request $request, $habitId)
    {
        $user = Auth::guard('api')->user();

        // Validasi payload
        $validated = $request->validate([
            'is_completed' => ['required', 'boolean'],
            'date' => ['nullable', 'date'],
        ]);

        $checkDate = $validated['date'] ?? now()->toDateString();
        $isToday = $checkDate === now()->toDateString();

        // Cari habit yang berada di dalam jurnal milik user ini
        $habit = Habit::whereHas('journal', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->find($habitId);

        if (!$habit) {
            return ApiResponse::error('Habit tidak ditemukan atau tidak memiliki akses.', null, 404);
        }

        // Update atau buat log untuk tanggal tertentu
        $log = \App\Models\HabitLog::updateOrCreate(
            ['habit_id' => $habit->id, 'date' => $checkDate],
            ['is_completed' => $validated['is_completed']]
        );

        // Hitung ulang streak secara dinamis
        $streak = 0;
        $currentDate = now();
        $checkingToday = true;

        while (true) {
            $dateStr = $currentDate->toDateString();
            $dailyLog = \App\Models\HabitLog::where('habit_id', $habit->id)
                ->where('date', $dateStr)
                ->first();

            if ($dailyLog && $dailyLog->is_completed) {
                $streak++;
                $currentDate->subDay();
                $checkingToday = false;
            } else {
                // Jika hari ini belum check-in, streak tidak mereset (kita cek hari kemarin)
                if ($checkingToday) {
                    $currentDate->subDay();
                    $checkingToday = false;
                    continue;
                }
                break;
            }
        }

        $habit->streak = $streak;
        $habit->save();

        return ApiResponse::success([
            'habit' => [
                'id' => $habit->id,
                'name' => $habit->name,
                'is_completed_today' => $isToday ? $log->is_completed : ($habit->logs()->where('date', now()->toDateString())->first()?->is_completed ?? false),
                'streak' => $habit->streak,
                'check_date' => $checkDate,
                'is_completed' => $log->is_completed,
            ]
        ], 'Habit check-in log berhasil diperbarui.');
    }
}
