<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_package_id')->constrained()->cascadeOnDelete();
            $table->text('question_text');
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->index('screening_package_id');
            $table->index(['screening_package_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_questions');
    }
};
