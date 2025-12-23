<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@sebaya.com'],
            [
                'name' => 'Admin Sebaya',
                'username' => 'admin',
                'email' => 'admin@sebaya.com',
                'password' => 'admin123',
                'whatsapp_number' => '6281234567890',
                'school_code' => 'ADMIN-SCHOOL',
                'role' => 'admin',
                'email_verified_at' => now(),
                'otp_verified_at' => now(),
            ]
        );

        if ($admin->wasRecentlyCreated) {
            $this->command->info('✅ Admin created: admin@sebaya.com / admin123');
        } else {
            $this->command->warn('⚠️  Admin already exists: admin@sebaya.com');
        }

        // Buat Guru
        $guru = User::firstOrCreate(
            ['email' => 'guru@sebaya.com'],
            [
                'name' => 'Guru Sebaya',
                'username' => 'guru',
                'email' => 'guru@sebaya.com',
                'password' => 'guru123',
                'whatsapp_number' => '6281234567891',
                'school_code' => 'GURU-SCHOOL',
                'role' => 'teacher',
                'email_verified_at' => now(),
                'otp_verified_at' => now(),
            ]
        );

        if ($guru->wasRecentlyCreated) {
            $this->command->info('✅ Guru created: guru@sebaya.com / guru123');
        } else {
            $this->command->warn('⚠️  Guru already exists: guru@sebaya.com');
        }
    }
}
