<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HiraganaSeeder extends Seeder
{
    public function run(): void
    {
        $hiragana = [
            // Basic
            ['kana' => 'あ', 'romaji' => 'a'],
            ['kana' => 'い', 'romaji' => 'i'],
            ['kana' => 'う', 'romaji' => 'u'],
            ['kana' => 'え', 'romaji' => 'e'],
            ['kana' => 'お', 'romaji' => 'o'],

            ['kana' => 'か', 'romaji' => 'ka'],
            ['kana' => 'き', 'romaji' => 'ki'],
            ['kana' => 'く', 'romaji' => 'ku'],
            ['kana' => 'け', 'romaji' => 'ke'],
            ['kana' => 'こ', 'romaji' => 'ko'],

            ['kana' => 'さ', 'romaji' => 'sa'],
            ['kana' => 'し', 'romaji' => 'shi'],
            ['kana' => 'す', 'romaji' => 'su'],
            ['kana' => 'せ', 'romaji' => 'se'],
            ['kana' => 'そ', 'romaji' => 'so'],

            ['kana' => 'た', 'romaji' => 'ta'],
            ['kana' => 'ち', 'romaji' => 'chi'],
            ['kana' => 'つ', 'romaji' => 'tsu'],
            ['kana' => 'て', 'romaji' => 'te'],
            ['kana' => 'と', 'romaji' => 'to'],

            ['kana' => 'な', 'romaji' => 'na'],
            ['kana' => 'に', 'romaji' => 'ni'],
            ['kana' => 'ぬ', 'romaji' => 'nu'],
            ['kana' => 'ね', 'romaji' => 'ne'],
            ['kana' => 'の', 'romaji' => 'no'],

            ['kana' => 'は', 'romaji' => 'ha'],
            ['kana' => 'ひ', 'romaji' => 'hi'],
            ['kana' => 'ふ', 'romaji' => 'fu'],
            ['kana' => 'へ', 'romaji' => 'he'],
            ['kana' => 'ほ', 'romaji' => 'ho'],

            ['kana' => 'ま', 'romaji' => 'ma'],
            ['kana' => 'み', 'romaji' => 'mi'],
            ['kana' => 'む', 'romaji' => 'mu'],
            ['kana' => 'め', 'romaji' => 'me'],
            ['kana' => 'も', 'romaji' => 'mo'],

            ['kana' => 'や', 'romaji' => 'ya'],
            ['kana' => 'ゆ', 'romaji' => 'yu'],
            ['kana' => 'よ', 'romaji' => 'yo'],

            ['kana' => 'ら', 'romaji' => 'ra'],
            ['kana' => 'り', 'romaji' => 'ri'],
            ['kana' => 'る', 'romaji' => 'ru'],
            ['kana' => 'れ', 'romaji' => 're'],
            ['kana' => 'ろ', 'romaji' => 'ro'],

            ['kana' => 'わ', 'romaji' => 'wa'],
            ['kana' => 'を', 'romaji' => 'wo'],
            ['kana' => 'ん', 'romaji' => 'n'],

            // Dakuten
            ['kana' => 'が', 'romaji' => 'ga'],
            ['kana' => 'ぎ', 'romaji' => 'gi'],
            ['kana' => 'ぐ', 'romaji' => 'gu'],
            ['kana' => 'げ', 'romaji' => 'ge'],
            ['kana' => 'ご', 'romaji' => 'go'],

            ['kana' => 'ざ', 'romaji' => 'za'],
            ['kana' => 'じ', 'romaji' => 'ji'],
            ['kana' => 'ず', 'romaji' => 'zu'],
            ['kana' => 'ぜ', 'romaji' => 'ze'],
            ['kana' => 'ぞ', 'romaji' => 'zo'],

            ['kana' => 'だ', 'romaji' => 'da'],
            ['kana' => 'ぢ', 'romaji' => 'ji (di)'],
            ['kana' => 'づ', 'romaji' => 'zu (du)'],
            ['kana' => 'で', 'romaji' => 'de'],
            ['kana' => 'ど', 'romaji' => 'do'],

            ['kana' => 'ば', 'romaji' => 'ba'],
            ['kana' => 'び', 'romaji' => 'bi'],
            ['kana' => 'ぶ', 'romaji' => 'bu'],
            ['kana' => 'べ', 'romaji' => 'be'],
            ['kana' => 'ぼ', 'romaji' => 'bo'],

            // Handakuten
            ['kana' => 'ぱ', 'romaji' => 'pa'],
            ['kana' => 'ぴ', 'romaji' => 'pi'],
            ['kana' => 'ぷ', 'romaji' => 'pu'],
            ['kana' => 'ぺ', 'romaji' => 'pe'],
            ['kana' => 'ぽ', 'romaji' => 'po'],

            // Small kana
            ['kana' => 'ゃ', 'romaji' => 'small ya'],
            ['kana' => 'ゅ', 'romaji' => 'small yu'],
            ['kana' => 'ょ', 'romaji' => 'small yo'],
            ['kana' => 'っ', 'romaji' => 'small tsu'],
            ['kana' => 'ぁ', 'romaji' => 'small a'],
            ['kana' => 'ぃ', 'romaji' => 'small i'],
            ['kana' => 'ぅ', 'romaji' => 'small u'],
            ['kana' => 'ぇ', 'romaji' => 'small e'],
            ['kana' => 'ぉ', 'romaji' => 'small o'],
        ];

        foreach ($hiragana as &$symbol) {
            $symbol['learned'] = false;
            $symbol['created_at'] = now();
            $symbol['updated_at'] = now();
        }

        DB::table('hiraganas')->insert($hiragana);
    }
}
