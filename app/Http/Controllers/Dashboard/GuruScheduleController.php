<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use Carbon\Carbon;

class GuruScheduleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'message' => 'nullable|string|max:2000',
            'class_id' => 'nullable|integer',
            'student_id' => 'nullable|integer',
        ]);

        $datetime = Carbon::parse($request->input('date') . ' ' . $request->input('time'));

        $schedule = Schedule::create([
            'teacher_id' => $request->user()->id,
            'student_id' => $request->input('student_id'),
            'class_id' => $request->input('class_id'),
            'scheduled_at' => $datetime,
            'message' => $request->input('message'),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $schedule]);
        }

        return redirect()->back()->with('success', 'Jadwal berhasil disimpan.');
    }
}
