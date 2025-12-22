<?php

namespace App\Http\Controllers\Api;

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
            'habits.*.name' => ['required_with:habits', 'string'],
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
                    Habit::create([
                        'journal_id' => $journal->id,
                        'name' => $habitData['name'],
                        'description' => $habitData['description'] ?? null,
                        'is_completed_today' => $habitData['is_completed_today'] ?? false,
                        'streak' => 0,
                    ]);
                }
            }

            DB::commit();

            $journal->load(['todoItems', 'habits']);

            return response()->json([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'journal_entry' => $this->formatJournalEntry($journal),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create journal entry',
                'error' => $e->getMessage(),
            ], 500);
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
            ->with(['todoItems', 'habits'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => [
                'journal_entries' => $journals->map(function ($journal) {
                    return $this->formatJournalEntry($journal);
                }),
                'pagination' => [
                    'current_page' => $journals->currentPage(),
                    'per_page' => $journals->perPage(),
                    'total_items' => $journals->total(),
                    'total_pages' => $journals->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * Get Single Journal Entry
     */
    public function show($id)
    {
        $user = Auth::guard('api')->user();

        $journal = Journal::where('user_id', $user->id)
            ->with(['todoItems', 'habits'])
            ->find($id);

        if (!$journal) {
            return response()->json([
                'success' => false,
                'message' => 'Journal entry not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => [
                'journal_entry' => $this->formatJournalEntry($journal),
            ],
        ]);
    }

    /**
     * Update Journal Entry
     */
    public function update(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        $journal = Journal::where('user_id', $user->id)->find($id);

        if (!$journal) {
            return response()->json([
                'success' => false,
                'message' => 'Journal entry not found',
            ], 404);
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
            'habits.*.name' => ['required_with:habits', 'string'],
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
                            return response()->json([
                                'success' => false,
                                'message' => 'Todo item not found or does not belong to this journal',
                            ], 404);
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
                            return response()->json([
                                'success' => false,
                                'message' => 'Habit not found or does not belong to this journal',
                            ], 404);
                        }
                        $habit->update([
                            'name' => $habitData['name'],
                            'description' => $habitData['description'] ?? $habit->description,
                            'is_completed_today' => $habitData['is_completed_today'] ?? $habit->is_completed_today,
                        ]);
                        $existingHabitIds[] = $habit->id;
                    } else {
                        // Create new habit
                        $newHabit = Habit::create([
                            'journal_id' => $journal->id,
                            'name' => $habitData['name'],
                            'description' => $habitData['description'] ?? null,
                            'is_completed_today' => $habitData['is_completed_today'] ?? false,
                            'streak' => 0,
                        ]);
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
            $journal->load(['todoItems', 'habits']);

            return response()->json([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'journal_entry' => $this->formatJournalEntry($journal),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update journal entry',
                'error' => $e->getMessage(),
            ], 500);
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
                return [
                    'id' => $habit->id,
                    'name' => $habit->name,
                    'description' => $habit->description,
                    'is_completed_today' => $habit->is_completed_today,
                    'streak' => $habit->streak,
                ];
            })->toArray(),
            'created_at' => $journal->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i:s') . '+07:00',
            'updated_at' => $journal->updated_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i:s') . '+07:00',
        ];
    }
}
