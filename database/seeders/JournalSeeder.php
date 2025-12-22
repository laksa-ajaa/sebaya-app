<?php

namespace Database\Seeders;

use App\Models\Journal;
use App\Models\TodoItem;
use App\Models\Habit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class JournalSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil atau buat user untuk contoh
        $user = User::first();
        
        if (!$user) {
            $this->command->warn('No user found. Please create a user first or run UserSeeder.');
            return;
        }

        // 1. Journal dengan tipe TEXT
        $textJournal = Journal::create([
            'user_id' => $user->id,
            'title' => 'Refleksi Hari Ini',
            'content' => 'Hari ini saya merasa sangat produktif. Saya berhasil menyelesaikan tugas dengan baik dan merasa puas dengan pencapaian hari ini. Pagi ini saya bangun lebih awal dan langsung mulai bekerja dengan semangat. Setelah makan siang, saya melanjutkan pekerjaan dan berhasil menyelesaikan beberapa tugas penting.',
            'type' => 'TEXT',
            'date' => Carbon::now()->subDays(1)->toDateString(),
        ]);

        $this->command->info("Created TEXT journal: {$textJournal->title}");

        // 2. Journal dengan tipe TODO_LIST
        $todoJournal = Journal::create([
            'user_id' => $user->id,
            'title' => 'Daftar Tugas Minggu Ini',
            'content' => '',
            'type' => 'TODO_LIST',
            'date' => Carbon::now()->toDateString(),
        ]);

        // Tambahkan todo items
        $todoItems = [
            [
                'text' => 'Belajar matematika',
                'is_completed' => false,
                'reminder_time' => Carbon::now()->addDays(1)->setTime(18, 0, 0),
                'reminder_label' => 'Senin, 18.00',
                'order' => 0,
            ],
            [
                'text' => 'Olahraga pagi',
                'is_completed' => true,
                'reminder_time' => null,
                'reminder_label' => null,
                'order' => 1,
            ],
            [
                'text' => 'Mengerjakan tugas bahasa Indonesia',
                'is_completed' => false,
                'reminder_time' => Carbon::now()->addDays(2)->setTime(14, 0, 0),
                'reminder_label' => 'Selasa, 14.00',
                'order' => 2,
            ],
            [
                'text' => 'Bersih-bersih kamar',
                'is_completed' => false,
                'reminder_time' => null,
                'reminder_label' => null,
                'order' => 3,
            ],
        ];

        foreach ($todoItems as $item) {
            TodoItem::create([
                'journal_id' => $todoJournal->id,
                ...$item,
            ]);
        }

        $this->command->info("Created TODO_LIST journal: {$todoJournal->title} with " . count($todoItems) . " todo items");

        // 3. Journal dengan tipe HABITS_TRACKER
        $habitsJournal = Journal::create([
            'user_id' => $user->id,
            'title' => 'Kebiasaan Sehat',
            'content' => '',
            'type' => 'HABITS_TRACKER',
            'date' => Carbon::now()->toDateString(),
        ]);

        // Tambahkan habits
        $habits = [
            [
                'name' => 'Minum 8 gelas air',
                'description' => 'Target harian untuk hidrasi',
                'is_completed_today' => true,
                'streak' => 5,
            ],
            [
                'name' => 'Meditasi 10 menit',
                'description' => 'Setiap pagi sebelum aktivitas',
                'is_completed_today' => false,
                'streak' => 3,
            ],
            [
                'name' => 'Olahraga 30 menit',
                'description' => 'Jogging atau senam ringan',
                'is_completed_today' => true,
                'streak' => 7,
            ],
            [
                'name' => 'Membaca buku 30 menit',
                'description' => 'Membaca sebelum tidur',
                'is_completed_today' => false,
                'streak' => 2,
            ],
            [
                'name' => 'Tidur sebelum jam 22.00',
                'description' => 'Untuk menjaga pola tidur yang sehat',
                'is_completed_today' => true,
                'streak' => 4,
            ],
        ];

        foreach ($habits as $habit) {
            Habit::create([
                'journal_id' => $habitsJournal->id,
                ...$habit,
            ]);
        }

        $this->command->info("Created HABITS_TRACKER journal: {$habitsJournal->title} with " . count($habits) . " habits");

        // 4. Tambahkan beberapa journal TEXT lagi untuk variasi
        $textJournals = [
            [
                'title' => 'Pelajaran Hari Ini',
                'content' => 'Hari ini saya belajar tentang sejarah Indonesia. Sangat menarik mengetahui perjuangan para pahlawan untuk kemerdekaan. Saya merasa lebih menghargai jasa-jasa mereka setelah mempelajari sejarah ini.',
                'date' => Carbon::now()->subDays(2)->toDateString(),
            ],
            [
                'title' => 'Rencana Weekend',
                'content' => 'Untuk weekend ini, saya berencana untuk pergi ke perpustakaan dan membaca beberapa buku baru. Selain itu, saya juga ingin menghabiskan waktu bersama keluarga dan menonton film bersama.',
                'date' => Carbon::now()->subDays(3)->toDateString(),
            ],
        ];

        foreach ($textJournals as $journalData) {
            Journal::create([
                'user_id' => $user->id,
                'title' => $journalData['title'],
                'content' => $journalData['content'],
                'type' => 'TEXT',
                'date' => $journalData['date'],
            ]);
        }

        $this->command->info("Created " . count($textJournals) . " additional TEXT journals");

        // 5. Tambahkan TODO_LIST dengan lebih banyak item
        $todoJournal2 = Journal::create([
            'user_id' => $user->id,
            'title' => 'Persiapan Ujian',
            'content' => '',
            'type' => 'TODO_LIST',
            'date' => Carbon::now()->subDays(1)->toDateString(),
        ]);

        $todoItems2 = [
            [
                'text' => 'Review materi matematika',
                'is_completed' => true,
                'order' => 0,
            ],
            [
                'text' => 'Latihan soal fisika',
                'is_completed' => true,
                'order' => 1,
            ],
            [
                'text' => 'Buat rangkuman sejarah',
                'is_completed' => false,
                'order' => 2,
            ],
            [
                'text' => 'Persiapkan alat tulis',
                'is_completed' => true,
                'order' => 3,
            ],
        ];

        foreach ($todoItems2 as $item) {
            TodoItem::create([
                'journal_id' => $todoJournal2->id,
                ...$item,
            ]);
        }

        $this->command->info("Created TODO_LIST journal: {$todoJournal2->title}");

        $this->command->info("\n✅ Journal seeder completed successfully!");
        $this->command->info("Total journals created: " . Journal::where('user_id', $user->id)->count());
        $this->command->info("Total todo items created: " . TodoItem::whereIn('journal_id', Journal::where('user_id', $user->id)->pluck('id'))->count());
        $this->command->info("Total habits created: " . Habit::whereIn('journal_id', Journal::where('user_id', $user->id)->pluck('id'))->count());
    }
}
