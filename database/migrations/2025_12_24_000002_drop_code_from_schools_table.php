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
        if (Schema::hasTable('schools') && Schema::hasColumn('schools', 'code')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->dropUnique(['code']);
                $table->dropColumn('code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('schools') && ! Schema::hasColumn('schools', 'code')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->string('code')->unique()->nullable();
            });
        }
    }
};
