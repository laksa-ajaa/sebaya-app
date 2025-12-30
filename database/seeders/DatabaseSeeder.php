<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed users (admin dan guru)
        $this->call([
            UserSeeder::class,
        ]);

        // Seed journal entries with examples
        $this->call([
            JournalSeeder::class,
        ]);

        // Seed screening packages (DASS-21)
        $this->call([
            ScreeningPackageSeeder::class,
            ScreeningUserSessionSeeder::class,
        ]);

        // Seed mood checks sample (user id 1, last 7 days)
        $this->call([
            MoodCheckWeekSeeder::class,
        ]);
    }
}
