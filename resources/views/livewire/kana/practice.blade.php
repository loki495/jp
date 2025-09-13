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
        '' => [],
        'g' => [47,48,49,50,51],
        'z' => [52,53,54,55,56],
        'd' => [57,58,59,60,61],
        '' => [],
    ];

    public $message = '';

    public $last_word = '';

    #[Session]
    public $count = 0;

    #[Session]
    public $correct = 0;

    #[Session]
    public $wrong = 0;

    #[Session]
    public $done = 0;

    #[Session]
    public $next_on_correct = false;

    public $solutions = [];

    public $max_solutions = 4;

    #[Session]
    public $solutions_tried = [];

    #[Session]
    public $selected_groups = [];

    #[Session]
    public $length = 1;

    public $max_length = 10;

    public $chosen_word;
    public $chosen_kana;
    public $chosen_romaji;

    public function mount() {
        $this->next();
    }

    public function generateWord() {
        $this->done = false;

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

        $this->last_word = $this->chosen_word;
        while ($this->last_word == $this->chosen_word) {
            $this->chosen_word = Collection::times($current_length, fn() => $selected->random());
        }
        $this->chosen_kana = $this->chosen_word->map(fn($kana) => $kana['kana'])->implode('');
        $this->chosen_romaji = $this->chosen_word->map(fn($kana) => $kana['romaji'])->implode('');

        $this->solutions = [];
        while (count($this->solutions) < $this->max_solutions - 1) {
            $solution = Collection::times($current_length, fn() => $selected->random());
            if ($solution != $this->chosen_word && !in_array($solution->map(fn($kana) => $kana['romaji'])->implode(''), $this->solutions)) {
                $this->solutions[] = $solution->map(fn($kana) => $kana['romaji'])->implode('');
            }
        }

        $this->solutions[] = $this->chosen_word->map(fn($kana) => $kana['romaji'])->implode('');
        $this->solutions = array_values($this->solutions);
        shuffle($this->solutions);

        $this->solutions_tried = [];
    }

    public function updated() {
        $this->next();
    }

    public function next() {
        $this->generateWord();
        $this->dispatch('clear-solutions');
        $this->message = '';
    }

    public function resetCount() {
        $this->count = 0;
        $this->correct = 0;
        $this->wrong = 0;
        $this->next();
    }

    public function check($solution_index) {
        $this->count++;
        $solution = $this->solutions[$solution_index];
        if ($solution == $this->chosen_romaji) {
            $this->correct++;
            $this->done = true;
            $this->message = 'Correct!';
            $this->dispatch('next');
        } else {
            $this->wrong++;
            $this->message = 'Wrong!';
        }
        $this->solutions_tried[] = $solution_index;
    }
}

?>
<div class="max-w-6xl mx-auto px-4 py-0 dark" x-data="{ show_solutions: false }" @hide-solutions.window="show_solutions = false">

    <div wire:loading class="fixed right-4 top-4"><flux:icon.loading /></div>

    <h1 class="text-3xl font-bold text-center mb-2 text-white">Kana Practice</h1>

    <div class="flex flex-col md:flex-row md:justify-between">
        <div class="mb-4 flex gap-4">
            <div>Practicing Groups:</div>
           <div>{{ empty($selected_groups) ? 'All' : strtoupper(implode(', ', $selected_groups)) }} ({{ mb_strlen($chosen_kana) }} chars / {{ $length }} max)</div>
        </div>

        <details class="mb-4 group border border-zinc-600 p-2 rounded-xl bg-zinc-700">
            <summary class="text-white font-semibold flex items-center gap-4 cursor-pointer flex md:justify-end">
                <span>Config:</span>
                <flux:icon.chevron-down class="w-4 h-4 text-zinc-500 dark:text-zinc-400 group-open:rotate-180 transition" />
            </summary>

            <flux:fieldset>
                <div class="flex gap-6 *:gap-x-2 mt-4">
                    <flux:checkbox.group wire:model.live="selected_groups" class="grid grid-cols-5">
                        @foreach ($kana_grouping as $key => $group)
                            @if (empty($group))
                            <div class="">
                            </div>
                            @else
                            <div class="border border-zinc-600 p-2 rounded-xl bg-zinc-700">
                                <flux:checkbox label="{{ strtoupper($key) }}" value="{{ $key }}" />
                            </div>
                            @endif
                        @endforeach
                    </flux:checkbox.group>
                </div>

                <div class="flex gap-4 py-4 items-center">
                    <label class="text-white font-semibold flex items-center gap-4">
                        <span>Max Length:</span>
                        <input wire:model.live.change="length" type="range" min="1" max="{{ $max_length }}" class="bg-zinc-800 text-white rounded" />
                        <span class="ml-2">{{ $length }} / {{ $max_length }}</span>
                    </label>
                </div>
                <div class="flex gap-4 py-4 items-center">
                    <flux:checkbox label="Next on Correct" wire:model="next_on_correct" />
                </div>
            </flux:fieldset>

        </details>
    </div>

    <div class="flex flex-col gap-4 justify-center items-center">
        <div class="flex gap-2 flex-wrap justify-center" x-data="{ chosen_word: @entangle('chosen_word') }">
            <template x-for="char, index in chosen_word" :key="'kana-word-' + char.id + '-' + index">
            <div class="kana group flex items-center gap-4 mb-4 p-2 rounded-xl justify-center border-b border-zinc-500 text-6xl"
               x-data="{
                    kana: char.kana,
                    romaji: char.romaji,
                    show_romaji: false
                    }"
                    @click="show_romaji = !show_romaji"
                    @clear-solutions.window="show_romaji = false">
                    <span x-show="!show_romaji" class="text-white w-full min-w-24 min-h-[10px] text-center" x-text="kana"></span>
                    <span x-show="show_romaji" class="text-white w-full min-w-24 min-h-[10px] text-center" x-cloak x-text="romaji"></span>
                </div>
            </template>
        </div>

        <button class="w-full border border-zinc-600 p-4 text-2xl rounded-xl text-white hover:bg-blue-800 cursor-pointer" @click="show_solutions = !show_solutions; if (show_solutions) setTimeout(() => fitAll(), 10)">Toggle Solutions</button>

        <div class="grid grid-cols-2 gap-4 text-center w-full" x-show="show_solutions" x-cloak>
            @foreach ($solutions as $index => $solution)
            <button class="{{ $solution }} w-full border border-zinc-600 px-4 rounded-xl text-white"
                wire:key="{{ $count . '-' . $index }}-{{ $solution }}"
                @if (!$done && !in_array($index, $solutions_tried))
                    wire:click="check('{{ $index}}')" @click="document.querySelector('.message').innerHtml = '';"
                @endif
                @if ($done && $solutions[$index] == $chosen_romaji) wire:click="next()" @endif
                :class="
                {
                'bg-blue-700 active:bg-blue-600 hover:bg-blue-800/70': {{ !$done ? 'true' : 'false' }},
                'cursor-pointer': {{ $solutions[$index] == $chosen_romaji || (!in_array($index, $solutions_tried) && !$done) ? 'true' : 'false' }},
                'bg-zinc-600': {{ $done ? 'true' : 'false' }},
                '!bg-green-700': {{ in_array($index, $solutions_tried) && $solutions[$index] == $chosen_romaji ? 'true' : 'false' }},
                '!bg-red-600': {{ in_array($index, $solutions_tried) && $solutions[$index] != $chosen_romaji ? 'true' : 'false' }},
                'hover:!bg-green-600': {{ $done && $solutions[$index] == $chosen_romaji ? 'true' : 'false' }},
                }
                "
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50"
                wire:target.except="null"
            >
                <div class="w-full h-full flex items-center justify-center max-w-4xl max-h-16 min-h-16 relative group">
                    <p class="fit-text opacity-0">
                        {{ $solution }}
                    </p>
                    @if ($done && $solutions[$index] == $chosen_romaji)
                        <span class="hidden group-hover:block absolute right-4">Next...</span>
                    @endif
                </div>
            </button>
            @endforeach
        </div>

        <div class="flex w-full justify-between">
            <div class="flex">Correct: {{ $correct }} / {{ $count }} ({{ round($correct / max(1,$count) * 100) }}%)</div>
            <div class="flex">Wrong: {{ $wrong }} / {{ $count }} ({{ round($wrong / max(1,$count) * 100) }}%)</div>
        </div>

        <div class="flex w-full p-0 message"></div>

        <div class="grid grid-cols-2 gap-8 w-full flex-wrap justify-center">
            <button wire:click="resetCount" class="active:bg-red-600 bg-red-800 text-white rounded-xl p-4 text-3xl">Reset</button>
            <button wire:click="next" class="active:bg-blue-600 bg-blue-800 text-white rounded-xl p-4 text-3xl">{{ $done ? 'Next' : 'Skip' }}</button>
            <button class="active:bg-green-600 bg-yellow-800 text-white rounded-xl p-4 text-3xl" @click="playAudio('{{ $chosen_kana }}', 0.1)">Play Slow</button>
            <button class="active:bg-green-600 bg-green-800 text-white rounded-xl p-4 text-3xl" @click="playAudio('{{ $chosen_kana }}')">Play</button>
        </div>
    </div>

</div>

<script>
function fitText(el, minSize = 12, maxSize = 200) {
    if (!el.scrollWidth) return;
    const parent = el.parentElement;
    let low = minSize;
    let high = maxSize;
    let size = minSize;

    el.style.whiteSpace = "normal"; // allow wrapping

    while (low <= high) {
        let mid = Math.floor((low + high) / 2);
        el.style.fontSize = mid + "px";

        if (el.scrollWidth <= parent.clientWidth && el.scrollHeight <= parent.clientHeight) {
            size = mid;  // fits, try bigger
            low = mid + 1;
        } else {
            high = mid - 1; // too big, try smaller
        }
    }

    el.style.fontSize = size + "px";
    console.log('showing ' + el.innerText);
    el.style.opacity = 1;
}

function fitAll() {
    document.querySelectorAll(".fit-text").forEach(el => fitText(el));
}

window.addEventListener("load", function() {
    fitAll();
    Livewire.hook('morphed', ({ el, component }) => {
        if (component.canonical.next_on_correct && component.canonical.done) {
            setTimeout(() => {
                Livewire.dispatch('hide-solutions')
                component.$wire.next();
            },100);
        }
        requestAnimationFrame(() => {
            fitAll();
        });
    });
});

window.addEventListener("resize", () => {
    clearTimeout(window._fitTimer);
    window._fitTimer = setTimeout(fitAll, 100); // debounce
});

</script>
