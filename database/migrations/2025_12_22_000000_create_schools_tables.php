<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->comment('Kode unik sekolah, bisa dikaitkan dengan users.school_code');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        // Admin yang mengatur sekolah tertentu
        Schema::create('school_admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['school_id', 'user_id'], 'school_admin_unique');
        });

        // Guru yang terdaftar di sekolah
        Schema::create('school_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['school_id', 'teacher_id'], 'school_teacher_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_teachers');
        Schema::dropIfExists('school_admins');
        Schema::dropIfExists('schools');
    }
};


