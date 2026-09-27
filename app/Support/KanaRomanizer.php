<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/**
 * Kana to romaji in the style the words table already uses: Hepburn-ish, long-vowel
 * mark dropped (アパート => apato), は/を written literally (ha/wo), small tsu doubles
 * the next consonant (あっち => acchi).
 */
final class KanaRomanizer
{
    private const DIGRAPHS = [
        'きゃ' => 'kya', 'きゅ' => 'kyu', 'きょ' => 'kyo',
        'しゃ' => 'sha', 'しゅ' => 'shu', 'しょ' => 'sho', 'しぇ' => 'she',
        'ちゃ' => 'cha', 'ちゅ' => 'chu', 'ちょ' => 'cho', 'ちぇ' => 'che',
        'にゃ' => 'nya', 'にゅ' => 'nyu', 'にょ' => 'nyo',
        'ひゃ' => 'hya', 'ひゅ' => 'hyu', 'ひょ' => 'hyo',
        'みゃ' => 'mya', 'みゅ' => 'myu', 'みょ' => 'myo',
        'りゃ' => 'rya', 'りゅ' => 'ryu', 'りょ' => 'ryo',
        'ぎゃ' => 'gya', 'ぎゅ' => 'gyu', 'ぎょ' => 'gyo',
        'じゃ' => 'ja', 'じゅ' => 'ju', 'じょ' => 'jo', 'じぇ' => 'je',
        'びゃ' => 'bya', 'びゅ' => 'byu', 'びょ' => 'byo',
        'ぴゃ' => 'pya', 'ぴゅ' => 'pyu', 'ぴょ' => 'pyo',
        'ふぁ' => 'fa', 'ふぃ' => 'fi', 'ふぇ' => 'fe', 'ふぉ' => 'fo',
        'てぃ' => 'ti', 'でぃ' => 'di', 'うぃ' => 'wi', 'うぇ' => 'we', 'うぉ' => 'wo',
        'ゔぁ' => 'va', 'ゔぃ' => 'vi', 'ゔぇ' => 've', 'ゔぉ' => 'vo',
    ];

    private const SINGLES = [
        'あ' => 'a', 'い' => 'i', 'う' => 'u', 'え' => 'e', 'お' => 'o',
        'か' => 'ka', 'き' => 'ki', 'く' => 'ku', 'け' => 'ke', 'こ' => 'ko',
        'さ' => 'sa', 'し' => 'shi', 'す' => 'su', 'せ' => 'se', 'そ' => 'so',
        'た' => 'ta', 'ち' => 'chi', 'つ' => 'tsu', 'て' => 'te', 'と' => 'to',
        'な' => 'na', 'に' => 'ni', 'ぬ' => 'nu', 'ね' => 'ne', 'の' => 'no',
        'は' => 'ha', 'ひ' => 'hi', 'ふ' => 'fu', 'へ' => 'he', 'ほ' => 'ho',
        'ま' => 'ma', 'み' => 'mi', 'む' => 'mu', 'め' => 'me', 'も' => 'mo',
        'や' => 'ya', 'ゆ' => 'yu', 'よ' => 'yo',
        'ら' => 'ra', 'り' => 'ri', 'る' => 'ru', 'れ' => 're', 'ろ' => 'ro',
        'わ' => 'wa', 'を' => 'wo', 'ん' => 'n',
        'が' => 'ga', 'ぎ' => 'gi', 'ぐ' => 'gu', 'げ' => 'ge', 'ご' => 'go',
        'ざ' => 'za', 'じ' => 'ji', 'ず' => 'zu', 'ぜ' => 'ze', 'ぞ' => 'zo',
        'だ' => 'da', 'ぢ' => 'ji', 'づ' => 'zu', 'で' => 'de', 'ど' => 'do',
        'ば' => 'ba', 'び' => 'bi', 'ぶ' => 'bu', 'べ' => 'be', 'ぼ' => 'bo',
        'ぱ' => 'pa', 'ぴ' => 'pi', 'ぷ' => 'pu', 'ぺ' => 'pe', 'ぽ' => 'po',
        'ゔ' => 'vu',
        'ぁ' => 'a', 'ぃ' => 'i', 'ぅ' => 'u', 'ぇ' => 'e', 'ぉ' => 'o',
    ];

    private const SMALL_TSU = 'っ';

    private const LONG_VOWEL_MARK = 'ー';

    public static function romanize(string $text): string
    {
        $chars = mb_str_split(mb_convert_kana($text, 'c'));
        $romaji = '';
        $doubleNext = false;

        for ($i = 0, $count = count($chars); $i < $count; $i++) {
            $char = $chars[$i];

            if ($char === self::SMALL_TSU) {
                $doubleNext = true;

                continue;
            }

            if ($char === self::LONG_VOWEL_MARK) {
                continue;
            }

            $digraph = $char.($chars[$i + 1] ?? '');
            if (isset(self::DIGRAPHS[$digraph])) {
                $syllable = self::DIGRAPHS[$digraph];
                $i++;
            } elseif (isset(self::SINGLES[$char])) {
                $syllable = self::SINGLES[$char];
            } else {
                throw new InvalidArgumentException("Cannot romanize '{$char}' in '{$text}'");
            }

            $romaji .= ($doubleNext ? $syllable[0] : '').$syllable;
            $doubleNext = false;
        }

        return $romaji;
    }
}
