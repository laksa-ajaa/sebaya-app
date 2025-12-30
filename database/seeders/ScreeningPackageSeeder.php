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
            'description' => 'A 21-item self-report instrument designed to measure the negative emotional states of depression, anxiety and stress.',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create DASS-21 Dimensions
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
                'description' => "Measures {$dimension['name']}",
                'multiplier' => $dimension['multiplier'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // DASS-21 Questions
        $questions = [
            // Depression (Q: 3, 5, 10, 13, 16, 17, 21)
            ['text' => 'I found it hard to wind down', 'dimension' => 'S', 'order' => 1],
            ['text' => 'I was aware of dryness of my mouth', 'dimension' => 'A', 'order' => 2],
            ['text' => 'I couldn\'t seem to experience any positive feeling at all', 'dimension' => 'D', 'order' => 3],
            ['text' => 'I experienced breathing difficulty (eg, excessively rapid breathing, breathlessness in the absence of physical exertion)', 'dimension' => 'A', 'order' => 4],
            ['text' => 'I found it difficult to work up the initiative to do things', 'dimension' => 'D', 'order' => 5],
            ['text' => 'I tended to over-react to situations', 'dimension' => 'S', 'order' => 6],
            ['text' => 'I experienced trembling (eg, in the hands)', 'dimension' => 'A', 'order' => 7],
            ['text' => 'I was worried about situations in which I might panic and make a fool of myself', 'dimension' => 'A', 'order' => 8],
            ['text' => 'I felt that I had nothing to look forward to', 'dimension' => 'D', 'order' => 9],
            ['text' => 'I found myself getting agitated', 'dimension' => 'S', 'order' => 10],
            ['text' => 'I found it difficult to relax', 'dimension' => 'S', 'order' => 11],
            ['text' => 'I was depressed and had very little interest in anything', 'dimension' => 'D', 'order' => 12],
            ['text' => 'I was impatient', 'dimension' => 'S', 'order' => 13],
            ['text' => 'I felt scared without any good reason', 'dimension' => 'A', 'order' => 14],
            ['text' => 'I felt that I was not worth much as a person', 'dimension' => 'D', 'order' => 15],
            ['text' => 'I was intolerant of anything that kept me from getting on with what I was doing', 'dimension' => 'S', 'order' => 16],
            ['text' => 'I felt terrified', 'dimension' => 'A', 'order' => 17],
            ['text' => 'I could see nothing in the future to be hopeful about', 'dimension' => 'D', 'order' => 18],
            ['text' => 'I felt that life was meaningless', 'dimension' => 'D', 'order' => 19],
            ['text' => 'I found myself getting very irritated', 'dimension' => 'S', 'order' => 20],
            ['text' => 'I was worried about my health', 'dimension' => 'A', 'order' => 21],
        ];

        $questionIds = [];
        foreach ($questions as $question) {
            $dimension = $question['dimension'];
            $questionIds[$question['order']] = DB::table('screening_questions')->insertGetId([
                'screening_package_id' => $dass21,
                'question_text' => $question['text'],
                'order' => $question['order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Link question to dimension
            DB::table('screening_question_dimensions')->insert([
                'screening_question_id' => $questionIds[$question['order']],
                'screening_dimension_id' => $dimensionIds[$dimension],
                'weight' => 1,
            ]);
        }

        // Create Options (0, 1, 2, 3)
        $options = [
            ['label' => 'Did not apply to me at all', 'value' => 0],
            ['label' => 'Applied to me to some degree, or some of the time', 'value' => 1],
            ['label' => 'Applied to me to a considerable degree or a good part of time', 'value' => 2],
            ['label' => 'Applied to me very much or most of the time', 'value' => 3],
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
