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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('name'); // contoh: "Kelas 7A"
            $table->string('grade')->nullable(); // contoh: "7", "8"
            $table->timestamps();

            $table->index(['school_id', 'grade']);
        });

        // Relasi guru - kelas (guru mengajar kelas tertentu)
        Schema::create('class_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['class_id', 'teacher_id'], 'class_teacher_unique');
        });

        // Relasi siswa - kelas
        // Di sini siswa direpresentasikan sebagai user dengan role 'user'
        Schema::create('class_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->unique(['class_id', 'student_id'], 'class_student_unique');
            $table->index(['student_id', 'class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_students');
        Schema::dropIfExists('class_teacher');
        Schema::dropIfExists('classes');
    }
};
