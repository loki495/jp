<?php

declare(strict_types=1);

use App\Support\KanaRomanizer;

it('romanizes kana the way the words table does', function (string $kana, string $romaji): void {
    expect(KanaRomanizer::romanize($kana))->toBe($romaji);
})->with([
    'hiragana' => ['おにぎり', 'onigiri'],
    'katakana' => ['ピザ', 'piza'],
    'digraph' => ['でんしゃ', 'densha'],
    'small tsu doubles the consonant' => ['あっち', 'acchi'],
    'long vowel mark is dropped' => ['アパート', 'apato'],
    'literal particle spelling' => ['こんにちは', 'konnichiha'],
    'foreign sound' => ['ファンタジー', 'fantaji'],
    'empty string' => ['', ''],
]);

it('rejects characters it cannot romanize', function (string $text): void {
    KanaRomanizer::romanize($text);
})->with([
    'kanji' => ['水'],
    'latin letters' => ['Tシャツ'],
])->throws(InvalidArgumentException::class, 'Cannot romanize');
