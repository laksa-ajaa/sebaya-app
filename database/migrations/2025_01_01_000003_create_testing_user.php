<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->insert([
            'name' => 'Testing User',
            'username' => 'testing_user',
            'whatsapp_number' => '6281111111111',
            'school_code' => 'TEST-SCHOOL',
            'role' => 'user',
            'email' => 'testing@example.com',
            'email_verified_at' => now(),
            'otp_verified_at' => now(),
            'password' => Hash::make('password123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->where('email', 'testing@example.com')
            ->delete();
    }
};
