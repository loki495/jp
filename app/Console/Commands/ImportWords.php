<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Word;
use App\Support\KanaRomanizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use JsonException;

class ImportWords extends Command
{
    protected $signature = 'words:import
        {file : Path to the Duolingo export, a JSON list of [kana, meaning] pairs}
        {--romaji= : Path to a JSON object of {kana: romaji} overrides, required for kanji and other non-kana entries}
        {--dry-run : Report what would change without writing}';

    protected $description = 'Mark the words in a Duolingo export as learned, updating meanings and adding missing words';

    public function handle(): int
    {
        try {
            $entries = $this->readEntries((string) $this->argument('file'));
            $romajiPath = $this->option('romaji');
            $overrides = $this->readOverrides(is_string($romajiPath) ? $romajiPath : null);
            $plan = $this->plan($entries, $overrides);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            '%d entries: %d existing, %d new%s',
            count($entries),
            count($plan['existing']),
            count($plan['new']),
            $this->option('dry-run') ? ' (dry run, nothing written)' : '',
        ));

        if ($this->option('dry-run')) {
            $this->table(['kana', 'romaji', 'meaning'], array_map(
                fn (array $row): array => [$row['kana'], $row['romaji'], $row['meaning']],
                $plan['new'],
            ));

            return self::SUCCESS;
        }

        DB::transaction(function () use ($plan): void {
            foreach ($plan['existing'] as $row) {
                $row['word']->update(['meaning' => $row['meaning'], 'learned' => true]);
            }

            foreach ($plan['new'] as $row) {
                Word::create([
                    'kana' => $row['kana'],
                    'romaji' => $row['romaji'],
                    'meaning' => $row['meaning'],
                    'learned' => true,
                ]);
            }
        });

        return self::SUCCESS;
    }

    /**
     * @return list<array{string, string}>
     */
    private function readEntries(string $path): array
    {
        $data = $this->readJson($path);

        foreach ($data as $index => $entry) {
            if (! is_array($entry) || count($entry) !== 2 || ! is_string($entry[0] ?? null) || ! is_string($entry[1] ?? null)) {
                throw new InvalidArgumentException("Entry #{$index} in {$path} is not a [kana, meaning] pair");
            }
        }

        /** @var list<array{string, string}> $data */
        return $data;
    }

    /**
     * @return array<string, string>
     */
    private function readOverrides(?string $path): array
    {
        if ($path === null) {
            return [];
        }

        $data = $this->readJson($path);

        foreach ($data as $kana => $romaji) {
            if (! is_string($romaji)) {
                throw new InvalidArgumentException("Romaji for '{$kana}' in {$path} is not a string");
            }
        }

        /** @var array<string, string> $data */
        return $data;
    }

    /**
     * @return array<mixed>
     */
    private function readJson(string $path): array
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("File not found: {$path}");
        }

        try {
            $data = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException("{$path} is not valid JSON: {$e->getMessage()}", $e->getCode(), previous: $e);
        }

        if (! is_array($data)) {
            throw new InvalidArgumentException("{$path} must contain a JSON list or object");
        }

        return $data;
    }

    /**
     * @param  list<array{string, string}>  $entries
     * @param  array<string, string>  $overrides
     * @return array{existing: list<array{word: Word, meaning: string}>, new: list<array{kana: string, romaji: string, meaning: string}>}
     */
    private function plan(array $entries, array $overrides): array
    {
        $existing = [];
        $new = [];
        $unreadable = [];
        $words = Word::query()->whereIn('kana', array_column($entries, 0))->get()->keyBy('kana');

        foreach ($entries as [$kana, $meaning]) {
            $word = $words->get($kana);
            if ($word !== null) {
                $existing[] = ['word' => $word, 'meaning' => $meaning];

                continue;
            }

            try {
                $romaji = $overrides[$kana] ?? KanaRomanizer::romanize($kana);
            } catch (InvalidArgumentException) {
                $unreadable[] = $kana;

                continue;
            }

            $new[] = ['kana' => $kana, 'romaji' => $romaji, 'meaning' => $meaning];
        }

        if ($unreadable !== []) {
            throw new InvalidArgumentException(
                'No romaji for these new words, add them to the --romaji file: '.implode(', ', $unreadable),
            );
        }

        return ['existing' => $existing, 'new' => $new];
    }
}
