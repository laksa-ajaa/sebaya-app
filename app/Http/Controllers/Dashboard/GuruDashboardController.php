<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\School;
use App\Models\User;
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
}
