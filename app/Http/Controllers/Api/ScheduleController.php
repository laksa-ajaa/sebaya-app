<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    // GET /api/schedules - return schedule notifications for students
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Schedule::with('teacher')->orderByDesc('scheduled_at');

        if ($user) {
            // for students, only return global (student_id null) or targeted schedules
            if ($user->role !== 'teacher') {
                $query->where(function ($q) use ($user) {
                    $q->whereNull('student_id')->orWhere('student_id', $user->id);
                });
            }
        }

        $schedules = $query->limit(50)->get()->map(function ($s) {
            $scheduledAt = null;
            try {
                if ($s->scheduled_at instanceof \DateTimeInterface) {
                    $dt = Carbon::instance($s->scheduled_at);
                    $scheduledAt = $dt->locale('id')->isoFormat('dddd, D MMM YYYY [Pukul] HH.mm');
                } elseif (is_string($s->scheduled_at) && $s->scheduled_at !== '') {
                    $dt = Carbon::parse($s->scheduled_at);
                    $scheduledAt = $dt->locale('id')->isoFormat('dddd, D MMM YYYY [Pukul] HH.mm');
                }
            } catch (\Exception $e) {
                // fallback to raw value or null
                $scheduledAt = is_string($s->scheduled_at) ? $s->scheduled_at : null;
            }

            return [
                'id' => $s->id,
                'teacher_name' => $s->teacher?->name,
                'scheduled_at' => $scheduledAt,
                'message' => $s->message,
            ];
        });

        return response()->json(['data' => $schedules]);
    }
}
