<?php

namespace Database\Seeders;

use App\Models\SecurityQuestion;
use Illuminate\Database\Seeder;

class SecurityQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            'Siapa nama guru favorit Anda saat di sekolah dasar?',
            'Apa nama tim sepak bola yang anda sukai?',
            'Makanan apa yang anda sangat sukai?',
            'Apa jenis hewan peliharaan pertama Anda dan siapa namanya?',
            'Siapa actor favorite anda sampai sekarang?',
        ];

        foreach ($questions as $question) {
            SecurityQuestion::firstOrCreate([
                'question' => $question,
            ]);
        }

        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('✓ Security Questions berhasil dibuat!');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('PERTANYAAN KEAMANAN:');
        foreach ($questions as $index => $question) {
            $this->command->info(' '.($index + 1).'. '.$question);
        }
        $this->command->info('========================================');
    }
}
