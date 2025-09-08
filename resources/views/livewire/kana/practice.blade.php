<?php


namespace App\Http\Livewire;

use App\Models\Kana;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Session;
use Livewire\Volt\Component;

new class extends Component {

    public $kana_grouping = [
        'a' => [1,2,3,4,5],
        'k' => [6,7,8,9,10],
        's' => [11,12,13,14,15],
        't' => [16,17,18,19,20],
        'n' => [21,22,23,24,25],
    ];

    #[Session]
    public $selected_groups = [];

    #[Session]
    public $length = 1;

    public $max_length = 10;

    public $chosen_word;

    public function mount() {
        $this->next();
    }

    public function generateWord() {
        $groups = collect($this->kana_grouping)->mapWithKeys(function ($list, $key) {
            return [$key => Kana::query()->whereIn('id', $list)->get()->toArray() ];
        });

        if ($this->selected_groups) {
            $selected = $groups->only($this->selected_groups);
        } else {
            $selected = $groups;
        }

        $current_length = rand(1, $this->length);

        $selected = $selected->flatMap(fn($group) => $group);

        $this->chosen_word = Collection::times($current_length, fn() => $selected->random());
        $this->chosen_kana = $this->chosen_word->map(fn($kana) => $kana['kana'])->implode('');
    }

    public function updated() {
        $this->next();
    }

    public function next() {
        $this->generateWord();
        $this->dispatch('reset-alpine-open');
    }

    #[Computed]
    public function chosen_word_kana() {
        return $this->chosen_word->map(fn($kana) => $kana['kana'])->implode('');
    }
}

?>
<div
    class="max-w-6xl mx-auto px-4 py-6 dark"
>
    <div wire:loading class="fixed right-4 top-4"><flux:icon.loading /></div>

    <h1 class="text-3xl font-bold text-center mb-8 text-white">Kana Practice</h1>

    <div class="mb-4">
        Practicing Groups: {{ empty($selected_groups) ? 'All' : strtoupper(implode(', ', $selected_groups)) }} ({{ count($chosen_word) }} chars / {{ $length }} max)
    </div>

    <flux:fieldset>
        <div class="flex gap-6 *:gap-x-2">
            <flux:checkbox.group wire:model.live="selected_groups" class="flex [&>[data-flux-field]:last-child]:mb-4!">
                @foreach (array_keys($kana_grouping) as $group)
                    <div class="border border-zinc-600 p-2 rounded-xl bg-zinc-700">
                        <flux:checkbox label="{{ strtoupper($group) }}" value="{{ $group }}" />
                    </div>
                @endforeach
            </flux:checkbox.group>
        </div>

        <div class="flex gap-4 py-4 items-centea">
            <label class="text-white font-semibold flex items-center gap-4">
                <span>Max Length:</span>
                <input wire:model.live.change="length" type="range" min="1" max="{{ $max_length }}" class="bg-zinc-800 text-white rounded" />
                <span class="ml-2">{{ $length }} / {{ $max_length }}</span>
            </label>
    </flux:fieldset>

    <div class="flex flex-col gap-4 justify-center items-center">
        <div class="flex gap-2 flex-wrap justify-center" x-data="{ chosen_word: @entangle('chosen_word') }">
            <template x-for="char, index in chosen_word" :key="'kana-word-' + char.id + '-' + index">
            <div class="kana group flex items-center gap-4 mb-4 p-2 rounded-xl justify-center border-b border-zinc-500 text-6xl"
               x-data="{
                    kana: char.kana,
                    romaji: char.romaji,
                    show: false
                    }" @click="show = !show"
                    @reset-alpine-open.window="show = false">
                    <span x-show="!show" class="text-white w-full min-w-24 text-center" x-text="kana"></span>
                    <span x-show="show" class="text-white w-full min-w-24 text-center" x-cloak x-text="romaji"></span>
                </div>
            </template>
        </div>

        <div class="flex gap-8">
            <button class="active:bg-green-600 bg-yellow-800 text-white rounded-xl p-4 text-3xl" @click="playAudio('{{ $this->chosen_word_kana }}', 0.1)">Play Slow</button>
            <button class="active:bg-green-600 bg-green-800 text-white rounded-xl p-4 text-3xl" @click="playAudio('{{ $this->chosen_word_kana }}')">Play</button>
            <button wire:click="next" class="active:bg-blue-600 bg-blue-800 text-white rounded-xl p-4 text-3xl">Next</button>
        </div>
    </div>

</div>
