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
        Schema::table('journals', function (Blueprint $table) {
            $table->string('title', 200)->after('id');
            $table->enum('type', ['TEXT', 'TODO_LIST', 'HABITS_TRACKER'])->default('TEXT')->after('title');
            $table->text('content')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn(['title', 'type']);
            $table->text('content')->nullable(false)->change();
        });
    }
};
