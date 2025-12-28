<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    public function joinByCode(Request $request)
    {
        $data = $request->validate([
            'class_code' => ['required', 'string', 'max:100'],
        ]);

        $user = $request->user();

        if ($user->role !== 'user') {
            return response()->json([
                'message' => 'Hanya siswa yang dapat bergabung ke kelas.',
            ], 403);
        }

        $class = ClassModel::where('code', $data['class_code'])->first();

        if (! $class) {
            return response()->json([
                'message' => 'Kode kelas tidak ditemukan.',
            ], 404);
        }

        // Jika sudah terdaftar, tidak perlu ajukan lagi
        $alreadyJoined = $class->students()->where('users.id', $user->id)->exists();
        if ($alreadyJoined) {
            return response()->json([
                'message' => 'Anda sudah terdaftar di kelas ini.',
            ], 409);
        }

        // Tandai sebagai pending verifikasi: simpan class_code pada user
        DB::transaction(function () use ($user, $data) {
            $user->class_code = $data['class_code'];
            $user->save();
        });

        return response()->json([
            'message' => 'Permintaan bergabung terkirim. Menunggu verifikasi guru.',
            'class' => [
                'id' => $class->id,
                'name' => $class->name,
                'grade' => $class->grade,
                'code' => $class->code,
                'school_id' => $class->school_id,
            ],
        ]);
    }
}
