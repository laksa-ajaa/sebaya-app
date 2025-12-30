<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Journal;
use App\Models\MoodCheck;
use App\Models\School;
use App\Models\TeacherRegistration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminDashboardController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        // Statistik pengguna
        $totalUsers = User::count();
        $totalUmum = User::where('role', 'user')->whereDoesntHave('class')->count();
        $totalStudents = User::where('role', 'user')->whereHas('class')->count();
        $totalTeachers = User::where('role', 'teacher')->count();
        $totalAdmins = User::where('role', 'admin')->count();

        // Statistik mood checks
        $totalMoodChecks = MoodCheck::count();
        $moodDistribution = MoodCheck::select('mood_level', DB::raw('count(*) as total'))
            ->groupBy('mood_level')
            ->orderBy('mood_level')
            ->pluck('total', 'mood_level')
            ->toArray();

        // Data untuk chart mood distribution (urut: 5=Sangat Senang, 4=Senang, 3=Netral, 2=Sedih, 1=Sangat Sedih)
        $moodChartData = [
            $moodDistribution[5] ?? 0, // Sangat Senang
            $moodDistribution[4] ?? 0, // Senang
            $moodDistribution[3] ?? 0, // Netral
            $moodDistribution[2] ?? 0, // Sedih
            $moodDistribution[1] ?? 0, // Sangat Sedih
        ];

        // Statistik journals
        $totalJournals = Journal::count();
        $journalsThisWeek = Journal::where('created_at', '>=', now()->startOfWeek())->count();

        // Statistik mood checks per hari dalam seminggu
        $startOfWeek = now()->startOfWeek()->toDateString();
        $endOfWeek = now()->endOfWeek()->toDateString();

        // Mapping untuk hari dalam bahasa Indonesia
        $dayMapping = [
            'Monday' => 'Sen',
            'Tuesday' => 'Sel',
            'Wednesday' => 'Rab',
            'Thursday' => 'Kam',
            'Friday' => 'Jum',
            'Saturday' => 'Sab',
            'Sunday' => 'Min'
        ];

        $dailyMoodChecks = [];
        $days = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        foreach ($days as $day) {
            $dailyMoodChecks[$day] = 0;
        }

        // Ambil semua mood checks dalam seminggu ini dan group per hari
        $moodChecksThisWeek = MoodCheck::whereBetween('date', [$startOfWeek, $endOfWeek])
            ->select('date', DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        foreach ($moodChecksThisWeek as $check) {
            $dayName = $dayMapping[Carbon::parse($check->date)->format('l')] ?? '';
            if ($dayName && isset($dailyMoodChecks[$dayName])) {
                $dailyMoodChecks[$dayName] += $check->count;
            }
        }

        // Statistik screening (jika tabel ada)
        $totalScreenings = 0;
        $screeningDistribution = [];

        if (DB::getSchemaBuilder()->hasTable('screenings')) {
            $totalScreenings = DB::table('screenings')->count();

            // Ambil distribusi screening berdasarkan result JSON
            // Asumsi result memiliki field 'level' atau kategori
            $screenings = DB::table('screenings')
                ->whereNotNull('result')
                ->get();

            // Kategorisasi screening (jika ada data)
            $screeningDistribution = [
                'Sangat Parah' => 0,
                'Parah' => 0,
                'Sedang' => 0,
                'Normal' => 0,
                'Ringan' => 0
            ];
        }

        // Statistik habits dan todos
        $totalHabits = 0;
        $totalTodos = 0;
        $completedTodos = 0;

        if (DB::getSchemaBuilder()->hasTable('habits')) {
            $totalHabits = DB::table('habits')->count();
        }

        if (DB::getSchemaBuilder()->hasTable('todo_items')) {
            $totalTodos = DB::table('todo_items')->count();
            $completedTodos = DB::table('todo_items')->where('is_completed', true)->count();
        }

        // Statistik aktivitas hari ini
        $moodChecksToday = MoodCheck::whereDate('date', today())->count();
        $journalsToday = Journal::whereDate('created_at', today())->count();

        return view('dashboard.admin.index', compact(
            'totalUsers',
            'totalUmum',
            'totalStudents',
            'totalTeachers',
            'totalAdmins',
            'totalMoodChecks',
            'totalJournals',
            'journalsThisWeek',
            'moodChartData',
            'dailyMoodChecks',
            'totalScreenings',
            'screeningDistribution',
            'totalHabits',
            'totalTodos',
            'completedTodos',
            'moodChecksToday',
            'journalsToday'
        ));
    }

    public function statistik()
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        // Ambil query parameter untuk filter dan search
        $role = request()->get('role', 'all');
        $search = request()->get('search', '');
        $classCode = request()->get('class_code', '');
        $perPage = request()->get('per_page', 15);

        // Query dasar
        $query = User::query();

        // Filter berdasarkan role
        if ($role !== 'all') {
            if ($role === 'umum') {
                // User umum: role=user tapi tidak terdaftar di class
                $query->where('role', 'user')->whereDoesntHave('class');
            } elseif ($role === 'student') {
                // Student: role=user dan terdaftar di class
                $query->where('role', 'user')->whereHas('class');
            } else {
                // teacher atau admin
                $query->where('role', $role);
            }
        }

        // Filter berdasarkan kode sekolah
        if ($classCode) {
            $query->where('class_code', 'like', "%{$classCode}%");
        }

        // Search berdasarkan name
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Pagination dengan per_page dinamis
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        // Statistik cepat
        $totalUsers = User::count();
        $totalUmum = User::where('role', 'user')->whereDoesntHave('class')->count();
        $totalStudents = User::where('role', 'user')->whereHas('class')->count();
        $totalTeachers = User::where('role', 'teacher')->count();
        $totalAdmins = User::where('role', 'admin')->count();

        // Ambil semua kode sekolah unik untuk dropdown
        $classCodes = User::whereNotNull('class_code')
            ->distinct()
            ->orderBy('class_code')
            ->pluck('class_code')
            ->filter()
            ->values();

        return view('dashboard.admin.statistik', compact(
            'users',
            'totalUsers',
            'totalUmum',
            'totalStudents',
            'totalTeachers',
            'totalAdmins',
            'role',
            'search',
            'classCode',
            'perPage',
            'classCodes'
        ));
    }

    public function resetPassword($id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $user = User::findOrFail($id);

        // Jangan izinkan reset password untuk admin
        if ($user->role === 'admin') {
            return redirect()->route('admin.statistik')->with('error', 'Tidak dapat reset password untuk admin');
        }

        // Reset password menjadi default "password123"
        $user->password = \Illuminate\Support\Facades\Hash::make('password123');
        $user->save();

        return redirect()->route('admin.statistik')->with('success', 'Password berhasil direset menjadi "password123"');
    }

    public function deleteUser($id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $user = User::findOrFail($id);

        // Jangan izinkan hapus admin
        if ($user->role === 'admin') {
            return redirect()->route('admin.statistik')->with('error', 'Tidak dapat menghapus admin');
        }

        $user->delete();

        return redirect()->route('admin.statistik')->with('success', 'Pengguna berhasil dihapus');
    }

    public function laporan()
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
        return view('dashboard.admin.laporan');
    }

    public function teacherRequests()
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $requests = TeacherRegistration::query()
            ->where('status', 'pending')
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.admin.teacher_requests', compact('requests'));
    }

    public function approveTeacherRegistration($id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $registration = TeacherRegistration::with('user')->findOrFail($id);
        if ($registration->status !== 'pending') {
            return redirect()->back()->with('error', 'Permintaan sudah diproses.');
        }

        $user = $registration->user;

        $school = null;
        if ($registration->school_npsn) {
            $school = School::firstOrCreate(
                ['npsn' => $registration->school_npsn],
                [
                    'name' => $registration->school_name,
                    'address' => $registration->school_address,
                    'phone' => $registration->school_phone,
                ]
            );
        } else {
            $school = School::firstOrCreate(
                ['name' => $registration->school_name],
                [
                    'npsn' => null,
                    'address' => $registration->school_address,
                    'phone' => $registration->school_phone,
                ]
            );
        }

        // Tambahkan sebagai admin sekolah
        $existsAdmin = DB::table('school_admins')
            ->where('school_id', $school->id)
            ->where('user_id', $user->id)
            ->exists();
        if (! $existsAdmin) {
            DB::table('school_admins')->insert([
                'school_id' => $school->id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $registration->status = 'approved';
        $registration->verified_at = now();
        $registration->verified_by = Auth::id();
        $registration->rejection_reason = null;
        $registration->save();

        $user->account_status = 'active';
        $user->teacher_level = 'admin';
        $user->role = 'teacher';
        $user->save();

        // Kirim email notifikasi ke user
        Mail::raw(
            "Hi {$user->name},\n\nPengajuan akun guru Anda telah disetujui. Anda sekarang dapat mengakses dashboard guru.\n\nSalam,\nTim Sebaya",
            function ($message) use ($user) {
                $message->to($user->email)->subject('Pengajuan Guru Disetujui');
            }
        );

        return redirect()->back()->with('success', 'Guru berhasil diverifikasi dan ditetapkan sebagai admin sekolah.');
    }

    public function rejectTeacherRegistration(Request $request, $id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $registration = TeacherRegistration::with('user')->findOrFail($id);
        if ($registration->status !== 'pending') {
            return redirect()->back()->with('error', 'Permintaan sudah diproses.');
        }

        $data = $request->validate([
            'rejection_reason' => ['nullable', 'string'],
        ]);

        $registration->status = 'rejected';
        $registration->verified_at = now();
        $registration->verified_by = Auth::id();
        $registration->rejection_reason = $data['rejection_reason'] ?? null;
        $registration->save();

        $user = $registration->user;
        $user->account_status = 'suspended';
        $user->save();

        // Kirim email notifikasi penolakan ke user
        $reason = $registration->rejection_reason ? "Alasan: {$registration->rejection_reason}\n\n" : '';
        Mail::raw(
            "Hi {$user->name},\n\nPengajuan akun guru Anda ditolak. {$reason}Jika perlu, silakan ajukan kembali atau hubungi admin untuk informasi lebih lanjut.\n\nSalam,\nTim Sebaya",
            function ($message) use ($user) {
                $message->to($user->email)->subject('Pengajuan Guru Ditolak');
            }
        );

        return redirect()->back()->with('success', 'Permintaan guru ditolak.');
    }

    public function schools()
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $search = request()->get('search', '');
        $perPage = request()->get('per_page', 15);
        $query = School::with('admins');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $schools = $query->orderBy('name')->paginate($perPage)->withQueryString();
        $totalSchools = School::count();

        return view('dashboard.admin.schools', compact('schools', 'totalSchools', 'search', 'perPage'));
    }

    public function sekolahStore(Request $request)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'npsn' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'admin_option' => 'required|in:none,new',
            'new_admin_name' => 'nullable|required_if:admin_option,new|string|max:255',
            'new_admin_email' => 'nullable|required_if:admin_option,new|email|unique:users,email',
            'new_admin_password' => 'nullable|required_if:admin_option,new|string|min:6',
        ]);

        DB::transaction(function () use ($validated) {
            // Create school
            $school = School::create([
                'name' => $validated['name'],
                'npsn' => $validated['npsn'] ?? null,
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
            ]);

            // Create admin teacher if option is 'new'
            if ($validated['admin_option'] === 'new') {
                $newAdmin = User::create([
                    'name' => $validated['new_admin_name'],
                    'email' => $validated['new_admin_email'],
                    'password' => Hash::make($validated['new_admin_password']),
                    'role' => 'teacher',
                    'account_status' => 'active',
                    'otp_verified_at' => now(),
                    'username' => strtolower(str_replace(' ', '_', $validated['new_admin_name'])),
                    'whatsapp_number' => '-',
                    'teacher_level' => 'admin',
                ]);

                // Assign to school_admins
                DB::table('school_admins')->insert([
                    'school_id' => $school->id,
                    'user_id' => $newAdmin->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Also add to school_teachers pivot
                $school->teachers()->attach($newAdmin->id);
            }
        });

        return redirect()->route('admin.schools')->with('success', 'Sekolah berhasil ditambahkan');
    }

    public function sekolahUpdate(Request $request, $id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $school = School::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'npsn' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        // Keep school code unchanged (auto-generated)
        $school->update($validated);

        return redirect()->route('admin.schools')->with('success', 'Sekolah berhasil diupdate');
    }

    public function sekolahDelete($id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $school = School::findOrFail($id);
        $school->delete();

        return redirect()->route('admin.schools')->with('success', 'Sekolah berhasil dihapus');
    }

    public function kelasIndex($school_id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $school = School::with('teachers')->findOrFail($school_id);
        $search = request()->get('search', '');
        $grade = request()->get('grade', '');
        $perPage = request()->get('per_page', 15);

        $query = $school->classes()->with('teachers');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($grade) {
            $query->where('grade', 'like', "%{$grade}%");
        }

        $classes = $query->orderBy('name')->paginate($perPage)->withQueryString();

        // Get all teachers from this school (school_teachers pivot)
        $teachers = $school->teachers;

        return view('dashboard.admin.kelas', compact('school', 'classes', 'search', 'grade', 'perPage', 'teachers'));
    }

    public function kelasStore(Request $request, $school_id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        // Validasi bahwa sekolah exists
        $school = School::findOrFail($school_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade' => 'nullable|string|max:10',
            'teacher_option' => 'required|in:none,existing,new',
            'teacher_id' => [
                'nullable',
                'required_if:teacher_option,existing',
                'integer',
                Rule::exists('school_teachers', 'teacher_id')->where(fn($q) => $q->where('school_id', $school_id)),
            ],
            'new_teacher_name' => 'nullable|required_if:teacher_option,new|string|max:255',
            'new_teacher_email' => 'nullable|required_if:teacher_option,new|email|unique:users,email',
            'new_teacher_password' => 'nullable|required_if:teacher_option,new|string|min:6',
        ]);

        DB::transaction(function () use ($validated, $school) {
            $teacherId = null;

            // Jika option existing, gunakan teacher_id yang dipilih
            if ($validated['teacher_option'] === 'existing') {
                $teacherId = $validated['teacher_id'];
            }
            // Jika option new, buat guru baru
            elseif ($validated['teacher_option'] === 'new') {
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
                // Pastikan guru baru terdaftar ke sekolah (school_teachers)
                $school->teachers()->syncWithoutDetaching([$newTeacher->id]);
                $teacherId = $newTeacher->id;
            }

            $klasData = [
                'school_id' => $school->id,
                'name' => $validated['name'],
                'grade' => $validated['grade'] ?? null,
            ];

            // Generate unique class code
            $classPrefix = 'CLS';
            do {
                $random = Str::upper(Str::random(6));
                $generatedCode = $classPrefix . '-' . $random;
            } while (ClassModel::where('code', $generatedCode)->exists());

            $klasData['code'] = $generatedCode;
            $class = ClassModel::create($klasData);

            // Set guru bertanggung jawab via pivot class_teacher
            if ($teacherId) {
                $class->teachers()->sync([$teacherId]);
            } else {
                $class->teachers()->sync([]);
            }
        });

        return redirect()->route('admin.sekolah.kelas.index', $school_id)->with('success', 'Kelas berhasil ditambahkan');
    }

    public function kelasUpdate(Request $request, $school_id, $id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $school = School::findOrFail($school_id);
        $class = ClassModel::findOrFail($id);

        // Validasi bahwa kelas milik sekolah yang benar
        if ($class->school_id != $school_id) {
            abort(404, 'Kelas tidak ditemukan untuk sekolah ini');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade' => 'nullable|string|max:10',
            'teacher_option' => 'required|in:none,existing,new',
            'teacher_id' => [
                'nullable',
                'required_if:teacher_option,existing',
                'integer',
                Rule::exists('school_teachers', 'teacher_id')->where(fn($q) => $q->where('school_id', $school_id)),
            ],
            'new_teacher_name' => 'nullable|required_if:teacher_option,new|string|max:255',
            'new_teacher_email' => 'nullable|required_if:teacher_option,new|email|unique:users,email',
            'new_teacher_password' => 'nullable|required_if:teacher_option,new|string|min:6',
        ]);

        DB::transaction(function () use ($validated, $class, $school) {
            $teacherId = null;

            if ($validated['teacher_option'] === 'existing') {
                $teacherId = $validated['teacher_id'];
            } elseif ($validated['teacher_option'] === 'new') {
                $newTeacher = User::create([
                    'name' => $validated['new_teacher_name'],
                    'email' => $validated['new_teacher_email'],
                    'password' => \Illuminate\Support\Facades\Hash::make($validated['new_teacher_password']),
                    'role' => 'teacher',
                    'account_status' => 'active',
                    'otp_verified_at' => now(),
                    'username' => strtolower(str_replace(' ', '_', $validated['new_teacher_name'])),
                    'whatsapp_number' => '-',
                    'teacher_level' => 'kelas',
                ]);
                // Pastikan guru baru terdaftar ke sekolah (school_teachers)
                $school->teachers()->syncWithoutDetaching([$newTeacher->id]);
                $teacherId = $newTeacher->id;
            }

            // Pastikan school_id tetap sama
            $class->update([
                'name' => $validated['name'],
                'grade' => $validated['grade'],
                'school_id' => $school->id,
            ]);

            // Set guru bertanggung jawab via pivot class_teacher
            if ($teacherId) {
                $class->teachers()->sync([$teacherId]);
            } else {
                $class->teachers()->sync([]);
            }
        });

        return redirect()->route('admin.sekolah.kelas.index', $school_id)->with('success', 'Kelas berhasil diupdate');
    }

    public function kelasDelete($school_id, $id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $class = ClassModel::findOrFail($id);

        // Validasi bahwa kelas milik sekolah yang benar
        if ($class->school_id != $school_id) {
            abort(404, 'Kelas tidak ditemukan untuk sekolah ini');
        }

        $class->delete();

        return redirect()->route('admin.sekolah.kelas.index', $school_id)->with('success', 'Kelas berhasil dihapus');
    }

    public function kelasShow($school_id, $id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $school = School::findOrFail($school_id);
        $class = ClassModel::with('students')->findOrFail($id);

        if ($class->school_id != $school_id) {
            abort(404, 'Kelas tidak ditemukan untuk sekolah ini');
        }

        // Siswa terdaftar di kelas (pivot class_students)
        $enrolled = $class->students()
            ->orderBy('name')
            ->get()
            ->map(function ($u) {
                $u->setAttribute('status', 'Terdaftar');
                return $u;
            });

        // Siswa yang mengisi kode kelas namun belum di pivot
        $pending = collect();
        if ($class->code) {
            $pending = User::where('role', 'user')
                ->where('class_code', $class->code)
                ->whereNotIn('id', $enrolled->pluck('id'))
                ->orderBy('name')
                ->get()
                ->map(function ($u) {
                    $u->setAttribute('status', 'Perlu Verifikasi');
                    return $u;
                });
        }

        $students = $enrolled->merge($pending)->sortBy('name')->values();

        return view('dashboard.admin.kelas_show', compact('school', 'class', 'students'));
    }

    public function kelasVerifyStudent($school_id, $id, $user_id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $class = ClassModel::findOrFail($id);
        if ($class->school_id != $school_id) {
            abort(404, 'Kelas tidak ditemukan untuk sekolah ini');
        }

        $user = User::findOrFail($user_id);

        // Pastikan bukan admin/teacher
        if ($user->role !== 'user') {
            return redirect()->back()->with('error', 'Hanya siswa yang dapat diverifikasi ke kelas');
        }

        // Attach ke pivot class_students jika belum
        if (! $class->students()->where('users.id', $user->id)->exists()) {
            $class->students()->attach($user->id, ['start_date' => now()]);
        }

        return redirect()->back()->with('success', 'Siswa berhasil diverifikasi dan ditambahkan ke kelas');
    }

    public function kelasRejectStudent($school_id, $id, $user_id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $class = ClassModel::findOrFail($id);
        if ($class->school_id != $school_id) {
            abort(404, 'Kelas tidak ditemukan untuk sekolah ini');
        }

        $user = User::findOrFail($user_id);

        // Hanya siswa yang bisa ditolak
        if ($user->role !== 'user') {
            return redirect()->back()->with('error', 'Hanya siswa yang dapat ditolak');
        }

        // Jika pending (punya class_code), kosongkan agar tidak lagi tercatat sebagai perlu verifikasi
        $user->class_code = null;
        $user->save();

        return redirect()->back()->with('success', 'Permintaan siswa ditolak. Kode kelas dihapus.');
    }
}
