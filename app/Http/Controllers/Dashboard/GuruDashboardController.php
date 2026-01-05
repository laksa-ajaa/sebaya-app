<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\MoodCheck;
use App\Models\School;
use App\Models\ScreeningAnswer;
use App\Models\ScreeningDimension;
use App\Models\ScreeningPackage;
use App\Models\ScreeningSession;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GuruDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user?->role === 'teacher', 403);

        // Get student IDs based on teacher level
        $studentIds = $this->getStudentIds($user);

        // Default date range (this week)
        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        // Get chart data
        $chartData = $this->getChartData($studentIds, $startDate, $endDate);

        return view('dashboard.guru.index', array_merge([
            'teacher_level' => $user->teacher_level,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ], $chartData));
    }

    /**
     * Get student IDs based on teacher level (admin = school, kelas = classes)
     */
    private function getStudentIds($teacher)
    {
        if ($teacher->teacher_level === 'admin') {
            // Get all students from teacher's school
            $schoolId = DB::table('school_admins')
                ->where('user_id', $teacher->id)
                ->value('school_id');

            if (!$schoolId) return collect();

            $classIds = ClassModel::where('school_id', $schoolId)->pluck('id');
            return DB::table('class_students')
                ->whereIn('class_id', $classIds)
                ->pluck('student_id');
        } else {
            // Get students from teacher's classes
            $classIds = DB::table('class_teacher')
                ->where('teacher_id', $teacher->id)
                ->pluck('class_id');

            return DB::table('class_students')
                ->whereIn('class_id', $classIds)
                ->pluck('student_id');
        }
    }

    /**
     * Get chart data for given student IDs and date range
     */
    private function getChartData($studentIds, $startDate, $endDate)
    {
        $period = CarbonPeriod::create($startDate, $endDate);

        $dailyMoodChecks = [];
        $dailyMoodStacked = [];
        $chartDateCategories = [];

        foreach ($period as $date) {
            $label = $date->format('d M');
            $chartDateCategories[] = $label;
            $dailyMoodChecks[$label] = 0;
            $dailyMoodStacked[$label] = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        }

        if ($studentIds->isEmpty()) {
            return [
                'moodChartData' => [0, 0, 0, 0, 0],
                'dailyMoodChecks' => $dailyMoodChecks,
                'dailyMoodStacked' => $dailyMoodStacked,
                'chartDateCategories' => $chartDateCategories,
                'totalStudents' => 0,
            ];
        }

        // Mood distribution (donut)
        $moodDistribution = MoodCheck::whereIn('user_id', $studentIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->select('mood_level', DB::raw('count(*) as total'))
            ->groupBy('mood_level')
            ->pluck('total', 'mood_level')
            ->toArray();

        $moodChartData = [
            $moodDistribution[5] ?? 0,
            $moodDistribution[4] ?? 0,
            $moodDistribution[3] ?? 0,
            $moodDistribution[2] ?? 0,
            $moodDistribution[1] ?? 0,
        ];

        // Daily totals
        $moodChecksInRange = MoodCheck::whereIn('user_id', $studentIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->select('date', DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->get();

        foreach ($moodChecksInRange as $check) {
            $label = Carbon::parse($check->date)->format('d M');
            if (isset($dailyMoodChecks[$label])) {
                $dailyMoodChecks[$label] += $check->count;
            }
        }

        // Stacked by level
        $moodChecksByLevel = MoodCheck::whereIn('user_id', $studentIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->select('date', 'mood_level', DB::raw('COUNT(*) as count'))
            ->groupBy('date', 'mood_level')
            ->get();

        foreach ($moodChecksByLevel as $row) {
            $label = Carbon::parse($row->date)->format('d M');
            if (isset($dailyMoodStacked[$label])) {
                $level = (int) $row->mood_level;
                if (isset($dailyMoodStacked[$label][$level])) {
                    $dailyMoodStacked[$label][$level] += $row->count;
                }
            }
        }

        // Calculate daily service statistics
        $dailyStats = $this->getDailyServiceStats($studentIds);

        return [
            'moodChartData' => $moodChartData,
            'dailyMoodChecks' => $dailyMoodChecks,
            'dailyMoodStacked' => $dailyMoodStacked,
            'chartDateCategories' => $chartDateCategories,
            'totalStudents' => $studentIds->unique()->count(),
            'activeScreenings' => $dailyStats['activeScreenings'],
            'needsAttention' => $dailyStats['needsAttention'],
            'todayMoodChecks' => $dailyStats['todayMoodChecks'],
            'totalSchedules' => $dailyStats['totalSchedules'],
        ];
    }

    /**
     * Get daily service statistics
     */
    private function getDailyServiceStats($studentIds)
    {
        if ($studentIds->isEmpty()) {
            return [
                'activeScreenings' => 0,
                'needsAttention' => 0,
                'todayMoodChecks' => 0,
                'totalSchedules' => 0,
            ];
        }

        // 1. Active Screenings (not submitted yet)
        $activeScreenings = ScreeningSession::whereIn('user_id', $studentIds)
            ->count();

        // 2. Needs Attention (students with high-risk screening results)
        $needsAttention = ScreeningSession::whereIn('user_id', $studentIds)
            ->whereNotNull('submitted_at')
            ->get()
            ->filter(function ($session) {
                $overall = $this->calculateOverallScreening($session);
                // Level 1 or 2 indicates need for attention (severe/very severe)
                return in_array($overall['level'], [1, 2]);
            })
            ->unique('user_id')
            ->count();

        // 3. Today's Mood Check-ins
        $todayMoodChecks = MoodCheck::whereIn('user_id', $studentIds)
            ->whereDate('date', today())
            ->count();

        // 4. Total Schedules for this teacher
        $teacher = Auth::user();
        $totalSchedules = DB::table('schedules')
            ->where('teacher_id', $teacher->id)
            ->count();

        return [
            'activeScreenings' => $activeScreenings,
            'needsAttention' => $needsAttention,
            'todayMoodChecks' => $todayMoodChecks,
            'totalSchedules' => $totalSchedules,
        ];
    }

    /**
     * AJAX endpoint for chart data
     */
    public function chartData(Request $request)
    {
        $user = Auth::user();
        abort_unless($user?->role === 'teacher', 403);

        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : now()->startOfWeek();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : now()->endOfWeek();

        if ($endDate->lessThan($startDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $studentIds = $this->getStudentIds($user);
        $chartData = $this->getChartData($studentIds, $startDate, $endDate);

        return response()->json($chartData);
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
                'classes.id as class_id',
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
                    'class_id' => $row->class_id,
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
                'classes.id as class_id',
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
                    'class_id' => $row->class_id,
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

    public function kelasVerifyStudent($id, $user_id)
    {
        $teacher = Auth::user();
        abort_unless($teacher?->role === 'teacher', 403);

        $class = ClassModel::findOrFail($id);

        // Pastikan guru ini mengajar kelas tersebut atau adalah admin sekolah
        $teaches = DB::table('class_teacher')
            ->where('class_id', $class->id)
            ->where('teacher_id', $teacher->id)
            ->exists();

        $isSchoolAdmin = DB::table('school_admins')
            ->where('school_id', $class->school_id)
            ->where('user_id', $teacher->id)
            ->exists();

        abort_unless($teaches || $isSchoolAdmin, 403);

        $user = User::findOrFail($user_id);
        if ($user->role !== 'user') {
            return redirect()->back()->with('error', 'Hanya siswa yang dapat diverifikasi ke kelas');
        }

        if (! $class->students()->where('users.id', $user->id)->exists()) {
            $class->students()->attach($user->id, ['start_date' => now()]);
        }

        return redirect()->back()->with('success', 'Siswa berhasil diverifikasi dan ditambahkan ke kelas');
    }

    public function kelasRejectStudent($id, $user_id)
    {
        $teacher = Auth::user();
        abort_unless($teacher?->role === 'teacher', 403);

        $class = ClassModel::findOrFail($id);

        // Pastikan guru ini mengajar kelas tersebut atau adalah admin sekolah
        $teaches = DB::table('class_teacher')
            ->where('class_id', $class->id)
            ->where('teacher_id', $teacher->id)
            ->exists();

        $isSchoolAdmin = DB::table('school_admins')
            ->where('school_id', $class->school_id)
            ->where('user_id', $teacher->id)
            ->exists();

        abort_unless($teaches || $isSchoolAdmin, 403);

        $user = User::findOrFail($user_id);
        if ($user->role !== 'user') {
            return redirect()->back()->with('error', 'Hanya siswa yang dapat ditolak');
        }

        // Hapus pengajuan (class_code) agar tidak tampil sebagai pending lagi
        $user->class_code = null;
        $user->save();

        return redirect()->back()->with('success', 'Permintaan siswa ditolak. Kode kelas dihapus.');
    }

    public function sekolah()
    {
        $user = Auth::user();
        abort_unless($user?->role === 'teacher' && $user->teacher_level === 'admin', 403);

        // Ambil sekolah yang di-admin oleh guru ini
        $schools = School::query()
            ->join('school_admins', 'school_admins.school_id', '=', 'schools.id')
            ->where('school_admins.user_id', $user->id)
            ->select('schools.*')
            ->get();

        return view('dashboard.guru.sekolah', compact('schools'));
    }

    public function sekolahUpdate(Request $request, $id)
    {
        $user = Auth::user();
        abort_unless($user?->role === 'teacher' && $user->teacher_level === 'admin', 403);

        $school = School::findOrFail($id);

        // Pastikan guru ini adalah admin sekolah tersebut
        $isAdmin = DB::table('school_admins')
            ->where('school_id', $school->id)
            ->where('user_id', $user->id)
            ->exists();

        abort_unless($isAdmin, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'npsn' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        $school->update($validated);

        return redirect()->route('guru.sekolah')->with('success', 'Data sekolah berhasil diupdate');
    }

    public function kelas()
    {
        $user = Auth::user();
        abort_unless($user?->role === 'teacher', 403);
        abort_unless(in_array($user->teacher_level, ['admin', 'kelas']), 403);

        $search = request()->get('search', '');
        $perPage = request()->get('per_page', 15);

        if ($user->teacher_level === 'admin') {
            // Admin: ambil semua kelas dari sekolah yang di-admin
            $query = ClassModel::query()
                ->join('school_admins', 'school_admins.school_id', '=', 'classes.school_id')
                ->where('school_admins.user_id', $user->id)
                ->select('classes.*')
                ->with(['school', 'teachers']);
        } else {
            // Guru kelas: hanya kelas yang diajar
            $query = ClassModel::query()
                ->join('class_teacher', 'class_teacher.class_id', '=', 'classes.id')
                ->where('class_teacher.teacher_id', $user->id)
                ->select('classes.*')
                ->with(['school', 'teachers']);
        }

        if ($search) {
            $query->where('classes.name', 'like', "%{$search}%");
        }

        $classes = $query->orderBy('classes.name')->paginate($perPage)->withQueryString();

        // Ambil sekolah untuk dropdown (jika admin) beserta guru per sekolah
        $schools = collect();
        $teachersBySchool = collect();
        if ($user->teacher_level === 'admin') {
            $schools = School::query()
                ->with('teachers')
                ->join('school_admins', 'school_admins.school_id', '=', 'schools.id')
                ->where('school_admins.user_id', $user->id)
                ->select('schools.*')
                ->get();

            $teachersBySchool = $schools->mapWithKeys(function ($school) {
                return [$school->id => $school->teachers->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'name' => $t->name,
                    ];
                })->values()];
            });
        }

        return view('dashboard.guru.kelas', compact('classes', 'schools', 'teachersBySchool', 'search', 'perPage'));
    }

    public function kelasStore(Request $request)
    {
        $user = Auth::user();
        abort_unless($user?->role === 'teacher' && $user->teacher_level === 'admin', 403);

        $validated = $request->validate([
            'school_id' => 'required|integer|exists:schools,id',
            'name' => 'required|string|max:255',
            'grade' => 'nullable|string|max:10',
            'teacher_option' => 'required|in:none,existing,new',
            'teacher_id' => [
                'nullable',
                'required_if:teacher_option,existing',
                'integer',
                Rule::exists('school_teachers', 'teacher_id')->where(fn($q) => $q->where('school_id', $request->school_id)),
            ],
            'new_teacher_name' => 'nullable|required_if:teacher_option,new|string|max:255',
            'new_teacher_email' => 'nullable|required_if:teacher_option,new|email|unique:users,email',
            'new_teacher_password' => 'nullable|required_if:teacher_option,new|string|min:6',
        ]);

        // Pastikan guru ini adalah admin sekolah tersebut
        $isAdmin = DB::table('school_admins')
            ->where('school_id', $validated['school_id'])
            ->where('user_id', $user->id)
            ->exists();

        abort_unless($isAdmin, 403);

        DB::transaction(function () use ($validated) {
            $teacherId = null;

            if ($validated['teacher_option'] === 'existing') {
                $teacherId = $validated['teacher_id'];
            } elseif ($validated['teacher_option'] === 'new') {
                $newTeacher = User::create([
                    'name' => $validated['new_teacher_name'],
                    'email' => $validated['new_teacher_email'],
                    'password' => Hash::make($validated['new_teacher_password']),
                    'role' => 'teacher',
                    'account_status' => 'active',
                    'otp_verified_at' => now(),
                    'username' => strtolower(str_replace(' ', '_', $validated['new_teacher_name'])),
                    'whatsapp_number' => '-',
                    'teacher_level' => 'kelas',
                ]);

                // Pastikan guru baru terdaftar di sekolah
                DB::table('school_teachers')->updateOrInsert([
                    'school_id' => $validated['school_id'],
                    'teacher_id' => $newTeacher->id,
                ], []);

                $teacherId = $newTeacher->id;
            }

            // Generate unique class code
            $classPrefix = 'CLS';
            do {
                $random = Str::upper(Str::random(6));
                $generatedCode = $classPrefix . '-' . $random;
            } while (ClassModel::where('code', $generatedCode)->exists());

            $class = ClassModel::create([
                'school_id' => $validated['school_id'],
                'name' => $validated['name'],
                'grade' => $validated['grade'] ?? null,
                'code' => $generatedCode,
            ]);

            // Set guru pada pivot class_teacher
            if ($teacherId) {
                $class->teachers()->sync([$teacherId]);
            }
        });

        return redirect()->route('guru.kelas')->with('success', 'Kelas berhasil ditambahkan');
    }

    public function kelasUpdate(Request $request, $id)
    {
        $user = Auth::user();
        abort_unless($user?->role === 'teacher' && $user->teacher_level === 'admin', 403);

        $class = ClassModel::findOrFail($id);

        // Hanya admin sekolah yang dapat mengedit kelas
        $hasAccess = DB::table('school_admins')
            ->where('school_id', $class->school_id)
            ->where('user_id', $user->id)
            ->exists();

        abort_unless($hasAccess, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade' => 'nullable|string|max:10',
            'teacher_option' => 'required|in:none,existing,new',
            'teacher_id' => [
                'nullable',
                'required_if:teacher_option,existing',
                'integer',
                Rule::exists('school_teachers', 'teacher_id')->where(fn($q) => $q->where('school_id', $class->school_id)),
            ],
            'new_teacher_name' => 'nullable|required_if:teacher_option,new|string|max:255',
            'new_teacher_email' => 'nullable|required_if:teacher_option,new|email|unique:users,email',
            'new_teacher_password' => 'nullable|required_if:teacher_option,new|string|min:6',
        ]);

        DB::transaction(function () use ($validated, $class) {
            $teacherId = null;

            if ($validated['teacher_option'] === 'existing') {
                $teacherId = $validated['teacher_id'];
            } elseif ($validated['teacher_option'] === 'new') {
                $newTeacher = User::create([
                    'name' => $validated['new_teacher_name'],
                    'email' => $validated['new_teacher_email'],
                    'password' => Hash::make($validated['new_teacher_password']),
                    'role' => 'teacher',
                    'account_status' => 'active',
                    'otp_verified_at' => now(),
                    'username' => strtolower(str_replace(' ', '_', $validated['new_teacher_name'])),
                    'whatsapp_number' => '-',
                    'teacher_level' => 'kelas',
                ]);

                DB::table('school_teachers')->updateOrInsert([
                    'school_id' => $class->school_id,
                    'teacher_id' => $newTeacher->id,
                ], []);

                $teacherId = $newTeacher->id;
            }

            $class->update([
                'name' => $validated['name'],
                'grade' => $validated['grade'] ?? null,
            ]);

            if ($teacherId) {
                $class->teachers()->sync([$teacherId]);
            } else {
                $class->teachers()->sync([]);
            }
        });

        return redirect()->route('guru.kelas')->with('success', 'Kelas berhasil diupdate');
    }

    public function kelasDelete($id)
    {
        $user = Auth::user();
        abort_unless($user?->role === 'teacher' && $user->teacher_level === 'admin', 403);

        $class = ClassModel::findOrFail($id);

        // Pastikan guru ini adalah admin sekolah tersebut
        $isAdmin = DB::table('school_admins')
            ->where('school_id', $class->school_id)
            ->where('user_id', $user->id)
            ->exists();

        abort_unless($isAdmin, 403);

        $class->delete();

        return redirect()->route('guru.kelas')->with('success', 'Kelas berhasil dihapus');
    }

    public function kelasDetail($id)
    {
        $user = Auth::user();
        abort_unless($user?->role === 'teacher', 403);

        $class = ClassModel::findOrFail($id);

        // Cek akses: admin sekolah atau guru kelas tersebut
        if ($user->teacher_level === 'admin') {
            $hasAccess = DB::table('school_admins')
                ->where('school_id', $class->school_id)
                ->where('user_id', $user->id)
                ->exists();
        } else {
            $hasAccess = DB::table('class_teacher')
                ->where('class_id', $class->id)
                ->where('teacher_id', $user->id)
                ->exists();
        }

        abort_unless($hasAccess, 403);

        // Enrolled students
        $enrolled = $class->students()->get()->map(function ($student) use ($class) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'username' => $student->username,
                'class_id' => $class->id,
                'status' => 'Terdaftar',
            ];
        })->values();

        // Pending requests: users who set class_code but not yet in pivot
        $pending = collect();
        if ($class->code) {
            $pending = \App\Models\User::where('role', 'user')
                ->where('class_code', $class->code)
                ->whereNotIn('id', $enrolled->pluck('id'))
                ->orderBy('name')
                ->get()
                ->map(function ($u) use ($class) {
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'username' => $u->username,
                        'class_id' => $class->id,
                        'status' => 'Perlu Verifikasi',
                    ];
                })->values();
        }

        $students = $enrolled->toBase()->merge($pending->toBase())->sortBy('name')->values();

        return view('dashboard.guru.kelas_show', [
            'class' => $class,
            'school' => $class->school,
            'students' => $students,
            'enrolled_count' => $enrolled->count(),
            'pending_count' => $pending->count(),
        ]);
    }

    /**
     * Display mood check data table for teacher's students
     */
    public function moodCheck(Request $request)
    {
        $user = Auth::user();
        abort_unless($user?->role === 'teacher', 403);

        // Get student IDs based on teacher level
        $studentIds = $this->getStudentIds($user);

        // Get classes for filter based on teacher level
        $classes = collect();
        if ($user->teacher_level === 'admin') {
            $schoolId = DB::table('school_admins')
                ->where('user_id', $user->id)
                ->value('school_id');
            if ($schoolId) {
                $classes = ClassModel::where('school_id', $schoolId)->get();
            }
        } else {
            $classIds = DB::table('class_teacher')
                ->where('teacher_id', $user->id)
                ->pluck('class_id');
            $classes = ClassModel::whereIn('id', $classIds)->get();
        }

        // Filter parameters
        $classId = $request->get('class_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');
        $moodLevel = $request->get('mood_level');

        // Build query - only for teacher's students
        $query = MoodCheck::with(['user.class'])
            ->whereIn('mood_checks.user_id', $studentIds)
            ->join('users', 'mood_checks.user_id', '=', 'users.id')
            ->select('mood_checks.*');

        // Filter by class
        if ($classId) {
            $query->whereHas('user', function ($q) use ($classId) {
                $q->whereHas('class', function ($q2) use ($classId) {
                    $q2->where('classes.id', $classId);
                });
            });
        }

        // Filter by date range
        if ($startDate) {
            $query->whereDate('mood_checks.date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('mood_checks.date', '<=', $endDate);
        }

        // Filter by mood level
        if ($moodLevel && $moodLevel !== 'all') {
            $query->where('mood_checks.mood_level', $moodLevel);
        }

        // Search by name
        if ($search) {
            $query->where('users.name', 'like', '%' . $search . '%');
        }

        // Order and paginate
        $moodChecks = $query->orderBy('mood_checks.date', 'desc')
            ->orderBy('mood_checks.created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.guru.mood-check', compact(
            'classes',
            'moodChecks',
            'classId',
            'startDate',
            'endDate',
            'search',
            'moodLevel'
        ));
    }

    /**
     * Export mood check data to CSV for teacher's students
     */
    public function moodCheckExport(Request $request)
    {
        $user = Auth::user();
        abort_unless($user?->role === 'teacher', 403);

        // Get student IDs based on teacher level
        $studentIds = $this->getStudentIds($user);

        // Same filters as moodCheck method
        $classId = $request->get('class_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');
        $moodLevel = $request->get('mood_level');

        $query = MoodCheck::with(['user.class'])
            ->whereIn('mood_checks.user_id', $studentIds)
            ->join('users', 'mood_checks.user_id', '=', 'users.id')
            ->select('mood_checks.*');

        if ($classId) {
            $query->whereHas('user', function ($q) use ($classId) {
                $q->whereHas('class', function ($q2) use ($classId) {
                    $q2->where('classes.id', $classId);
                });
            });
        }

        if ($startDate) {
            $query->whereDate('mood_checks.date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('mood_checks.date', '<=', $endDate);
        }

        if ($moodLevel && $moodLevel !== 'all') {
            $query->where('mood_checks.mood_level', $moodLevel);
        }

        if ($search) {
            $query->where('users.name', 'like', '%' . $search . '%');
        }

        $moodChecks = $query->orderBy('mood_checks.date', 'desc')->get();

        $moodLabels = [
            5 => 'Sangat Senang',
            4 => 'Senang',
            3 => 'Netral',
            2 => 'Sedih',
            1 => 'Sangat Sedih',
        ];

        // Generate CSV
        $filename = 'mood_check_data_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($moodChecks, $moodLabels) {
            $file = fopen('php://output', 'w');
            // Add BOM for Excel UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            fputcsv($file, ['No', 'Nama Siswa', 'Kelas', 'Tanggal', 'Mood Level', 'Mood', 'AI Response']);

            $no = 1;
            foreach ($moodChecks as $check) {
                $className = $check->user->class->first()?->name ?? '-';
                fputcsv($file, [
                    $no++,
                    $check->user->name,
                    $className,
                    $check->date->format('d-m-Y'),
                    $check->mood_level,
                    $moodLabels[$check->mood_level] ?? '-',
                    strip_tags($check->ai_response ?? '-'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function screening(Request $request)
    {
        $user = Auth::user();
        abort_unless($user?->role === 'teacher', 403);

        /* ===============================
     * AMBIL SISWA YANG DIAJAR GURU
     * =============================== */
        $studentIds = $this->getStudentIds($user);

        /* ===============================
     * AMBIL KELAS (UNTUK FILTER)
     * =============================== */
        $classes = collect();

        if ($user->teacher_level === 'admin') {
            $schoolId = DB::table('school_admins')
                ->where('user_id', $user->id)
                ->value('school_id');

            if ($schoolId) {
                $classes = ClassModel::where('school_id', $schoolId)->get();
            }
        } else {
            $classIds = DB::table('class_teacher')
                ->where('teacher_id', $user->id)
                ->pluck('class_id');

            $classes = ClassModel::whereIn('id', $classIds)->get();
        }

        /* ===============================
     * FILTER INPUT
     * =============================== */
        $classId   = $request->class_id;
        $packageId = $request->package_id;
        $search    = $request->search;

        $packages = ScreeningPackage::where('is_active', true)->get();

        /* ===============================
     * QUERY SCREENING (KHUSUS SISWA GURU)
     * =============================== */
        $query = ScreeningSession::with([
            'user.class.school',
            'package',
        ])->whereIn('user_id', $studentIds);

        // Filter kelas
        if ($classId) {
            $query->whereHas(
                'user.class',
                fn($q) =>
                $q->where('classes.id', $classId)
            );
        }

        // Filter paket
        if ($packageId) {
            $query->where('screening_package_id', $packageId);
        }

        // Search nama
        if ($search) {
            $query->whereHas(
                'user',
                fn($q) =>
                $q->where('name', 'like', "%{$search}%")
            );
        }

        /* ===============================
     * ORDER + PAGINATION
     * =============================== */
        $sessions = $query
            ->orderByRaw('submitted_at IS NULL')
            ->orderByDesc('submitted_at')
            ->orderByDesc('started_at')
            ->paginate(15)
            ->withQueryString();

        /* ===============================
     * HITUNG OVERALL SCREENING
     * =============================== */
        $sessions->getCollection()->transform(function ($session) {
            $session->overall = $this->calculateOverallScreening($session);
            return $session;
        });

        return view('dashboard.guru.screening-report', compact(
            'sessions',
            'classes',
            'packages',
            'classId',
            'packageId',
            'search'
        ));
    }

    private function calculateOverallScreening($session)
    {
        if (! $session->submitted_at) {
            return [
                'label' => 'Active',
                'level' => 3,
                'details' => [],
                'recommendation' => '-',
            ];
        }

        $answers = ScreeningAnswer::where('screening_session_id', $session->id)
            ->with(['option', 'question.dimensions'])
            ->get();

        $dimensions = ScreeningDimension::where(
            'screening_package_id',
            $session->screening_package_id
        )->get();

        $details = [];
        $totalScore = 0;

        foreach ($dimensions as $dimension) {
            $rawScore = $answers
                ->filter(
                    fn($a) =>
                    $a->question->dimensions->contains('id', $dimension->id)
                )
                ->sum(fn($a) => $a->option->value);

            $score = $rawScore * $dimension->multiplier;

            $interpretation = match (true) {
                $score <= 9  => 'Normal',
                $score <= 13 => 'Mild',
                $score <= 20 => 'Moderate',
                $score <= 27 => 'Severe',
                default      => 'Extremely Severe',
            };

            $details[] = [
                'name' => $dimension->name,
                'score' => $score,
                'interpretation' => $interpretation,
            ];

            $totalScore += $score;
        }

        $overall = match (true) {
            $totalScore <= 28 => ['label' => 'Normal', 'level' => 5],
            $totalScore <= 40 => ['label' => 'Ringan', 'level' => 4],
            $totalScore <= 60 => ['label' => 'Sedang', 'level' => 3],
            default           => ['label' => 'Berat', 'level' => 2],
        };

        return [
            'label' => $overall['label'],
            'level' => $overall['level'],
            'details' => $details,
            'recommendation' =>
            'Disarankan melakukan konseling dengan guru BK atau psikolog.',
        ];
    }
}
