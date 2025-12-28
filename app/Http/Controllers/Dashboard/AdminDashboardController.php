<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Journal;
use App\Models\MoodCheck;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        // Statistik pengguna
        $totalUsers = User::count();
        $totalStudents = User::where('role', 'user')->count();
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
            $query->where('role', $role);
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
        $totalStudents = User::where('role', 'user')->count();
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

    public function schools()
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $search = request()->get('search', '');
        $perPage = request()->get('per_page', 15);
        $query = School::query();

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
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        // Do not generate or use a school code; create school with provided data
        School::create($validated);

        return redirect()->route('admin.schools')->with('success', 'Sekolah berhasil ditambahkan');
    }

    public function sekolahUpdate(Request $request, $id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $school = School::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
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

        $school = School::with('classes')->findOrFail($school_id);
        $search = request()->get('search', '');
        $grade = request()->get('grade', '');
        $perPage = request()->get('per_page', 15);

        $query = $school->classes();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($grade) {
            $query->where('grade', 'like', "%{$grade}%");
        }

        $classes = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return view('dashboard.admin.kelas', compact('school', 'classes', 'search', 'grade', 'perPage'));
    }

    public function kelasStore(Request $request, $school_id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        // Validasi bahwa sekolah exists
        $school = School::findOrFail($school_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade' => 'nullable|string|max:10',
        ]);

        // Pastikan school_id dari route digunakan, bukan dari request
        $validated['school_id'] = $school_id;

        // Generate unique class code automatically, independent from school (format: CLS-XXXXXX)
        $classPrefix = 'CLS';
        do {
            $random = Str::upper(Str::random(6));
            $generatedCode = $classPrefix . '-' . $random;
        } while (ClassModel::where('code', $generatedCode)->exists());

        $validated['code'] = $generatedCode;

        ClassModel::create($validated);

        return redirect()->route('admin.sekolah.kelas.index', $school_id)->with('success', 'Kelas berhasil ditambahkan');
    }

    public function kelasUpdate(Request $request, $school_id, $id)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $class = ClassModel::findOrFail($id);

        // Validasi bahwa kelas milik sekolah yang benar
        if ($class->school_id != $school_id) {
            abort(404, 'Kelas tidak ditemukan untuk sekolah ini');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade' => 'nullable|string|max:10',
        ]);

        // Pastikan school_id tetap sama
        $validated['school_id'] = $school_id;

        $class->update($validated);

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
