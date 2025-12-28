<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GuruDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user?->role === 'teacher', 403);
        return view('dashboard.guru.index', [
            'teacher_level' => $user->teacher_level
        ]);
    }

    public function screening()
    {
        abort_unless(Auth::user()?->role === 'teacher', 403);
        return view('dashboard.guru.screening');
    }

    public function siswa()
    {
        abort_unless(Auth::user()?->role === 'teacher', 403);
        $teacher = Auth::user();

        // Kelas yang diajar oleh guru ini
        $classes = ClassModel::query()
            ->select('classes.*')
            ->join('class_teacher', 'class_teacher.class_id', '=', 'classes.id')
            ->where('class_teacher.teacher_id', $teacher->id)
            ->get();

        $classIds = $classes->pluck('id')->all();
        $classCodes = $classes->pluck('code')->filter()->all();

        // Siswa yang sudah terdaftar di kelas (pivot class_students)
        $enrolled = DB::table('class_students')
            ->join('users', 'users.id', '=', 'class_students.student_id')
            ->join('classes', 'classes.id', '=', 'class_students.class_id')
            ->whereIn('class_students.class_id', $classIds)
            ->where('users.role', 'user')
            ->select([
                'users.id as user_id',
                'users.name',
                'users.username',
                'users.whatsapp_number',
                'classes.name as class_name',
                'classes.code as class_code',
                'class_students.created_at as reference_at',
            ])
            ->get()
            ->map(function ($row) {
                return [
                    'user_id' => $row->user_id,
                    'name' => $row->name,
                    'username' => $row->username,
                    'whatsapp_number' => $row->whatsapp_number,
                    'class_name' => $row->class_name,
                    'class_code' => $row->class_code,
                    'reference_at' => $row->reference_at,
                    'status' => 'Terdaftar',
                ];
            });

        // Siswa yang memasukkan class_code milik guru namun belum diverifikasi/ditambahkan ke pivot
        $pending = DB::table('users')
            ->leftJoin('class_students', function ($join) use ($classIds) {
                $join->on('class_students.student_id', '=', 'users.id')
                    ->whereIn('class_students.class_id', $classIds);
            })
            ->join('classes', 'classes.code', '=', 'users.class_code')
            ->join('class_teacher', 'class_teacher.class_id', '=', 'classes.id')
            ->where('class_teacher.teacher_id', $teacher->id)
            ->whereNull('class_students.id')
            ->where('users.role', 'user')
            ->select([
                'users.id as user_id',
                'users.name',
                'users.username',
                'users.whatsapp_number',
                'classes.name as class_name',
                'classes.code as class_code',
                'users.created_at as reference_at',
            ])
            ->get()
            ->map(function ($row) {
                return [
                    'user_id' => $row->user_id,
                    'name' => $row->name,
                    'username' => $row->username,
                    'whatsapp_number' => $row->whatsapp_number,
                    'class_name' => $row->class_name,
                    'class_code' => $row->class_code,
                    'reference_at' => $row->reference_at,
                    'status' => 'Perlu Verifikasi',
                ];
            });

        // Gabungkan keduanya menjadi satu list
        $students = $enrolled->merge($pending)
            ->sortBy([['status', 'desc'], ['name', 'asc']])
            ->values();

        return view('dashboard.guru.siswa', [
            'students' => $students,
            'classes' => $classes,
        ]);
    }

    public function laporan()
    {
        abort_unless(Auth::user()?->role === 'teacher', 403);
        return view('dashboard.guru.laporan');
    }
}
