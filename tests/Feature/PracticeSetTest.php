<?php

declare(strict_types=1);

use App\Models\Kana;
use App\Models\PracticeSet;
use App\Models\Word;
use Illuminate\Support\Facades\Storage;

/**
 * @return array<mixed>
 */
function readSet(string $file): array
{
    /** @var array<mixed> $decoded */
    $decoded = json_decode((string) Storage::get($file), true);

    return $decoded;
}

beforeEach(function (): void {
    Storage::fake('local');
});

it('creates an empty backing file for a new custom list', function (): void {
    new PracticeSet('Food and Drink');

    Storage::assertExists('practice_sets/food-and-drink.json');
    expect(readSet('practice_sets/food-and-drink.json'))->toBe([]);
});

it('reuses an existing backing file instead of overwriting it', function (): void {
    Storage::put('practice_sets/numbers.json', (string) json_encode([1, 2, 3]));

    new PracticeSet('Numbers');

    expect(readSet('practice_sets/numbers.json'))->toBe([1, 2, 3]);
});

it('does not create a backing file for the built-in learned or hiragana sets', function (): void {
    new PracticeSet(PracticeSet::LEARNED_SET);
    new PracticeSet(PracticeSet::HIRAGANA_SET);

    Storage::assertDirectoryEmpty('practice_sets');
});

it('lists the built-in sets plus one entry per custom list file', function (): void {
    Storage::put('practice_sets/numbers.json', '[]');
    Storage::put('practice_sets/food-and-drink.json', '[]');

    expect(PracticeSet::available_sets())->toEqualCanonicalizing([
        PracticeSet::LEARNED_SET,
        PracticeSet::HIRAGANA_SET,
        'numbers',
        'food-and-drink',
    ]);
});

it('returns only learned words for the learned set, filtered by search', function (): void {
    Word::create(['kana' => 'すし', 'romaji' => 'sushi', 'meaning' => 'sushi', 'learned' => true]);
    Word::create(['kana' => 'みず', 'romaji' => 'mizu', 'meaning' => 'water', 'learned' => true]);
    Word::create(['kana' => 'ねこ', 'romaji' => 'neko', 'meaning' => 'cat', 'learned' => false]);

    $words = new PracticeSet(PracticeSet::LEARNED_SET)->words();
    expect(collect($words)->pluck('romaji')->all())->toEqualCanonicalizing(['sushi', 'mizu']);

    $filtered = new PracticeSet(PracticeSet::LEARNED_SET)->words('sushi');
    expect(collect($filtered)->pluck('romaji')->all())->toEqual(['sushi']);
});

it('returns only learned kana for the hiragana set, filtered by search', function (): void {
    Kana::create(['type' => 'hiragana', 'kana' => 'あ', 'romaji' => 'a', 'learned' => true]);
    Kana::create(['type' => 'hiragana', 'kana' => 'い', 'romaji' => 'i', 'learned' => false]);

    $kana = new PracticeSet(PracticeSet::HIRAGANA_SET)->words();
    expect(collect($kana)->pluck('romaji')->all())->toEqual(['a']);
});

it('returns the words listed in a custom set, filtered by search', function (): void {
    $sushi = Word::create(['kana' => 'すし', 'romaji' => 'sushi', 'meaning' => 'sushi', 'learned' => true]);
    $mizu = Word::create(['kana' => 'みず', 'romaji' => 'mizu', 'meaning' => 'water', 'learned' => true]);
    Storage::put('practice_sets/food-and-drink.json', (string) json_encode([$sushi->id, $mizu->id]));

    $words = new PracticeSet('Food and Drink')->words();
    expect(collect($words)->pluck('romaji')->all())->toEqualCanonicalizing(['sushi', 'mizu']);

    $filtered = new PracticeSet('Food and Drink')->words('mizu');
    expect(collect($filtered)->pluck('romaji')->all())->toEqual(['mizu']);
});

it('throws when a custom set references a word that no longer exists', function (): void {
    Storage::put('practice_sets/food-and-drink.json', (string) json_encode([999]));

    new PracticeSet('Food and Drink')->words();
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);

it('toggles a word learned on and off in the learned set', function (): void {
    $word = Word::create(['kana' => 'すし', 'romaji' => 'sushi', 'meaning' => 'sushi', 'learned' => false]);
    $set = new PracticeSet(PracticeSet::LEARNED_SET);

    $set->toggleWordInList($word->id);
    expect($word->refresh()->learned)->toBeTruthy();

    $set->toggleWordInList($word->id);
    expect($word->refresh()->learned)->toBeFalsy();
});

it('adds and removes a word from a custom set', function (): void {
    $word = Word::create(['kana' => 'すし', 'romaji' => 'sushi', 'meaning' => 'sushi', 'learned' => true]);
    $set = new PracticeSet('Food and Drink');

    $set->toggleWordInList($word->id);
    expect(readSet('practice_sets/food-and-drink.json'))->toBe([$word->id]);

    $set->toggleWordInList($word->id);
    expect(readSet('practice_sets/food-and-drink.json'))->toBe([]);
});

it('throws when toggling a word id that does not exist', function (): void {
    new PracticeSet(PracticeSet::LEARNED_SET)->toggleWordInList(999);
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);
