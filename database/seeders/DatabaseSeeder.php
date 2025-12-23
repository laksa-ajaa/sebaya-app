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
    }
}
