<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('school_name');
            $table->string('school_npsn')->nullable();
            $table->text('school_address')->nullable();
            $table->string('school_phone')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'school_npsn']);
            $table->unique(['user_id', 'status'], 'teacher_registration_user_status_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_registrations');
    }
};
