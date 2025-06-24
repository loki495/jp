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
    Volt::route('/hiragana', 'hiragana.index')->name('hiragana.index');

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
