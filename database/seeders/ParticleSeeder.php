<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParticleSeeder extends Seeder
{
    public function run()
    {
        DB::table('kanas')->insert([
            // === Core Particles ===
            ['type'=>'particle','kana'=>'は','romaji'=>'wa','meaning'=>'topic marker (as for…)','examples'=>'今日は土曜日です (Kyou wa doyoubi desu)'],
            ['type'=>'particle','kana'=>'が','romaji'=>'ga','meaning'=>'subject marker / but','examples'=>'だれが来ますか (Dare ga kimasu ka)'],
            ['type'=>'particle','kana'=>'を','romaji'=>'o','meaning'=>'direct object marker','examples'=>'ご飯を食べます (Gohan o tabemasu)'],
            ['type'=>'particle','kana'=>'に','romaji'=>'ni','meaning'=>'to / at (time/location)','examples'=>'学校に行きます (Gakkou ni ikimasu)'],
            ['type'=>'particle','kana'=>'で','romaji'=>'de','meaning'=>'at / by (location/method)','examples'=>'図書館で勉強します (Toshokan de benkyou shimasu)'],
            ['type'=>'particle','kana'=>'へ','romaji'=>'e','meaning'=>'towards (destination)','examples'=>'東京へ行きます (Toukyou e ikimasu)'],
            ['type'=>'particle','kana'=>'と','romaji'=>'to','meaning'=>'and / with / quote marker','examples'=>'友達と話す (Tomodachi to hanasu)'],
            ['type'=>'particle','kana'=>'や','romaji'=>'ya','meaning'=>'and (non-exhaustive list)','examples'=>'りんごやバナナを食べる (Ringo ya banana o taberu)'],
            ['type'=>'particle','kana'=>'の','romaji'=>'no','meaning'=>'possession / noun linker','examples'=>'私の本 (Watashi no hon)'],
            ['type'=>'particle','kana'=>'も','romaji'=>'mo','meaning'=>'also / too','examples'=>'私も行きます (Watashi mo ikimasu)'],
            ['type'=>'particle','kana'=>'から','romaji'=>'kara','meaning'=>'from / because','examples'=>'家から駅まで歩く (Ie kara eki made aruku)'],
            ['type'=>'particle','kana'=>'まで','romaji'=>'made','meaning'=>'until / up to','examples'=>'五時まで働きます (Goji made hatarakimasu)'],
            ['type'=>'particle','kana'=>'か','romaji'=>'ka','meaning'=>'question marker','examples'=>'元気ですか？ (Genki desu ka?)'],
            ['type'=>'particle','kana'=>'ね','romaji'=>'ne','meaning'=>'confirmation tag (right?)','examples'=>'いい天気ですね (Ii tenki desu ne)'],
            ['type'=>'particle','kana'=>'よ','romaji'=>'yo','meaning'=>'assertion / emphasis','examples'=>'本当ですよ (Hontou desu yo)'],
            ['type'=>'particle','kana'=>'っけ','romaji'=>'kke','meaning'=>'recalling question (was it…?)','examples'=>'何時だっけ？ (Nanji dakke?)'],
            ['type'=>'particle','kana'=>'な','romaji'=>'na','meaning'=>'emotion / admiration','examples'=>'すごいな (Sugoi na)'],
            ['type'=>'particle','kana'=>'なあ','romaji'=>'naa','meaning'=>'emotional emphasis','examples'=>'行きたいなあ (Ikitai naa)'],

            // === Common Question Words ===
            ['type'=>'particle','kana'=>'なに','romaji'=>'nani','meaning'=>'what','examples'=>'これは何ですか？ (Kore wa nan desu ka?)'],
            ['type'=>'particle','kana'=>'いつ','romaji'=>'itsu','meaning'=>'when','examples'=>'いつ行きますか？ (Itsu ikimasu ka?)'],
            ['type'=>'particle','kana'=>'どこ','romaji'=>'doko','meaning'=>'where','examples'=>'どこに住んでいますか？ (Doko ni sundeimasu ka?)'],
            ['type'=>'particle','kana'=>'だれ','romaji'=>'dare','meaning'=>'who','examples'=>'誰が来ますか？ (Dare ga kimasu ka?)'],
            ['type'=>'particle','kana'=>'なぜ','romaji'=>'naze','meaning'=>'why (formal)','examples'=>'なぜ遅れたのですか？ (Naze okureta no desu ka?)'],
            ['type'=>'particle','kana'=>'どうして','romaji'=>'doushite','meaning'=>'why (casual)','examples'=>'どうして行かないの？ (Doushite ikanai no?)'],
            ['type'=>'particle','kana'=>'どう','romaji'=>'dou','meaning'=>'how','examples'=>'どうやって行きますか？ (Dou yatte ikimasu ka?)'],
            ['type'=>'particle','kana'=>'いくら','romaji'=>'ikura','meaning'=>'how much','examples'=>'これはいくらですか？ (Kore wa ikura desu ka?)'],
            ['type'=>'particle','kana'=>'いくつ','romaji'=>'ikutsu','meaning'=>'how many (generic count)','examples'=>'りんごはいくつありますか？ (Ringo wa ikutsu arimasu ka?)'],
            ['type'=>'particle','kana'=>'どちら','romaji'=>'dochira','meaning'=>'which one / which way','examples'=>'どちらがいいですか？ (Dochira ga ii desu ka?)'],
            ['type'=>'particle','kana'=>'どれ','romaji'=>'dore','meaning'=>'which (among 3+)','examples'=>'どれがあなたの車ですか？ (Dore ga anata no kuruma desu ka?)'],
            ['type'=>'particle','kana'=>'なん','romaji'=>'nan','meaning'=>'what (alt. form before certain consonants)','examples'=>'何時ですか？ (Nanji desu ka?)']
        ]);
    }
}

