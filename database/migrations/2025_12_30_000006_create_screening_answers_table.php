<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('screening_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('screening_option_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['screening_session_id', 'screening_question_id'], 'sq_answer_session_question_unique');
            $table->index('screening_session_id');
            $table->index('screening_question_id');
            $table->index('screening_option_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_answers');
    }
};
