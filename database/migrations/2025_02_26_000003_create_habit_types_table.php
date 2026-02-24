<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus foreign key habit_type_id dari habits jika ada
        if (Schema::hasColumn('habits', 'habit_type_id')) {
            Schema::table('habits', function (Blueprint $table) {
                $table->dropForeign(['habit_type_id']);
                $table->dropColumn('habit_type_id');
            });
        }

        // Hapus tabel habit_types jika ada
        Schema::dropIfExists('habit_types');
    }

    public function down(): void
    {
        // Tidak perlu rollback — fitur ini dihapus permanen
    }
};
