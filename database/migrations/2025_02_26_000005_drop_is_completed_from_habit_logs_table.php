<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('habit_logs', function (Blueprint $table) {
            $table->dropColumn('is_completed');
        });
    }

    public function down(): void
    {
        Schema::table('habit_logs', function (Blueprint $table) {
            $table->boolean('is_completed')->default(true)->after('date');
        });
    }
};
