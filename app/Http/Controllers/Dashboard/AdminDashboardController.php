<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Journal;
use App\Models\MoodCheck;
use App\Models\School;
use App\Models\ScreeningAnswer;
use App\Models\ScreeningDimension;
use App\Models\ScreeningPackage;
use App\Models\ScreeningSession;
use App\Models\TeacherRegistration;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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

        // Statistik journals
        $totalJournals = Journal::count();
        $journalsThisWeek = Journal::where('created_at', '>=', now()->startOfWeek())->count();

        // Statistik mood checks dengan rentang tanggal dinamis
        $startDateInput = request()->get('start_date');
        $endDateInput = request()->get('end_date');

        $startDate = $startDateInput ? Carbon::parse($startDateInput)->startOfDay() : now()->startOfWeek();
        $endDate = $endDateInput ? Carbon::parse($endDateInput)->endOfDay() : now()->endOfWeek();

        // Normalisasi jika end < start
        if ($endDate->lessThan($startDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        // Data untuk donut chart - menggunakan range tanggal yang dipilih
        $moodDistribution = MoodCheck::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->select('mood_level', DB::raw('count(*) as total'))
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

        // Hitung total per hari dalam rentang
        $moodChecksInRange = MoodCheck::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->select('date', DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        foreach ($moodChecksInRange as $check) {
            $label = Carbon::parse($check->date)->format('d M');
            if (isset($dailyMoodChecks[$label])) {
                $dailyMoodChecks[$label] += $check->count;
            }
        }

        // Hitung stacked per level
        $moodChecksByLevel = MoodCheck::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->select('date', 'mood_level', DB::raw('COUNT(*) as count'))
            ->groupBy('date', 'mood_level')
            ->orderBy('date')
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

        // Statistik screening
        $totalScreenings = ScreeningSession::count();
        $activeScreenings = ScreeningSession::whereNull('submitted_at')->count();
        $completedScreenings = ScreeningSession::whereNotNull('submitted_at')->count();

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
            'dailyMoodStacked',
            'chartDateCategories',
            'startDate',
            'endDate',
            'totalHabits',
            'totalTodos',
            'completedTodos',
            'moodChecksToday',
            'journalsToday',
            'totalScreenings',
            'activeScreenings',
            'completedScreenings'
        ));
    }

    /**
     * AJAX endpoint untuk data chart berdasarkan rentang tanggal
     */
    public function chartData(Request $request)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : now()->startOfWeek();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : now()->endOfWeek();

        if ($endDate->lessThan($startDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

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

        // Total per hari
        $moodChecksInRange = MoodCheck::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->select('date', DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->get();

        foreach ($moodChecksInRange as $check) {
            $label = Carbon::parse($check->date)->format('d M');
            if (isset($dailyMoodChecks[$label])) {
                $dailyMoodChecks[$label] += $check->count;
            }
        }

        // Stacked per level
        $moodChecksByLevel = MoodCheck::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
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

        // Distribusi mood (donut chart)
        $moodDistribution = MoodCheck::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
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

        return response()->json([
            'moodChartData' => $moodChartData,
            'dailyMoodChecks' => $dailyMoodChecks,
            'dailyMoodStacked' => $dailyMoodStacked,
            'chartDateCategories' => $chartDateCategories,
        ]);
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

    public function userStore(Request $request)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:user,teacher',
            'teacher_level' => 'nullable|string|in:kelas,admin'
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
        ];

        if ($request->role === 'teacher') {
            $userData['teacher_level'] = $request->teacher_level ?? 'kelas';
            // automatically approve the teacher if admin created it
            $userData['status_guru'] = 'approved';
        }

        User::create($userData);

        return redirect()->route('admin.statistik')->with('success', 'Pengguna baru berhasil dibuat');
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
        $teachers = User::where('role', 'teacher')
                    ->where('teacher_level', 'admin')
                    ->orderBy('name')
                    ->get();

        return view('dashboard.admin.schools', compact('schools', 'totalSchools', 'search', 'perPage', 'teachers'));
    }

    public function sekolahStore(Request $request)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'npsn' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'admin_option' => 'required|in:none,new,existing',
            'admin_id' => 'nullable|required_if:admin_option,existing|exists:users,id',
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

            // Assign existing admin
            if ($validated['admin_option'] === 'existing' && !empty($validated['admin_id'])) {
                $user = User::find($validated['admin_id']);
                if ($user) {
                    $user->update(['teacher_level' => 'admin']);
                    DB::table('school_admins')->insert([
                        'school_id' => $school->id,
                        'user_id' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $school->teachers()->syncWithoutDetaching([$user->id]);
                }
            }

            // Create admin teacher if option is 'new'
            if ($validated['admin_option'] === 'new') {
                $baseUsername = strtolower(str_replace(' ', '_', $validated['new_admin_name']));
                $username = $baseUsername;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }

                $newAdmin = User::create([
                    'name' => $validated['new_admin_name'],
                    'email' => $validated['new_admin_email'],
                    'password' => Hash::make($validated['new_admin_password']),
                    'role' => 'teacher',
                    'account_status' => 'active',
                    'otp_verified_at' => now(),
                    'username' => $username,
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
            'admin_option' => 'required|in:none,new,existing',
            'admin_id' => 'nullable|required_if:admin_option,existing|exists:users,id',
            'new_admin_name' => 'nullable|required_if:admin_option,new|string|max:255',
            'new_admin_email' => 'nullable|required_if:admin_option,new|email|unique:users,email',
            'new_admin_password' => 'nullable|required_if:admin_option,new|string|min:6',
        ]);

        DB::transaction(function () use ($validated, $school) {
            $school->update([
                'name' => $validated['name'],
                'npsn' => $validated['npsn'] ?? null,
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
            ]);

            // Assign existing admin
            if ($validated['admin_option'] === 'existing' && !empty($validated['admin_id'])) {
                $user = User::find($validated['admin_id']);
                if ($user) {
                    $exists = DB::table('school_admins')->where('school_id', $school->id)->where('user_id', $user->id)->exists();
                    if (!$exists) {
                        $user->update(['teacher_level' => 'admin']);
                        DB::table('school_admins')->insert([
                            'school_id' => $school->id,
                            'user_id' => $user->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $school->teachers()->syncWithoutDetaching([$user->id]);
                    }
                }
            }

            if ($validated['admin_option'] === 'new') {
                $baseUsername = strtolower(str_replace(' ', '_', $validated['new_admin_name']));
                $username = $baseUsername;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }

                $newAdmin = User::create([
                    'name' => $validated['new_admin_name'],
                    'email' => $validated['new_admin_email'],
                    'password' => Hash::make($validated['new_admin_password']),
                    'role' => 'teacher',
                    'account_status' => 'active',
                    'otp_verified_at' => now(),
                    'username' => $username,
                    'whatsapp_number' => '-',
                    'teacher_level' => 'admin',
                ]);

                DB::table('school_admins')->insert([
                    'school_id' => $school->id,
                    'user_id' => $newAdmin->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $school->teachers()->syncWithoutDetaching([$newAdmin->id]);
            }
        });

        return redirect()->route('admin.schools')->with('success', 'Sekolah berhasil dan data admin diupdate');
    }

    public function sekolahDelete($id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $school = School::findOrFail($id);
        $school->delete();

        return redirect()->route('admin.schools')->with('success', 'Sekolah berhasil dihapus');
    }

    public function sekolahRemoveAdmin($school_id, $user_id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        DB::table('school_admins')->where('school_id', $school_id)->where('user_id', $user_id)->delete();
        
        return redirect()->route('admin.schools')->with('success', 'Admin sekolah berhasil dihapus dari kewenangannya pada sekolah ini');
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

        // Get teachers from this school (school_teachers pivot) that have teacher_level = 'kelas'
        $teachers = $school->teachers()->where('teacher_level', 'kelas')->get();

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
                $baseUsername = strtolower(str_replace(' ', '_', $validated['new_teacher_name']));
                $username = $baseUsername;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }

                $newTeacher = User::create([
                    'name' => $validated['new_teacher_name'],
                    'email' => $validated['new_teacher_email'],
                    'password' => Hash::make($validated['new_teacher_password']),
                    'role' => 'teacher',
                    'account_status' => 'active',
                    'otp_verified_at' => now(),
                    'username' => $username,
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
                // Ensure teacher level is 'kelas' if not already 'admin'
                $teacher = User::find($teacherId);
                if ($teacher && $teacher->teacher_level !== 'admin') {
                    $teacher->update(['teacher_level' => 'kelas']);
                }

                $class->teachers()->attach($teacherId);
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
                $baseUsername = strtolower(str_replace(' ', '_', $validated['new_teacher_name']));
                $username = $baseUsername;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }

                $newTeacher = User::create([
                    'name' => $validated['new_teacher_name'],
                    'email' => $validated['new_teacher_email'],
                    'password' => \Illuminate\Support\Facades\Hash::make($validated['new_teacher_password']),
                    'role' => 'teacher',
                    'account_status' => 'active',
                    'otp_verified_at' => now(),
                    'username' => $username,
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
                // Ensure teacher level is 'kelas' if not already 'admin'
                $teacher = User::find($teacherId);
                if ($teacher && $teacher->teacher_level !== 'admin') {
                    $teacher->update(['teacher_level' => 'kelas']);
                }

                $class->teachers()->syncWithoutDetaching([$teacherId]);
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

    public function kelasRemoveTeacher($school_id, $id, $user_id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $class = ClassModel::findOrFail($id);
        
        if ($class->school_id != $school_id) {
            abort(404, 'Kelas tidak ditemukan untuk sekolah ini');
        }

        $class->teachers()->detach($user_id);
        
        return redirect()->route('admin.sekolah.kelas.index', $school_id)->with('success', 'Guru kelas berhasil dihapus dari kewenangannya pada kelas ini');
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

    /**
     * Display mood check data table
     */
    public function moodCheck(Request $request)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        // Get all schools with classes for filter
        $schools = School::with('classes')->get();

        // Filter parameters
        $schoolId = $request->get('school_id');
        $classId = $request->get('class_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');
        $moodLevel = $request->get('mood_level');

        // Build query
        $query = MoodCheck::with(['user.class'])
            ->join('users', 'mood_checks.user_id', '=', 'users.id')
            ->select('mood_checks.*');

        // Filter by school (melalui pivot class_students)
        if ($schoolId) {
            $query->whereHas('user', function ($q) use ($schoolId) {
                $q->whereHas('class', function ($q2) use ($schoolId) {
                    $q2->where('school_id', $schoolId);
                });
            });
        }

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

        // Get classes for selected school (for AJAX)
        $classes = [];
        if ($schoolId) {
            $classes = ClassModel::where('school_id', $schoolId)->get();
        }

        return view('dashboard.admin.mood-check', compact(
            'schools',
            'classes',
            'moodChecks',
            'schoolId',
            'classId',
            'startDate',
            'endDate',
            'search',
            'moodLevel'
        ));
    }

    /**
     * Export mood check data to CSV
     */
    public function moodCheckExport(Request $request)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $schoolId = $request->get('school_id');
        $classId = $request->get('class_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');
        $moodLevel = $request->get('mood_level');

        $query = MoodCheck::with(['user.class'])
            ->join('users', 'mood_checks.user_id', '=', 'users.id')
            ->select('mood_checks.*');

        if ($schoolId) {
            $query->whereHas('user.class', fn($q) => $q->where('school_id', $schoolId));
        }

        if ($classId) {
            $query->whereHas('user.class', fn($q) => $q->where('id', $classId));
        }

        if ($startDate) $query->whereDate('mood_checks.date', '>=', $startDate);
        if ($endDate) $query->whereDate('mood_checks.date', '<=', $endDate);
        if ($moodLevel && $moodLevel !== 'all') $query->where('mood_checks.mood_level', $moodLevel);
        if ($search) $query->where('users.name', 'like', '%' . $search . '%');

        $moodChecks = $query->orderBy('mood_checks.date', 'desc')->get();

        $moodLabels = [
            5 => 'Sangat Senang',
            4 => 'Senang',
            3 => 'Netral',
            2 => 'Sedih',
            1 => 'Sangat Sedih',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Mood Check');

        $headers = ['No', 'Nama Siswa', 'Email', 'Kelas', 'Tanggal', 'Mood Level', 'Mood', 'AI Response'];
        $sheet->fromArray($headers, null, 'A1');

        $rowCount = 2;
        foreach ($moodChecks as $index => $check) {
            $className = $check->user->class->first()?->name ?? '-';
            $sheet->setCellValue('A' . $rowCount, (int)($index + 1));
            $sheet->setCellValue('B' . $rowCount, (string)($check->user->name ?? '-'));
            $sheet->setCellValue('C' . $rowCount, (string)($check->user->email ?? '-'));
            $sheet->setCellValue('D' . $rowCount, (string)($className));
            $sheet->setCellValue('E' . $rowCount, (string)($check->date->format('d-m-Y')));
            $sheet->setCellValue('F' . $rowCount, (int)$check->mood_level);
            $sheet->setCellValue('G' . $rowCount, (string)($moodLabels[$check->mood_level] ?? '-'));
            $sheet->setCellValue('H' . $rowCount, (string)strip_tags($check->ai_response ?? '-'));
            $rowCount++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'mood_check_data_' . now()->format('Y-m-d_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }


    /**
     * Display screening data table
     */
    public function screening(Request $request)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $schoolId  = $request->school_id;
        $classId   = $request->class_id;
        $packageId = $request->package_id;
        $search    = $request->search;

        $schools  = School::with('classes')->get();
        $packages = ScreeningPackage::where('is_active', true)->get();

        $query = ScreeningSession::with([
            'user.class.school',
            'package',
        ]);

        if ($schoolId) {
            $query->whereHas(
                'user.class',
                fn($q) =>
                $q->where('school_id', $schoolId)
            );
        }

        if ($classId) {
            $query->whereHas(
                'user.class',
                fn($q) =>
                $q->where('id', $classId)
            );
        }

        if ($packageId) {
            $query->where('screening_package_id', $packageId);
        }

        if ($search) {
            $query->whereHas(
                'user',
                fn($q) =>
                $q->where('name', 'like', "%{$search}%")
            );
        }

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

        return view('dashboard.admin.screening-report', compact(
            'sessions',
            'schools',
            'packages',
            'schoolId',
            'classId',
            'packageId',
            'search'
        ));
    }

    /**
     * Export screening report data to XLSX with detailed dimensions
     */
    public function screeningReportExport(Request $request)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $schoolId  = $request->school_id;
        $classId   = $request->class_id;
        $packageId = $request->package_id;
        $search    = $request->search;

        $query = ScreeningSession::with([
            'user.class.school',
            'package',
        ]);

        if ($schoolId) {
            $query->whereHas('user.class', fn($q) => $q->where('school_id', $schoolId));
        }

        if ($classId) {
            $query->whereHas('user.class', fn($q) => $q->where('id', $classId));
        }

        if ($packageId) {
            $query->where('screening_package_id', $packageId);
        }

        if ($search) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $sessions = $query->orderByDesc('submitted_at')->get();

        // Group sessions by package
        $sessionsByPackage = $sessions->groupBy('screening_package_id');

        $spreadsheet = new Spreadsheet();
        $sheetIndex = 0;

        foreach ($sessionsByPackage as $pkgId => $packageSessions) {
            // Get package info from first session
            $packageName = $packageSessions->first()->package->name ?? 'Unknown Package';

            // Create or get sheet
            if ($sheetIndex === 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet($sheetIndex);
            }

            // Sanitize sheet title (max 31 chars, no special chars)
            $sheetTitle = substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $packageName), 0, 31);
            $sheet->setTitle($sheetTitle);

            // Get all unique dimensions for this package
            $sampleSession = $packageSessions->first();
            $overall = $this->calculateOverallScreening($sampleSession);
            $dimensions = collect($overall['details'] ?? []);

            // Build Row 1 headers (main headers with merged cells)
            $row1Headers = ['No', 'Nama', 'Email', 'Sekolah', 'Kelas', 'Tanggal Submit', 'Status'];
            $row2Headers = ['', '', '', '', '', '', '']; // Empty for merged cells above

            $currentCol = count($row1Headers) + 1; // Start after basic info columns

            // Add dimension headers
            foreach ($dimensions as $dimension) {
                $row1Headers[] = $dimension['name'];
                $row1Headers[] = ''; // Will be merged
                $row2Headers[] = 'Skor';
                $row2Headers[] = 'Interpretasi';
            }

            // Add overall interpretation headers
            $row1Headers[] = 'Interpretasi Keseluruhan';
            $row1Headers[] = 'Rekomendasi';
            $row2Headers[] = '';
            $row2Headers[] = '';

            // Write headers
            $sheet->fromArray($row1Headers, null, 'A1');
            $sheet->fromArray($row2Headers, null, 'A2');

            // Merge cells for basic info columns (rows 1-2)
            $basicInfoCols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
            foreach ($basicInfoCols as $col) {
                $sheet->mergeCells($col . '1:' . $col . '2');
            }

            // Merge cells for each dimension name (2 columns wide)
            $col = 8; // Start after basic info (H column)
            foreach ($dimensions as $dimension) {
                $startCol = $this->getColumnLetter($col);
                $endCol = $this->getColumnLetter($col + 1);
                $sheet->mergeCells($startCol . '1:' . $endCol . '1');
                $col += 2;
            }

            // Merge cells for overall interpretation and recommendation
            $overallCol = $this->getColumnLetter($col);
            $sheet->mergeCells($overallCol . '1:' . $overallCol . '2');
            $col++;
            $rekomCol = $this->getColumnLetter($col);
            $sheet->mergeCells($rekomCol . '1:' . $rekomCol . '2');

            // Style header rows
            $lastCol = $this->getColumnLetter(count($row1Headers));
            $headerRange = 'A1:' . $lastCol . '2';
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '1e1f21']
                    ]
                ]
            ]);

            $rowCount = 3; // Start data from row 3
            foreach ($packageSessions as $index => $session) {
                $overall = $this->calculateOverallScreening($session);
                $col = 0;

                // Basic info
                $sheet->setCellValue($this->getColumnLetter(++$col) . $rowCount, (int)($index + 1));
                $sheet->setCellValue($this->getColumnLetter(++$col) . $rowCount, (string)($session->user->name ?? '-'));
                $sheet->setCellValue($this->getColumnLetter(++$col) . $rowCount, (string)($session->user->email ?? '-'));
                $sheet->setCellValue($this->getColumnLetter(++$col) . $rowCount, (string)($session->user?->class?->first()?->school?->name ?? '-'));
                $sheet->setCellValue($this->getColumnLetter(++$col) . $rowCount, (string)($session->user?->class?->first()?->name ?? '-'));
                $sheet->setCellValue($this->getColumnLetter(++$col) . $rowCount, (string)($session->submitted_at ? $session->submitted_at->format('d-m-Y H:i') : '-'));
                $sheet->setCellValue($this->getColumnLetter(++$col) . $rowCount, (string)($session->submitted_at ? 'Submitted' : 'Active'));

                // Dimension scores and interpretations
                foreach ($overall['details'] ?? [] as $dimension) {
                    $sheet->setCellValue($this->getColumnLetter(++$col) . $rowCount, (float)($dimension['score'] ?? 0));
                    $sheet->setCellValue($this->getColumnLetter(++$col) . $rowCount, (string)($dimension['interpretation'] ?? '-'));
                }

                // Overall interpretation and recommendation
                $sheet->setCellValue($this->getColumnLetter(++$col) . $rowCount, (string)($overall['label'] ?? '-'));
                $sheet->setCellValue($this->getColumnLetter(++$col) . $rowCount, (string)($overall['recommendation'] ?? '-'));

                $rowCount++;
            }

            // Apply borders to all data cells
            if ($rowCount > 3) {
                $lastCol = $this->getColumnLetter(count($row1Headers));
                $dataRange = 'A3:' . $lastCol . ($rowCount - 1);
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '1e1f21']
                        ]
                    ]
                ]);
            }

            // Auto-size all columns
            for ($i = 1; $i <= count($row1Headers); $i++) {
                $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
            }

            $sheetIndex++;
        }

        $filename = 'screening_report_' . now()->format('Y-m-d_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function calculateOverallScreening($session)
    {
        // Jika belum submit
        if (! $session->submitted_at) {
            return [
                'label' => 'Active',
                'level' => 3,
                'details' => [],
                'recommendation' => '-',
            ];
        }

        $session->load('package');
        $packageCode = $session->package->code;
        $config = config('screening.interpretations.' . $packageCode) ?? config('screening.interpretations.default');
        $overallConfig = config('screening.overall.' . $packageCode) ?? config('screening.overall.default');

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

            $dimensionConfig = $config[$dimension->code] ?? $config['general'];
            $interpretation = $this->getInterpretationFromRanges($score, $dimensionConfig['ranges']);

            $details[] = [
                'name' => $dimension->name,
                'score' => $score,
                'interpretation' => $interpretation['label'],
                'level' => $interpretation['level'],
            ];

            $totalScore += $score;
        }

        $overall = $this->getOverallFromSeverityLevel($details, $overallConfig['by_severity_level']);

        return [
            'label' => $overall['interpretation'],
            'level' => $overall['level'],
            'details' => $details,
            'recommendation' => $overall['recommendation'],
        ];
    }

    private function getInterpretationFromRanges($score, $ranges)
    {
        foreach ($ranges as $range) {
            if ($score >= $range['min'] && $score <= $range['max']) {
                return ['label' => $range['label'], 'level' => $range['level']];
            }
        }
        return ['label' => 'Unknown', 'level' => 0];
    }

    private function getOverallFromSeverityLevel($details, $severityConfig)
    {
        // Find the lowest (most severe) level from dimensions
        $minLevel = collect($details)->min('level');

        $config = $severityConfig[$minLevel] ?? ['interpretation' => 'Unknown', 'recommendation' => ''];

        return [
            'interpretation' => $config['interpretation'],
            'level' => $minLevel,
            'recommendation' => $config['recommendation'],
        ];
    }

    /**
     * Display user activity statistics
     */
    public function userActivity(Request $request)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $search = $request->get('search', '');
        $role = $request->get('role', 'all');
        $perPage = $request->get('per_page', 15);

        $query = User::query();

        // Filter by role
        if ($role !== 'all') {
            if ($role === 'umum') {
                $query->where('role', 'user')->whereDoesntHave('class');
            } elseif ($role === 'student') {
                $query->where('role', 'user')->whereHas('class');
            } else {
                $query->where('role', $role);
            }
        }

        // Search by name or email
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Get users with activity counts
        $users = $query->withCount([
            'moodChecks',
            'journals',
            'screeningSessions',
            'chatMessages'
        ])
            ->with(['journals' => function ($q) {
                $q->withCount(['todoItems', 'habits']);
            }])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        // Calculate todo and habit counts for each user
        $users->getCollection()->transform(function ($user) {
            $user->todo_items_count = $user->journals->sum('todo_items_count');
            $user->habits_count = $user->journals->sum('habits_count');
            return $user;
        });

        return view('dashboard.admin.user_activity', compact('users', 'search', 'role', 'perPage'));
    }

    /**
     * Export user activity statistics to CSV
     */
    public function userActivityExport(Request $request)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $search = $request->get('search', '');
        $role = $request->get('role', 'all');

        $query = User::query();

        if ($role !== 'all') {
            if ($role === 'umum') $query->where('role', 'user')->whereDoesntHave('class');
            elseif ($role === 'student') $query->where('role', 'user')->whereHas('class');
            else $query->where('role', $role);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->withCount(['moodChecks', 'journals', 'screeningSessions', 'chatMessages'])
            ->with(['journals' => fn($q) => $q->withCount(['todoItems', 'habits'])])
            ->orderBy('name')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('User Activity');

        $headers = ['No', 'Nama', 'Email', 'Role', 'Mood', 'Journals', 'Todo', 'Habits', 'Screening', 'Chat', 'Total'];
        $sheet->fromArray($headers, null, 'A1');

        $rowCount = 2;
        foreach ($users as $index => $user) {
            $roleLabel = match ($user->role) {
                'admin' => 'Admin',
                'teacher' => 'Guru',
                'user' => ($user->class()->exists() ? 'Siswa' : 'Umum'),
                default => $user->role
            };

            $todoCount = ($user->journals->sum('todo_items_count') ?? 0);
            $habitsCount = ($user->journals->sum('habits_count') ?? 0);
            $moodCount = ($user->mood_checks_count ?? 0);
            $journalCount = ($user->journals_count ?? 0);
            $screeningCount = ($user->screening_sessions_count ?? 0);
            $chatCount = ($user->chat_messages_count ?? 0);

            $total = $moodCount + $journalCount + $todoCount +
                $habitsCount + $screeningCount + $chatCount;

            $sheet->setCellValue('A' . $rowCount, $index + 1);
            $sheet->setCellValue('B' . $rowCount, $user->name);
            $sheet->setCellValue('C' . $rowCount, $user->email);
            $sheet->setCellValue('D' . $rowCount, $roleLabel);
            $sheet->setCellValue('E' . $rowCount, (int)$moodCount);
            $sheet->setCellValue('F' . $rowCount, (int)$journalCount);
            $sheet->setCellValue('G' . $rowCount, (int)$todoCount);
            $sheet->setCellValue('H' . $rowCount, (int)$habitsCount);
            $sheet->setCellValue('I' . $rowCount, (int)$screeningCount);
            $sheet->setCellValue('J' . $rowCount, (int)$chatCount);
            $sheet->setCellValue('K' . $rowCount, (int)$total);
            $rowCount++;
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'user_activity_' . now()->format('Y-m-d_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Helper function to convert column number to letter (A, B, C, ..., AA, AB, etc.)
     */
    private function getColumnLetter($columnNumber)
    {
        $letter = '';
        while ($columnNumber > 0) {
            $temp = ($columnNumber - 1) % 26;
            $letter = chr($temp + 65) . $letter;
            $columnNumber = ($columnNumber - $temp - 1) / 26;
        }
        return $letter;
    }
}
