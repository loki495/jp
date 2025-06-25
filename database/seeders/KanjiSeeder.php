<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kana;

class KanjiSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/kanji_full_with_romaji.csv');

        if (!file_exists($path)) {
            $this->command->error('CSV file not found at: ' . $path);
            return;
        }

        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle); // Read the first row as headers

            while (($data = fgetcsv($handle)) !== false) {
                $row = array_combine($headers, $data);

                if (!$row || empty($row['kanji'])) {
                    continue;
                }

                $row['kunyomi'] = str_replace('(n/a) ', '', $row['kunyomi']);
                $row['onyomi'] = str_replace('(n/a) ', '', $row['onyomi']);

                Kana::updateOrCreate(
                    ['kana' => $row['kanji']],
                    [
                        'type' => 'kanji',
                        'meaning' => $row['meanings'] ?? '',
                        'onyomi' => $row['onyomi'] ?? '',
                        'kunyomi' => $row['kunyomi'] ?? '',
                        'romaji'  => $row['romaji'] ?? '',
                        'learned' => false,
                    ]
                );
            }

            fclose($handle);
            $this->command->info('Kanji seeding complete.');
        } else {
            $this->command->error('Unable to open the file: ' . $path);
        }
    }
}
