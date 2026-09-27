<?php

declare(strict_types=1);

use App\Models\Word;

function writeJson(mixed $data): string
{
    $path = tempnam(sys_get_temp_dir(), 'words');
    file_put_contents($path, is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE));

    return $path;
}

it('marks existing words learned, takes the new meaning and adds missing words', function (): void {
    Word::create(['kana' => 'すし', 'romaji' => 'sushi', 'meaning' => 'sushi, vinegared rice', 'learned' => false]);
    $file = writeJson([['すし', 'sushi'], ['ピザ', 'pizza'], ['水', 'water']]);
    $romaji = writeJson(['水' => 'mizu']);

    $this->artisan('words:import', ['file' => $file, '--romaji' => $romaji])
        ->expectsOutputToContain('3 entries: 1 existing, 2 new')
        ->assertSuccessful();

    expect(Word::where('kana', 'すし')->sole())
        ->learned->toBeTruthy()
        ->meaning->toBe('sushi')
        ->romaji->toBe('sushi');
    expect(Word::where('kana', 'ピザ')->sole())
        ->learned->toBeTruthy()
        ->romaji->toBe('piza');
    expect(Word::where('kana', '水')->sole()->romaji)->toBe('mizu');
});

it('leaves words missing from the file untouched', function (): void {
    Word::create(['kana' => 'ねこ', 'romaji' => 'neko', 'meaning' => 'cat', 'learned' => true]);

    $this->artisan('words:import', ['file' => writeJson([['いぬ', 'dog']])])->assertSuccessful();

    expect(Word::where('kana', 'ねこ')->sole()->learned)->toBeTruthy();
});

it('is idempotent', function (): void {
    $file = writeJson([['ピザ', 'pizza']]);

    $this->artisan('words:import', ['file' => $file])->assertSuccessful();
    $this->artisan('words:import', ['file' => $file])
        ->expectsOutputToContain('1 existing, 0 new')
        ->assertSuccessful();

    expect(Word::count())->toBe(1);
});

it('writes nothing on a dry run', function (): void {
    $this->artisan('words:import', ['file' => writeJson([['ピザ', 'pizza']]), '--dry-run' => true])
        ->expectsOutputToContain('dry run')
        ->assertSuccessful();

    expect(Word::count())->toBe(0);
});

it('fails with a clear message and writes nothing for a new word it cannot romanize', function (): void {
    $file = writeJson([['ピザ', 'pizza'], ['水', 'water']]);

    $this->artisan('words:import', ['file' => $file])
        ->expectsOutputToContain('No romaji for these new words, add them to the --romaji file: 水')
        ->assertFailed();

    expect(Word::count())->toBe(0);
});

it('fails when the export file is missing', function (): void {
    $this->artisan('words:import', ['file' => '/nonexistent/words.json'])
        ->expectsOutputToContain('File not found')
        ->assertFailed();
});

it('fails when the export is not valid JSON', function (): void {
    $this->artisan('words:import', ['file' => writeJson('{not json')])
        ->expectsOutputToContain('is not valid JSON')
        ->assertFailed();

    expect(Word::count())->toBe(0);
});

it('fails when an entry is not a [kana, meaning] pair', function (mixed $entry): void {
    $this->artisan('words:import', ['file' => writeJson([['ピザ', 'pizza'], $entry])])
        ->expectsOutputToContain('Entry #1')
        ->assertFailed();

    expect(Word::count())->toBe(0);
})->with([
    'too short' => [['ねこ']],
    'not a list' => ['ねこ'],
    'non-string meaning' => [['ねこ', 5]],
]);

it('fails when the romaji overrides file is malformed', function (): void {
    $this->artisan('words:import', [
        'file' => writeJson([['水', 'water']]),
        '--romaji' => writeJson(['水' => 5]),
    ])
        ->expectsOutputToContain('is not a string')
        ->assertFailed();

    expect(Word::count())->toBe(0);
});
