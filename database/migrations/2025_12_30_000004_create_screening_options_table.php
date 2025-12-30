<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_question_id')->constrained()->cascadeOnDelete();
            $table->string('label'); // "Normal", "Rarely", "Sometimes", etc
            $table->unsignedSmallInteger('value'); // 0, 1, 2, 3
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->index('screening_question_id');
            $table->index(['screening_question_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_options');
    }
};
