<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\ClassModel;
use Carbon\Carbon;

class GuruScheduleController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $selectedClassId = $request->input('class_id');
        $selectedDate = $request->input('date');
        $selectedStatus = $request->input('status');

        $query = Schedule::where('teacher_id', $request->user()->id)
            ->with(['student.class', 'kelas']);

        // Filter by Search (Student Name or Class Name of group schedule)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })->orWhereHas('kelas', function($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filter by Class
        if ($selectedClassId) {
            $query->where(function($q) use ($selectedClassId) {
                $q->where('class_id', $selectedClassId)
                  ->orWhereHas('student.class', function($sq) use ($selectedClassId) {
                      $sq->where('classes.id', $selectedClassId);
                  });
            });
        }

        // Filter by Date
        if ($selectedDate) {
            $query->whereDate('scheduled_at', $selectedDate);
        }

        // Filter by Status
        if ($selectedStatus) {
            $query->where('status', $selectedStatus);
        }

        $schedules = $query->orderBy('status', 'desc') // upcoming first
            ->orderByDesc('scheduled_at')
            ->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('dashboard.guru.schedules_table', compact('schedules'))->render(),
                'pagination' => $schedules->appends($request->all())->links()->toHtml(),
                'info' => "Menampilkan " . ($schedules->firstItem() ?? 0) . "-" . ($schedules->lastItem() ?? 0) . " dari " . $schedules->total() . " Data"
            ]);
        }

        $classes = ClassModel::orderBy('name')->get();
            
        return view('dashboard.guru.schedules', compact('schedules', 'classes', 'search', 'selectedClassId', 'selectedDate', 'selectedStatus', 'perPage'));
    }

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

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'message' => 'nullable|string|max:2000',
        ]);

        $schedule = Schedule::findOrFail($id);
        
        // Ensure teacher owns schedule
        if ($schedule->teacher_id !== $request->user()->id) {
            abort(403);
        }

        $datetime = Carbon::parse($request->input('date') . ' ' . $request->input('time'));

        $schedule->update([
            'scheduled_at' => $datetime,
            'message' => $request->input('message'),
        ]);

        return redirect()->back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function finish(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        
        // Ensure teacher owns schedule
        if ($schedule->teacher_id !== $request->user()->id) {
            abort(403);
        }

        $schedule->update([
            'status' => 'finished'
        ]);

        return redirect()->back()->with('success', 'Pertemuan telah disudahi.');
    }
}
