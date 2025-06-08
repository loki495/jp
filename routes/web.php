<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'vocab-list')->name('dashboard');
Volt::route('/word/add', 'edit-word')->name('word.create');
Volt::route('/word/edit/{wordId}', 'edit-word')->name('word.edit');

