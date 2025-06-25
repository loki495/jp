<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

//use Illuminate\Database\Eloquent\Model;

class PracticeSet
{

    public $filename = '';
    public static $path = 'practice_sets/';

    const LEARNED_SET = 'learned';
    const HIRAGANA_SET = 'hiragana';

    public function __construct(
        public ?string $name = self::LEARNED_SET
    ) {

        if ($this->name &&
            $this->name != static::LEARNED_SET &&
            $this->name != static::HIRAGANA_SET
        ) {
            $slug = Str::slug($this->name);
            $this->filename = Storage::path(static::$path) . "{$slug}.json";

            if (!file_exists($this->filename)) {
                @mkdir(dirname($this->filename), 0777, true);
                file_put_contents($this->filename, json_encode([]));
            }
        } else {
            $this->name = $name;
        }
    }

    public static function available_sets(): array
    {
        $files = Storage::files(static::$path);

        $sets = array_merge(
            [static::LEARNED_SET],
            [static::HIRAGANA_SET],
            array_map(function ($file) {
                return basename($file, '.json');
            }, $files)
        );

        return $sets;
    }

    public function words(string $search = '') : array
    {
        if ($this->name === static::LEARNED_SET) {
            $data = Word::query()
                ->where(function ($query) use ($search) {
                    $query
                        ->where('romaji', 'like', "%{$search}%")
                        ->orWhere('kana', 'like', "%{$search}%")
                        ->orWhere('meaning', 'like', "%{$search}%");
                })
                ->where('learned', true)
                ->get();
        } elseif ($this->name === static::HIRAGANA_SET) {
            $data = Kana::query()
                ->where(function ($query) use ($search) {
                    $query
                        ->where('romaji', 'like', "%{$search}%")
                        ->orWhere('kana', 'like', "%{$search}%")
                        ->orWhere('meaning', 'like', "%{$search}%");
                })
                ->where('learned', true)
                ->get();
        } else {
            $data = collect(json_decode(file_get_contents($this->filename), true) ?? []);

            $data = $data->map(function ($id) {
                return Word::findOrFail($id);
            });

            if ($search) {
                $data = $data->filter(function ($id) use ($search) {
                    $word = Word::findOrFail($id);
                    return
                        strpos($word->romaji, $search) !== false ||
                        strpos($word->kana, $search) !== false ||
                        strpos($word->meaning, $search) !== false;
                });
            }
        }

        return $data->toArray();
    }

    public function toggleWordInList($id)
    {
        $word = Word::findOrFail($id);

        if ($this->name === 'learned') {
            $word->learned = !$word->learned;
            $word->save();

            return;
        }

        $data = collect($this->words())->pluck('id')->toArray();

        if (in_array($id, $data)) {
            unset($data[array_search($id, $data)]);
        } else {
            $data[] = $id;
        }
        sort($data);

        file_put_contents($this->filename, json_encode($data, JSON_PRETTY_PRINT));
    }

}
