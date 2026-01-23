<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScreeningUserSessionSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan user id 1 ada
        $user = DB::table('users')->find(14);
        if (! $user) {
            return; // tidak ada user, skip
        }

        $package = DB::table('screening_packages')->where('code', 'DASS21')->first();
        if (! $package) {
            return; // package belum ada
        }

        $questions = DB::table('screening_questions')
            ->where('screening_package_id', $package->id)
            ->orderBy('order')
            ->get();

        if ($questions->isEmpty()) {
            return; // belum ada soal
        }

        // Cek apakah user sudah punya sesi screening untuk package ini
        $existingSession = DB::table('screening_sessions')
            ->where('user_id', $user->id)
            ->where('screening_package_id', $package->id)
            ->first();

        // Jika sudah ada, skip agar riwayat terekam
        if ($existingSession) {
            return;
        }

        // Buat sesi baru (tidak menghapus sesi lama agar history tetap terekam)
        $sessionId = DB::table('screening_sessions')->insertGetId([
            'user_id' => $user->id,
            'screening_package_id' => $package->id,
            'started_at' => now(),
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Jawaban sample: siklik nilai 0-3
        $valueCycle = [0, 1, 2, 3];

        foreach ($questions as $index => $question) {
            $value = $valueCycle[$index % count($valueCycle)];

            $option = DB::table('screening_options')
                ->where('screening_question_id', $question->id)
                ->where('value', $value)
                ->first();

            if (! $option) {
                // fallback ke option pertama bila nilai tidak ditemukan
                $option = DB::table('screening_options')
                    ->where('screening_question_id', $question->id)
                    ->orderBy('order')
                    ->first();
            }

            if (! $option) {
                continue; // tidak ada option untuk soal ini
            }

            DB::table('screening_answers')->insert([
                'screening_session_id' => $sessionId,
                'screening_question_id' => $question->id,
                'screening_option_id' => $option->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
