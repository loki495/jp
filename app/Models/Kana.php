<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kana extends Model
{
    function guessRomajiForKanjiInWord($word) {
        $kanjiPos = mb_strpos($word['kana'], $this->kana);
        if ($kanjiPos === false) return null;

        // All kana-romaji pairs
        $kunyomi = self::parseReadings($this->kunyomi);
        $onyomi = self::parseReadings($this->onyomi);

        // Try kunyomi first if word has kana after kanji (okurigana), otherwise prefer onyomi
        $hasOkurigana = mb_strlen($word['kana']) > $kanjiPos + 1 && preg_match('/\p{Hiragana}/u', mb_substr($word['kana'], $kanjiPos + 1, 1));

        $candidates = $hasOkurigana ? $kunyomi : $onyomi;

        // Sort readings by kana length ascending → prioritize more isolated (e.g., 'ちい' over 'ちいさい')
        uksort($candidates, fn($a, $b) => mb_strlen($a) <=> mb_strlen($b));

        foreach ($candidates as $kana => $romaji) {
            if (!$romaji) continue;
            if (str_starts_with($word['romaji'], $romaji)) {
                // Map kana to this kanji only: trim off okurigana
                // We assume the kana part related to the kanji matches the same number of kana as the matched kana
                // BUT we only want to return the romaji that represents the kanji itself, not the trailing kana

                // Heuristic: if the matched kana reading has >1 kana, we try to find the shortest base (like 'ちい')
                if (mb_strlen($kana) > 1) {
                    foreach ($kunyomi as $baseKana => $baseRomaji) {
                        if (mb_strlen($baseKana) == 1 || mb_strlen($baseKana) < mb_strlen($kana)) {
                            if (str_starts_with($romaji, $baseRomaji)) {
                                return $baseRomaji;
                            }
                        }
                    }
                }

                // If already short or no better match found, return as-is
                return $romaji;
            }
        }

        // fallback to first single-kanji kunyomi or onyomi
        foreach (array_merge($kunyomi, $onyomi) as $kana => $romaji) {
            if (mb_strlen($kana) <= 2 && $romaji) return $romaji;
        }
    }


    private static function parseReadings($readingStr) {
        [$kanaStr, $romajiStr] = explode('(', $readingStr);
        $kanaList = array_map('trim', explode('、', trim($kanaStr)));
        $romajiList = array_map('trim', explode(',', rtrim($romajiStr, ')')));

        $map = [];
        foreach ($kanaList as $i => $kana) {
            $map[$kana] = $romajiList[$i] ?? null;
        }

        return $map;
    }

}
