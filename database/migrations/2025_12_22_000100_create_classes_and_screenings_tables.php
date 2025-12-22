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

        // Hasil screening siswa
        Schema::create('screenings', function (Blueprint $table) {
            $table->id();
            // siswa yang di-screening (user dengan role 'user')
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            // kelas tempat siswa terdaftar saat screening
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');
            // guru yang melakukan / memeriksa screening (user dengan role 'teacher')
            $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('screening_date');
            $table->json('result')->nullable()->comment('Isi hasil screening dalam bentuk JSON');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'class_id', 'screening_date'], 'screenings_student_class_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screenings');
        Schema::dropIfExists('class_students');
        Schema::dropIfExists('class_teacher');
        Schema::dropIfExists('classes');
    }
};
