<?php

declare(strict_types=1);

use App\Models\Kana;

function kanji(string $kana, string $onyomi, string $kunyomi): Kana
{
    // Direct property assignment, not mass assignment: this is a pure Unit test, so the
    // app never boots and Model::unguard() (in AppServiceProvider) never runs.
    $model = new Kana;
    $model->kana = $kana;
    $model->onyomi = $onyomi;
    $model->kunyomi = $kunyomi;

    return $model;
}

it('prefers kunyomi when the kanji is followed by okurigana', function (): void {
    $kanji = kanji('小', onyomi: 'ショウ (shou)', kunyomi: 'ちい、ちいさい、こ、お (chii, chiisai, ko, o)');

    $romaji = $kanji->guessRomajiForKanjiInWord(['kana' => '小さい', 'romaji' => 'chiisai']);

    expect($romaji)->toBe('chii');
});

it('falls back to the shortest known reading when neither onyomi nor kunyomi matches the word', function (): void {
    // 水's own onyomi (sui) and kunyomi (mizu) are both real readings, but "mizu" here is
    // read as kunyomi despite there being no okurigana, which the onyomi/kunyomi preference
    // heuristic doesn't predict for standalone kanji words; it falls through to this.
    $kanji = kanji('水', onyomi: 'スイ (sui)', kunyomi: 'みず (mizu)');

    $romaji = $kanji->guessRomajiForKanjiInWord(['kana' => '水', 'romaji' => 'mizu']);

    expect($romaji)->toBe('mizu');
});

it('returns null when the kanji is not in the word at all', function (): void {
    $kanji = kanji('水', onyomi: 'スイ (sui)', kunyomi: 'みず (mizu)');

    $romaji = $kanji->guessRomajiForKanjiInWord(['kana' => '大きい', 'romaji' => 'ookii']);

    expect($romaji)->toBeNull();
});

it('can surface the literal "n/a" placeholder as a reading (known limitation, see Dibs #384)', function (): void {
    // 曜's kunyomi is seeded as the literal "(n/a)" placeholder, matching real data
    // (database/database.sqlite) for kanji with no kun'yomi reading. parseReadings() turns
    // that into ['' => 'n/a'], and the final fallback loop only checks the kana key's
    // length (<=2, and '' qualifies), not whether it's actually a reading — so it wins.
    $kanji = kanji('曜', onyomi: 'ヨウ (you)', kunyomi: ' (n/a)');

    $romaji = $kanji->guessRomajiForKanjiInWord(['kana' => '日曜日', 'romaji' => 'nichiyoubi']);

    expect($romaji)->toBe('n/a');
});

it('can guess the wrong reading for compound/irregular words (known limitation, see Dibs #384)', function (): void {
    // 日's plain onyomi/kunyomi list doesn't include the irregular "ni" reading used in
    // 日本 (nihon), so the heuristic falls back to the shortest known reading ("hi") instead
    // of the actually-correct one. Pinned here as current behavior, not correct behavior.
    $kanji = kanji('日', onyomi: 'ニチ、ジツ (nichi, jitsu)', kunyomi: 'ひ、か (hi, ka)');

    $romaji = $kanji->guessRomajiForKanjiInWord(['kana' => '日本', 'romaji' => 'nihon']);

    expect($romaji)->toBe('hi');
});
