<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    protected $fillable = ['romaji', 'kana', 'meaning', 'learned'];
}
