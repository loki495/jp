<?php

use App\Models\PracticeSet;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('home');

    Route::view('/tts', 'tts')->name('tts');
    Volt::route('/words', 'words.index')->name('words.index');
    Volt::route('/words/add', 'words.edit')->name('words.create');
    Volt::route('/words/edit/{wordId?}', 'words.edit')->name('words.edit');
    Volt::route('/words/flashcards', 'words.flashcards')->name('words.flashcards');
    Volt::route('/kana', 'kana.index')->name('kana.index');
    Volt::route('/kana/practice', 'kana.practice')->name('kana.practice');
    Volt::route('/particles', 'particles.index')->name('particles.index');
});

Route::get('/api/practice-lists', function () {
    return response()->json(PracticeSet::available_sets());
});

Route::get('/api/practice-lists/{name}', function ($name) {
    return response()->json(new PracticeSet($name)->words());
});

Route::post('/api/practice-lists/{name}', function ($name) {
    return response()->json(new PracticeSet($name)->set_words(request('words')));
});

require __DIR__.'/auth.php';

Route::get('/test', function () {
    return;
    $kanji = \App\Models\Kana::find(732);
    dd($kanji->toArray());
});

Route::get('/convert-kanji-csv', function () {
    return;
    $url = 'https://raw.githubusercontent.com/kanjialive/kanji-data-media/master/language-data/ka_data.csv';
    $in = fopen($url, 'r');
    $out = fopen(storage_path('app/kanji_full_with_romaji.csv'), 'w');

    $headers = fgetcsv($in);

    // Adjust columns if naming differs slightly
    fputcsv($out, [
        'kanji',
        'onyomi',
        'kunyomi',
        'meanings',
    ]);

    while ($row = fgetcsv($in)) {

        $kanji = $row[0];
        $onyomi = $row[7];
        $onyomi_romaji = $row[8];
        $kunyomi = $row[5];
        $kunyomi_romaji = $row[6];
        $meanings = collect(json_decode($row[9]))
            ->map(function ($meaning) {
                return $meaning[0] . '(' . $meaning[1] . ')';
            })->implode(', ');

        $data = [
            $kanji,
            $onyomi . ' (' . $onyomi_romaji . ')',
            $kunyomi . ' (' . $kunyomi_romaji . ')',
            $meanings,
        ];
        fputcsv($out, $data);
    }

    fclose($in);
    fclose($out);

    return 'Output saved to storage/app/kanji_full_with_romaji.csv';
});
