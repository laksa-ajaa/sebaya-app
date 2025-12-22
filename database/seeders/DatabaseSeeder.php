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
        // User::factory(10)->create();

        // Cek apakah user sudah ada, jika belum buat user baru
        $user = User::first();
        
        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'username' => 'testuser',
                'email' => 'test@example.com',
                'whatsapp_number' => '6281234567890',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'otp_verified_at' => now(),
            ]);
        }

        // Seed journal entries with examples
        $this->call([
            JournalSeeder::class,
        ]);
    }
}
