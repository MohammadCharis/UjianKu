<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private const QUESTION_BODIES = [
        'Hasil dari 12 + 8 × 3 adalah ...',
        'Bilangan prima terkecil yang lebih besar dari 20 adalah ...',
        'Jika x + 5 = 12, maka nilai x adalah ...',
        'Luas persegi dengan sisi 9 cm adalah ... cm².',
        'Turunan dari f(x) = 3x² + 2x adalah ...',
        'Rata-rata dari 4, 6, 8, dan 10 adalah ...',
        'Perbandingan 12 : 18 disederhanakan menjadi ...',
        'Sebuah segitiga sudutnya 45°, 45°, dan ...',
        'Nilai dari 2³ × 3² adalah ...',
        'Diskon 20% dari harga Rp250.000 adalah ...',
        'Keliling lingkaran jari-jari 7 cm adalah ... cm.',
        'Jika 3x = 27, maka x adalah ...',
        'Jelaskan perbedaan teks narasi dan teks deskripsi!',
        'Apa maksud dari kalimat efektif? Jelaskan dengan contoh!',
        'Sebutkan tiga ciri teks argumentasi!',
        'Bagaimana cara menentukan ide pokok sebuah paragraf?',
        'Uraikan langkah menyelesaikan persamaan kuadrat!',
        'Jelaskan penerapan fungsi kuadrat dalam kehidupan sehari-hari!',
        'Apa yang dimaksud dengan himpunan bagian? Berikan contoh!',
        'Jelaskan perbedaan barisan aritmetika dan geometri!',
    ];

    public function run(): void
    {
        $this->firstOrCreateUser('Administrator', 'admin@ujianku.test', User::ADMIN);
        $gurus = [
            $this->firstOrCreateUser('Guru Matematika', 'guru1@ujianku.test', User::GURU),
            $this->firstOrCreateUser('Guru Bahasa Indonesia', 'guru2@ujianku.test', User::GURU),
        ];

        $classes = [
            SchoolClass::firstOrCreate(['name' => 'X IPA 1'], ['grade' => '10']),
            SchoolClass::firstOrCreate(['name' => 'XI IPS 1'], ['grade' => '11']),
            SchoolClass::firstOrCreate(['name' => 'XII IPA 2'], ['grade' => '12']),
        ];

        $subjects = [
            Subject::firstOrCreate(['name' => 'Matematika']),
            Subject::firstOrCreate(['name' => 'Bahasa Indonesia']),
        ];

        if (Student::count() === 0) {
            foreach (range(1, 30) as $number) {
                $user = User::factory()->siswa()->create([
                    'name' => 'Siswa '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                    'email' => 'siswa'.str_pad((string) $number, 2, '0', STR_PAD_LEFT).'@ujianku.test',
                ]);

                Student::factory()->create([
                    'user_id' => $user->id,
                    'class_id' => $classes[($number - 1) % count($classes)]->id,
                    'nis' => sprintf('2024%04d', $number),
                ]);
            }
        }

        if (Question::count() === 0) {
            foreach (self::QUESTION_BODIES as $index => $body) {
                $type = match (true) {
                    $index < 12 => 'pg',
                    $index < 16 => 'isian',
                    default => 'esai',
                };

                $question = Question::factory()->create([
                    'subject_id' => $subjects[$index % count($subjects)]->id,
                    'created_by' => $gurus[$index % count($gurus)]->id,
                    'type' => $type,
                    'body' => $body,
                ]);

                if ($type !== 'pg') {
                    continue;
                }

                foreach (['A', 'B', 'C', 'D'] as $optionIndex => $label) {
                    QuestionOption::factory()->create([
                        'question_id' => $question->id,
                        'label' => $label,
                        'text' => 'Pilihan '.$label,
                        'is_correct' => $optionIndex === 0,
                    ]);
                }
            }
        }
    }

    private function firstOrCreateUser(string $name, string $email, string $role): User
    {
        return User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'role' => $role,
                'email_verified_at' => now(),
            ],
        );
    }
}
