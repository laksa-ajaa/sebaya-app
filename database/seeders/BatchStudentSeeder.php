<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BatchStudentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = [
            ['name' => 'ABDURRAHMAN ARIF', 'email' => 'abdurrahmanarif@sebaya.com', 'password' => 'AbdurrahmanSRT30'],
            ['name' => 'AHMAD ABDUL ROZAQ', 'email' => 'ahmadabdulrozaq@sebaya.com', 'password' => 'AhmadSRT30'],
            ['name' => 'AIDIL SAPUTRA NASUTION', 'email' => 'aidilsaputranasution@sebaya.com', 'password' => 'AidilSRT30'],
            ['name' => 'ALDI IRWAN SYAHPUTRA', 'email' => 'aldiirwansyahputra@sebaya.com', 'password' => 'AldiSRT30'],
            ['name' => 'ALDINO', 'email' => 'aldino@sebaya.com', 'password' => 'AldinoSRT30'],
            ['name' => 'ANDRE PRANATA SIMBOLON', 'email' => 'andrepranatasimbolon@sebaya.com', 'password' => 'AndreSRT30'],
            ['name' => 'ANINDYA PUTRI R LUBIS', 'email' => 'anindyaputrirlubis@sebaya.com', 'password' => 'AnindyaSRT30'],
            ['name' => 'ARYYA WIJAYA PRATAMA', 'email' => 'aryyawijayapratama@sebaya.com', 'password' => 'AryyaSRT30'],
            ['name' => 'AXEL FEBRIANO MARPAUNG', 'email' => 'axelfebrianomarpaung@sebaya.com', 'password' => 'AxelSRT30'],
            ['name' => 'BILZA SRI WULANDARI', 'email' => 'bilzasriwulandari@sebaya.com', 'password' => 'BilzaSRT30'],
            ['name' => 'BIMA CANDRA KIRANA', 'email' => 'bimacandrakirana@sebaya.com', 'password' => 'BimaSRT30'],
            ['name' => 'BONA HANDIKA TAMPUBOLON', 'email' => 'bonahandikatampubolon@sebaya.com', 'password' => 'BonaSRT30'],
            ['name' => 'CHOKY TAMBUNAN', 'email' => 'chokytambunan@sebaya.com', 'password' => 'ChokySRT30'],
            ['name' => 'CINTA', 'email' => 'cinta@sebaya.com', 'password' => 'CintaSRT30'],
            ['name' => 'CINTA FEBRI YOLA', 'email' => 'cintafebriyola@sebaya.com', 'password' => 'CintaSRT30'],
            ['name' => 'DAFFA AR ROFI', 'email' => 'daffaarrofi@sebaya.com', 'password' => 'DaffaSRT30'],
            ['name' => 'DEBY LESTARI', 'email' => 'debylestari@sebaya.com', 'password' => 'DebySRT30'],
            ['name' => 'DESY MULIYED', 'email' => 'desymuliyed@sebaya.com', 'password' => 'DesySRT30'],
            ['name' => 'ENON ZEBUA', 'email' => 'enonzebua@sebaya.com', 'password' => 'EnonSRT30'],
            ['name' => 'FAHREZA RAMADHAN', 'email' => 'fahrezaramadhan@sebaya.com', 'password' => 'FahrezaSRT30'],
            ['name' => 'FIKRI AL BUKHARY', 'email' => 'fikrialbukhary@sebaya.com', 'password' => 'FikriSRT30'],
            ['name' => 'GEOVA RAMADHAN', 'email' => 'geovaramadhan@sebaya.com', 'password' => 'GeovaSRT30'],
            ['name' => 'GRATIA PAULINA TAMPUBOLON', 'email' => 'gratiapaulinatampubolon@sebaya.com', 'password' => 'GratiaSRT30'],
            ['name' => 'HALVI YULAINI', 'email' => 'halviyulaini@sebaya.com', 'password' => 'HalviSRT30'],
            ['name' => 'HANIF', 'email' => 'hanif@sebaya.com', 'password' => 'HanifSRT30'],
            ['name' => 'JON. PILIP SITOHANG', 'email' => 'jon.pilipsitohang@sebaya.com', 'password' => 'Jon.SRT30'],
            ['name' => 'JUWITA RAMADHANI', 'email' => 'juwitaramadhani@sebaya.com', 'password' => 'JuwitaSRT30'],
            ['name' => 'LIDYA SARAGIH', 'email' => 'lidyasaragih@sebaya.com', 'password' => 'LidyaSRT30'],
            ['name' => 'M BAKTI SOLEH', 'email' => 'mbaktisoleh@sebaya.com', 'password' => 'MSRT30'],
            ['name' => 'MARSYAH SILVIA', 'email' => 'marsyahsilvia@sebaya.com', 'password' => 'MarsyahSRT30'],
            ['name' => 'MHD ABID RAMADAN', 'email' => 'mhdabidramadan@sebaya.com', 'password' => 'MhdSRT30'],
            ['name' => 'MUHAMMAD FAHRI', 'email' => 'muhammadfahri@sebaya.com', 'password' => 'MuhammadSRT30'],
            ['name' => 'MUHAMMAD SURYADI', 'email' => 'muhammadsuryadi@sebaya.com', 'password' => 'MuhammadSRT30'],
            ['name' => 'MUHAMMAD SHAJID HARFIE HARAHAP', 'email' => 'muhammadshjidharfieharahap@sebaya.com', 'password' => 'MuhammadSRT30'],
            ['name' => 'NUOVAL AHMAD', 'email' => 'nuovalahmad@sebaya.com', 'password' => 'NuovalSRT30'],
            ['name' => 'NUR AMAL AULIA FAJRIAH BR BERUTU', 'email' => 'nuramaluliafajriahbrberutu@sebaya.com', 'password' => 'NurSRT30'],
            ['name' => 'NURSAHIRA', 'email' => 'nursahira@sebaya.com', 'password' => 'NursahiraSRT30'],
            ['name' => 'NURUL NABILA', 'email' => 'nurulnabila@sebaya.com', 'password' => 'NurulSRT30'],
            ['name' => 'RAFA ALFIANSYAH', 'email' => 'rafaalfiansyah@sebaya.com', 'password' => 'RafaSRT30'],
            ['name' => 'RISKA AMALIA', 'email' => 'riskaamalia@sebaya.com', 'password' => 'RiskaSRT30'],
            ['name' => 'RISKI RAMADAN', 'email' => 'riskiramadan@sebaya.com', 'password' => 'RiskiSRT30'],
            ['name' => 'RIVALDI PANJAITAN', 'email' => 'rivaldipanjaitan@sebaya.com', 'password' => 'RivaldiSRT30'],
            ['name' => 'RIZKY PRATAMA PURBA', 'email' => 'rizkypratamapurba@sebaya.com', 'password' => 'RizkySRT30'],
            ['name' => 'SITI ZAHRA KHAILANI', 'email' => 'sitizahrakhailani@sebaya.com', 'password' => 'SitiSRT30'],
            ['name' => 'SOFYA LESTARI', 'email' => 'sofyalestari@sebaya.com', 'password' => 'SofyaSRT30'],
            ['name' => 'TRI YANNA AL THAF UNNISA', 'email' => 'triyannaalthafunnisa@sebaya.com', 'password' => 'TriSRT30'],
            ['name' => 'YEHEZKIEL MARULI TUA NAPITUPULU', 'email' => 'yehezkielmarulituanapitupulu@sebaya.com', 'password' => 'YehezkielSRT30'],
            ['name' => 'ZIAH HERNITA', 'email' => 'ziahhernita@sebaya.com', 'password' => 'ZiahSRT30'],
            ['name' => 'ZULKHAIDIR', 'email' => 'zulkhaidir@sebaya.com', 'password' => 'ZulkhaidirSRT30'],
        ];

        foreach ($students as $student) {
            $username = explode('@', $student['email'])[0];
            $name = ucwords(strtolower($student['name']));

            $user = User::updateOrCreate(
                ['email' => $student['email']],
                [
                    'name' => $name,
                    'username' => $username,
                    'password' => $student['password'],
                    'role' => 'user',
                    'mode' => 'reguler',
                    'email_verified_at' => now(),
                    'otp_verified_at' => now(),
                ]
            );

            if ($user->wasRecentlyCreated) {
                $this->command->info("✅ Created: {$name}");
            } else {
                $this->command->info("🔄 Updated: {$name}");
            }
        }
    }
}
