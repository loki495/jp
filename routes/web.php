<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('dashboard');
Route::view('/tts', 'tts')->name('tts');
Volt::route('/words', 'words.index')->name('words.index');
Volt::route('/words/add', 'words.edit')->name('words.create');
Volt::route('/words/edit/{wordId}', 'words.edit')->name('words.edit');
Volt::route('/words/flashcards', 'words.flashcards')->name('words.flashcards');

