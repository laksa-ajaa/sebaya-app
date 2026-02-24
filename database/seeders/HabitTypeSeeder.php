<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HabitType;

class HabitTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $habitTypes = [
            [
                'name' => 'Minum Air Mineral',
                'description' => 'Minum Air Mineral 2 liter',
            ],
            [
                'name' => 'Istirahat',
                'description' => 'Istirahat 8 Jam',
            ],
            [
                'name' => 'Olahraga',
                'description' => 'Olahraga minimal 15 menit',
            ],
            [
                'name' => 'Waktu Tenang',
                'description' => 'Relaksasi 10 - 15 Menit',
            ],
            [
                'name' => 'Jurnal Singkat',
                'description' => 'Menulis perasaan 1 - 3 kalimat',
            ],
            [
                'name' => 'Afirmasi Positif',
                'description' => 'Menulis afirmasi dan motivasi',
            ],
            [
                'name' => 'Produktivitas',
                'description' => 'Belajar minimal 30 menit',
            ],
            [
                'name' => 'Membaca',
                'description' => 'Membaca minimal 15 menit',
            ],
        ];

        foreach ($habitTypes as $type) {
            HabitType::firstOrCreate(
                ['name' => $type['name']],
                ['description' => $type['description']]
            );
        }
    }
}
