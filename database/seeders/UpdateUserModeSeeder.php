<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UpdateUserModeSeeder extends Seeder
{
    /**
     * Run the database seeder.
     * 
     * This seeder updates the mode of all users who are enrolled in classes
     * to 'student'. This is useful for migrating existing data after adding
     * the mode feature.
     */
    public function run(): void
    {
        // Get all user IDs that are enrolled in at least one class
        $enrolledUserIds = DB::table('class_students')
            ->distinct()
            ->pluck('student_id');

        if ($enrolledUserIds->isEmpty()) {
            $this->command->info('No enrolled students found.');
            return;
        }

        // Update mode to 'student' for all enrolled users with role 'user'
        $updated = User::whereIn('id', $enrolledUserIds)
            ->where('role', 'user')
            ->update(['mode' => 'student']);

        $this->command->info("Updated {$updated} user(s) mode to 'student'.");

        // Show summary
        $this->command->newLine();
        $this->command->info('Summary:');
        $this->command->table(
            ['Status', 'Count'],
            [
                ['Total enrolled students', $enrolledUserIds->count()],
                ['Updated to student mode', $updated],
            ]
        );
    }
}
