<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MoodCheckWeekSeeder extends Seeder
{
    /**
     * Seed mood checks for user id 1 over the last 7 days.
     */
    public function run(): void
    {
        $userId = 1;
        $today = Carbon::today();

        // Sample moods for a week (0 = today, 6 = six days ago)
        $samples = [
            ['level' => 4, 'text' => 'Feeling productive and calm today.'],
            ['level' => 3, 'text' => 'Average day, keeping pace with tasks.'],
            ['level' => 5, 'text' => 'Great energy and very positive mood.'],
            ['level' => 2, 'text' => 'A bit tired, planning some rest.'],
            ['level' => 3, 'text' => 'Steady mood, staying consistent.'],
            ['level' => 4, 'text' => 'Optimistic after completing goals.'],
            ['level' => 2, 'text' => 'Low energy, focusing on recovery.'],
        ];

        foreach ($samples as $index => $sample) {
            $date = $today->copy()->subDays($index)->toDateString();
            DB::table('mood_checks')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'date' => $date,
                ],
                [
                    'mood_level' => $sample['level'],
                    'ai_response' => $sample['text'],
                    'updated_at' => now(),
                    'created_at' => $date . ' 00:00:00',
                ]
            );
        }
    }
}
