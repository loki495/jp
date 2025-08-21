<?php


namespace App\Http\Livewire;

use App\Models\Kana;
use Livewire\Attributes\Session;
use Livewire\Volt\Component;
use App\Models\PracticeSet;
use App\Models\Word;

new class extends Component {

    public $search = '';

    #[Session]
    public $activeSetName = '';

    public $sets = [];
    public $set_words = [];
    public $words = [];

    private ?PracticeSet $practiceSet = null;

    public function mount(): void {
        if (!$this->activeSetName) {
            $this->activeSetName = PracticeSet::LEARNED_SET;
        }
    }

    public function toggleWordInList($id)
    {
        $this->practiceSet = new PracticeSet($this->activeSetName);
        $this->practiceSet->toggleWordInList($id);
        $this->updateSetsAndWords();
    }

    public function updateSetsAndWords()
    {
        $this->practiceSet = new PracticeSet($this->activeSetName);
        $this->sets = PracticeSet::available_sets();
        unset($this->sets[array_search(PracticeSet::HIRAGANA_SET, $this->sets)]);
        $this->set_words = $this->practiceSet->words($this->search);
    }

    public function updatedActiveSetName()
    {
        $this->updateSetsAndWords();
        $words = $this->words();
        if ($this->search) {
            $words = $words->orderBy(
                '(
                    (
                        (kana like "' . $this->search . ') * 4) +
                        (romaji like "' . $this->search . ') * 4) +
                        (meaning like "' . $this->search . ') * 4) +
                    ) +
                    (
                        (kana like "' . $this->search . '%) * 2) +
                        (romaji like "' . $this->search . '%) * 2) +
                        (meaning like "' . $this->search . '%) * 2) +
                    ) +
                    0
                    ) DESC');
        }
        $words->ddRawSql();
        $words = $words->get();
        $this->dispatch('set-words-updated', [
            'words' => $words,
            'set_words' => $this->set_words,
            'sets' => $this->sets,
        ]);
    }

    public function with(): array
    {
        $this->updateSetsAndWords();

        $inSet = collect($this->set_words)
            ->map(function ($word) {
                $word['characters'] = collect(mb_str_split($word['kana']))
                    ->map(function ($char) use ($word) {
                        if ($char == 'ー') {
                            return [
                                'kana' => $char,
                                'romaji' => 'long vowel',
                            ];
                        }
                        $kana = Kana::where('kana', $char)->first();
                        if (!$kana) {
                            return [
                                'kana' => $char,
                                'romaji' => '',
                            ];
                        }
                        $romaji = $kana['romaji'];
                        if ($kana['type'] == 'kanji') {
                            $romaji = $kana->guessRomajiForKanjiInWord($word);
                        }

                        $char = [
                            'kana' => $char,
                            'romaji' => $romaji,
                        ];
                        return $char;
                    })->toArray();
                return $word;
            })->toArray();

        $this->words = Word::query()
            ->when($this->search, function ($query, $search) {
                $query
                    ->where('romaji', 'like', "%{$search}%")
                    ->orWhere('kana', 'like', "%{$search}%")
                    ->orWhere('meaning', 'like', "%{$search}%");
            })
            ->get()
            ->filter(function ($word) use ($inSet) {
                return !in_array($word->id, $inSet);
            });

        $kanas = Kana::all()
            ->mapWithKeys(function ($kana) use ($inSet) {
                return [
                    $kana['kana'] => $kana['romaji'],
                ];
            });

        return [
            'words' => $this->words,
            'set_words' => $this->set_words,
            'sets' => $this->sets,
            'all_kana' => $kanas,
        ];
    }

    public function addList($listName) {
        $this->practiceSet = new PracticeSet($listName);
        $this->activeSetName = $listName;
    }
}
?>

<div
    x-data="vocabState(@js($words), @js($set_words), @js($sets), @js($all_kana))"
    x-init="init()"
    class="max-w-6xl mx-auto px-4 py-6 dark"
>
    <h1 class="text-3xl font-bold text-center mb-8 text-white">Japanese Vocabulary Tracker</h1>

    <div class="flex items-center gap-4 mb-4">
    <label class="text-white font-semibold">Current List:</label>

    <select wire:model.live="activeSetName" class="bg-zinc-800 text-white rounded p-2" x-cloak>
        @foreach ($sets as $list)
            <option value="{{ $list }}">{{ $list }}</option>
        @endforeach
    </select>

    <button @click="if (listName = prompt('Enter list name')) $wire.addList(listName) "
            class="rounded-xl px-4 py-2 text-sm text-white bg-green-700 hover:underline cursor-pointer">
        Add New List
    </button>
    </div>

    <div class="flex justify-between mb-6 gap-4">
        <div class="flex flex-col gap-4 w-full">
            <flux:input
                x-model.debounce="search"
                placeholder="Search by romaji, kana, or meaning..."
                size="md"
                variant="filled"
                class="max-w-lg w-full"
                clearable
            />
        </div>

        <div class="flex flex-col gap-4 mb-8">
            <a href="{{ route('words.create') }}" class="w-max text-md bg-green-700 text-white rounded-xl px-4 py-2 hover:underline cursor-pointer border-none">
                Add Word
            </a>
        </div>
    </div>

    <div wire:loading wire:target="activeSetName" class="text-sm text-gray-400 mb-8">Loading word list…</div>

    <flux:separator class="mb-8" />

    <div class="mb-12" x-data="{ }">

        <div class="mb-4 flex justify-between" >
            <h2 class="text-xl font-semibold text-green-400">{{ ucwords($activeSetName) }} Set</h2>
            <button @click="inSetShow = !inSetShow" class="focus:outline-none" x-show="search.length === 0" x-cloak>
                <svg x-show="!search.length && !showInSet()" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                <svg x-show="showInSet" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
            </button>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block" x-show="inSetShow" wire:transition.scale.origin.top>
            <x-table>
                <x-slot name="head">
                    <x-table.tr>
                        <x-table.th>✓</x-table.th>
                        <x-table.th>Romaji</x-table.th>
                        <x-table.th>Kana</x-table.th>
                        <x-table.th>Meaning</x-table.th>
                        <x-table.th class="text-right">Actions</x-table.th>
                    </x-table.tr>
                </x-slot>

                <x-slot name="body">
                    <template x-for="word in filteredInSet()" :key="refreshKey + '-' + word.id">
                        <x-table.tr class="border-b border-zinc-700/60" x-cloak>
                            <x-table.td>
                                <flux:checkbox.group>
                                    <flux:checkbox x-bind:checked="1" @click.stop="toggle(word)" class="cursor-pointer" />
                                </flux:checkbox.group>
                            </x-table.td>
                            <x-table.td x-text="word.romaji" />
                            <x-table.td><x-kana/></x-table.td>
                            <x-table.td x-text="word.meaning" />
                            <x-table.td class="text-right">
                                <div class="flex items-center gap-2 justify-end">
                                    <a x-bind:href="'{{ route('words.edit') }}/' + word.id" class="text-md bg-green-700 text-white rounded-xl px-4 py-2 hover:underline cursor-pointer">
                                        Edit
                                    </a>
                                    <flux:button
                                        @click.stop="playAudio(word.kana)"
                                        icon="play"
                                        class="text-sm cursor-pointer"
                                    />
                                </div>
                            </x-table.td>
                        </x-table.tr>
                    </template>
                </x-slot>
            </x-table>
        </div>

        <div class="md:hidden space-y-4" x-show="showInSet" wire:transition.scale.origin.top>
            <template x-for="word in filteredInSet()" x-bind:key="refreshKey + '-' + word.id" x-cloak>
                <div class="bg-gray-800 text-white rounded-lg shadow border px-4 py-3">
                    <div class="flex justify-between items-start">
                        <div class="flex flex-col gap-4 grow">
                            <div class="font-semibold w-full" x-text="word.romaji"></div>
                            <div class="font-semibold w-full"><x-kana/></div>
                        </div>

                        <div class="flex flex-col gap-4 items-end">
                            <flux:button
                                @click.stop="playAudio(word.kana)"
                                icon="play"
                                class="text-sm cursor-pointer"
                            />
                            <div class="flex items-center gap-2 justify-end">
                                <a x-bind:href="'{{ route('words.edit') }}/' + word.id" class="text-md bg-green-700 text-white rounded-xl px-4 py-2 hover:underline cursor-pointer">
                                    Edit
                                </a>
                            </div>
                            <div class="flex gap-4">
                                <flux:checkbox x-bind:checked="1" @click="toggle(word)" class="cursor-pointer" /><span>In Set</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2" x-text="word.meaning"></div>
                </div>
            </template>
        </div>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-blue-400 mb-4">Not In Set</h2>
        <div class="hidden md:block">
            <x-table>
                <x-slot name="head">
                    <x-table.tr>
                        <x-table.th>✓</x-table.th>
                        <x-table.th>Romaji</x-table.th>
                        <x-table.th>Kana</x-table.th>
                        <x-table.th>Meaning</x-table.th>
                        <x-table.th class="text-right">Actions</x-table.th>
                    </x-table.tr>
                </x-slot>

                <x-slot name="body">
                    <template x-for="word, index in filteredNotInSet()" x-bind:key="refreshKey + '-' + word.id" x-cloak>
                        <x-table.tr class="border-b border-zinc-700/60">
                            <x-table.td>
                                <flux:checkbox.group>
                                    <flux:checkbox x-bind:checked="0" @click="toggle(word)" class="cursor-pointer" />
                                </flux:checkbox.group>
                            </x-table.td>
                            <x-table.td x-text="word.romaji" />
                            <x-table.td><x-kana/></x-table.td>
                            <x-table.td x-text="word.meaning" />
                            <x-table.td class="text-right">
                                <div class="flex items-center gap-2 justify-end">
                                    <a x-bind:href="'{{ route('words.edit') }}/' + word.id" class="text-md bg-green-700 text-white rounded-xl px-4 py-2 hover:underline cursor-pointer">
                                        Edit
                                    </a>
                                    <flux:button
                                        @click.stop="playAudio(word.kana)"
                                        icon="play"
                                        class="text-sm cursor-pointer"
                                    />
                                </div>
                            </x-table.td>
                        </x-table.tr>
                    </template>
                </x-slot>
            </x-table>
        </div>

        <div class="md:hidden space-y-4">
            <template x-for="word in filteredNotInSet()" x-bind:key="refreshKey + '-' + word.id" x-cloak>
                <div class="bg-gray-800 text-white rounded-lg shadow border px-4 py-3">
                    <div class="flex justify-between items-start">
                        <div class="flex flex-col gap-4 grow">
                            <div class="font-semibold w-full" x-text="word.romaji"></div>
                            <div class="font-semibold w-full"><x-kana/></div>
                            <div x-text="word.meaning"></div>
                        </div>

                        <div class="flex flex-col gap-4 items-end">
                            <flux:button
                                @click.stop="playAudio(word.kana)"
                                icon="play"
                                class="text-sm cursor-pointer"
                            />
                            <div class="flex items-center gap-2 justify-end">
                                <a x-bind:href="'{{ route('words.edit') }}/' + word.id" class="text-md bg-green-700 text-white rounded-xl px-4 py-2 hover:underline cursor-pointer">
                                    Edit
                                </a>
                            </div>
                            <div class="flex gap-4">
                                <flux:checkbox x-bind:checked="0" @click="toggle(word)" class="cursor-pointer" /><span>In Set</span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

@script
<script>
window.vocabState = function vocabState(words, inSet, sets, all_kana) {
    return {
        search: '',
        allWords: words,
        inSet: inSet,
        sets: sets,
        all_kana: all_kana,
        page: 1,
        perPage: 25,
        refreshKey: 0,
        pendingRemovals: new Set(),
        inSetShow: false,

        init() {
            window.addEventListener('set-words-updated', (e) => {
                let vars = e.detail[0];
                this.allWords = vars.words;
                this.inSet = vars.set_words;
                this.sets = vars.sets;
            });
        },

        triggerRefresh() {
            this.refreshKey++;
        },

        isWordInSet(word) {
            return this.inSet.filter(w => w.id === word.id).length > 0;
        },

        toggle(word) {

            const prev = this.isWordInSet(word);

            const wordClone = { ...word };

            if (prev) {
                new_list = this.inSet.filter(w => w.id !== word.id);
            } else {
                new_list = [...this.inSet, wordClone];
            }

            Toast('Updating word...', 'info');
            document.querySelectorAll('ui-checkbox').forEach(c => c.disabled = true);

            $nextTick(() => {
                this.inSet = new_list;

                $wire.toggleWordInList(word.id)
                    .then(() => {
                        Toast('Word updated', 'success');
                        this.triggerRefresh();
                    })
                    .catch(() => {
                        if (prev) {
                            this.inSet = [...this.inSet, wordClone];
                        } else {
                            this.inSet = this.inSet.filter(w => w.id !== word.id);
                        }
                        Toast('Update failed', 'danger');
                    })
                    .finally(() => {
                        this.pendingRemovals.delete(word.id);
                        document.querySelectorAll('ui-checkbox').forEach(c => c.disabled = false);
                        this.triggerRefresh();
                    });
            })
        },

        calcWordSearchScore(word, search) {

            if (word.romaji == 'onigiri') debugger;

            let score = 0;
            search = search.toLowerCase();

            score += word.romaji.toLowerCase() == search ? 8 : 0;
            score += word.kana == search ? 8 : 0;
            score += word.meaning.toLowerCase() == search ? 6 : 0;

            score += word.kana.startsWith(search) ? 4 : 0;
            score += word.romaji.toLowerCase().startsWith(search) ? 4 : 0;
            score += word.meaning.toLowerCase().startsWith(search) ? 2 : 0;

            return score
        },

        sortWords(words) {
            let calcFunc = this.calcWordSearchScore;
            let search = this.search;

            return words.sort(function (a, b) {
                let scoreA = calcFunc(a, search);
                let scoreB = calcFunc(b, search);

                let val = (scoreA > scoreB) ? -1 : (scoreA < scoreB) ? 1 : (a.romaji < b.romaji) ? -1 : (a.romaji > b.romaji) ? 1 : 0;;

                return val;
            })
        },

        filteredInSet() {
            let new_list = JSON.parse(JSON.stringify(this.inSet));

            new_list = new_list.filter(w =>
                !this.pendingRemovals.has(w.id) &&
                    (
                        w.romaji.toLowerCase().includes(this.search.toLowerCase()) ||
                            w.kana.toLowerCase().includes(this.search.toLowerCase()) ||
                            w.meaning.toLowerCase().includes(this.search.toLowerCase())
                    )
            );

            if (this.search) {
                new_list = this.sortWords(new_list);
            }

            return new_list;
        },

        filteredNotInSet() {
            let filtered_list =  [];

            for (let word of this.allWords) {
                if (this.isWordInSet(word)) {
                    continue;
                }

                if (
                    word.romaji.toLowerCase().includes(this.search.toLowerCase()) ||
                    word.kana.toLowerCase().includes(this.search.toLowerCase()) ||
                    word.meaning.toLowerCase().includes(this.search.toLowerCase())
                ) {
                    filtered_list.push(word);
                }

                if (filtered_list.length === this.perPage) {
                    break;
                }
            }

            filtered_list = filtered_list.slice((this.page - 1) * this.perPage, this.page * this.perPage);

            if (this.search) {
                filtered_list = this.sortWords(filtered_list)
            }

            return filtered_list;
        },

        showInSet() {
            return this.inSetShow || this.search.length > 0;
        }
    }
}
</script>
@endscript
