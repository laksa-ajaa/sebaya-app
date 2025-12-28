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
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'school_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('school_code', 'class_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'class_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('class_code', 'school_code');
            });
        }
    }
};
