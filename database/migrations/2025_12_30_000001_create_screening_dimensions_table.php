<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_dimensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_package_id')->constrained()->cascadeOnDelete();
            $table->string('code'); // D, A, S
            $table->string('name'); // Depression, Anxiety, Stress
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('multiplier')->default(1); // 2 untuk DASS-21
            $table->timestamps();

            $table->unique(['screening_package_id', 'code']);
            $table->index('screening_package_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_dimensions');
    }
};
