<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScreeningPackageSeeder extends Seeder
{
    public function run(): void
    {
        // Create DASS-21 Package
        $dass21 = DB::table('screening_packages')->insertGetId([
            'code' => 'DASS21',
            'name' => 'Depression Anxiety Stress Scale (DASS-21)',
            'description' => 'Skala 21 item untuk mengukur depresi, kecemasan, dan stres dalam 7 hari terakhir.',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Dimensions
        $dimensions = [
            ['code' => 'D', 'name' => 'Depression', 'multiplier' => 2],
            ['code' => 'A', 'name' => 'Anxiety', 'multiplier' => 2],
            ['code' => 'S', 'name' => 'Stress', 'multiplier' => 2],
        ];

        $dimensionIds = [];
        foreach ($dimensions as $dimension) {
            $dimensionIds[$dimension['code']] = DB::table('screening_dimensions')->insertGetId([
                'screening_package_id' => $dass21,
                'code' => $dimension['code'],
                'name' => $dimension['name'],
                'description' => "Mengukur {$dimension['name']}",
                'multiplier' => $dimension['multiplier'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // DASS-21 Questions (Bahasa Indonesia – valid)
        $questions = [
            ['order' => 1,  'dimension' => 'S', 'text' => 'Saya merasa bahwa diri saya menjadi marah karena hal-hal sepele.'],
            ['order' => 2,  'dimension' => 'A', 'text' => 'Saya merasa mulut saya sering kering.'],
            ['order' => 3,  'dimension' => 'D', 'text' => 'Saya sama sekali tidak dapat merasakan perasaan positif.'],
            ['order' => 4,  'dimension' => 'A', 'text' => 'Saya mengalami kesulitan bernafas (misalnya sering terengah-engah atau tidak dapat bernafas padahal tidak melakukan aktivitas fisik).'],
            ['order' => 5,  'dimension' => 'D', 'text' => 'Saya sepertinya tidak kuat lagi untuk melakukan suatu kegiatan.'],
            ['order' => 6,  'dimension' => 'S', 'text' => 'Saya cenderung bereaksi berlebihan terhadap suatu situasi.'],
            ['order' => 7,  'dimension' => 'A', 'text' => 'Saya merasa gemetar (misalnya pada tangan).'],
            ['order' => 8,  'dimension' => 'A', 'text' => 'Saya merasa telah menghabiskan banyak energi disaat merasa cemas.'],
            ['order' => 9,  'dimension' => 'A', 'text' => 'Saya merasa khawatir dengan situasi dimana saya mungkin menjadi panik dan mempermalukan diri sendiri.'],
            ['order' => 10, 'dimension' => 'D', 'text' => 'Saya merasa tidak ada hal yang dapat diharapkan di masa depan.'],
            ['order' => 11, 'dimension' => 'S', 'text' => 'Saya sedang merasa gelisah.'],
            ['order' => 12, 'dimension' => 'S', 'text' => 'Saya merasa sulit untuk bersantai.'],
            ['order' => 13, 'dimension' => 'D', 'text' => 'Saya merasa sedih dan tertekan.'],
            ['order' => 14, 'dimension' => 'S', 'text' => 'Saya sulit untuk sabar dalam menghadapi gangguan terhadap hal yang sedang saya lakukan.'],
            ['order' => 15, 'dimension' => 'A', 'text' => 'Saya merasa saya hampir panik.'],
            ['order' => 16, 'dimension' => 'D', 'text' => 'Saya tidak merasa antusias dalam hal apapun.'],
            ['order' => 17, 'dimension' => 'D', 'text' => 'Saya merasa bahwa saya tidak berharga sebagai seorang manusia.'],
            ['order' => 18, 'dimension' => 'S', 'text' => 'Saya merasa bahwa saya mudah tersinggung.'],
            ['order' => 19, 'dimension' => 'A', 'text' => 'Saya menyadari perubahan denyut jantung walaupun tidak sehabis melakukan aktivitas fisik.'],
            ['order' => 20, 'dimension' => 'A', 'text' => 'Saya merasa takut tanpa alasan yang jelas.'],
            ['order' => 21, 'dimension' => 'D', 'text' => 'Saya merasa bahwa hidup tidak bermanfaat.'],
        ];

        $questionIds = [];
        foreach ($questions as $question) {
            $qid = DB::table('screening_questions')->insertGetId([
                'screening_package_id' => $dass21,
                'question_text' => $question['text'],
                'order' => $question['order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $questionIds[] = $qid;

            DB::table('screening_question_dimensions')->insert([
                'screening_question_id' => $qid,
                'screening_dimension_id' => $dimensionIds[$question['dimension']],
                'weight' => 1,
            ]);
        }

        // Options (0–3)
        $options = [
            ['label' => 'Tidak sesuai dengan saya sama sekali', 'value' => 0],
            ['label' => 'Sesuai dengan saya sampai tingkat tertentu', 'value' => 1],
            ['label' => 'Sesuai dengan saya sampai batas yang dapat dipertimbangkan', 'value' => 2],
            ['label' => 'Sangat sesuai dengan saya', 'value' => 3],
        ];

        foreach ($questionIds as $questionId) {
            foreach ($options as $index => $option) {
                DB::table('screening_options')->insert([
                    'screening_question_id' => $questionId,
                    'label' => $option['label'],
                    'value' => $option['value'],
                    'order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
