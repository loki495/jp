<?php

namespace App\Http\Livewire;

use Livewire\Attributes\Session;
use Livewire\Volt\Component;
use App\Models\Word;

new class extends Component {

    #[Session]
    public $search = '';

    public function toggleLearned($id)
    {
        $word = Word::findOrFail($id);
        $word->learned = !$word->learned;
        $word->save();
    }

    public function with(): array
    {
        $words = Word::query()
            ->where(function ($query) {
                $query
                    ->where('romaji', 'like', "%{$this->search}%")
                    ->orWhere('kana', 'like', "%{$this->search}%")
                    ->orWhere('meaning', 'like', "%{$this->search}%");
            })
            ->get()->toArray();

        return [
            'words' => $words
        ];
    }
}
?>

<div
    x-data="vocabState(@js($words))"
    x-init="init()"
    class="max-w-6xl mx-auto px-4 py-6 dark"
>
    <h1 class="text-3xl font-bold text-center mb-8 text-white">Japanese Vocabulary Tracker</h1>

    <div class="flex justify-between mb-6">
        <div class="flex flex-col gap-4 w-full">
            <flux:input
                x-model="search"
                placeholder="Search by romaji, kana, or meaning..."
                size="md"
                variant="filled"
                class="max-w-lg w-full"
            />
            <div x-show="loading" class="text-sm text-gray-400">Syncing with server…</div>
        </div>

        <div class="flex flex-col gap-4 mb-8">
            <a x-bind:href="'/word/create/'" class="w-max text-md bg-green-700 text-white rounded-xl px-4 py-2 hover:underline cursor-pointer">
                Add Word
            </a>
        </div>
    </div>

    <div class="mb-12">

        <flux:separator class="mb-8" />

        <h2 class="text-xl font-semibold text-green-400">Learned Words</h2>

        <!-- Desktop Table -->
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
                    <template x-for="word in filtered(learnedWords)" x-bind:key="word.id">
                        <x-table.tr>
                            <x-table.td>
                                <flux:checkbox.group>
                                    <flux:checkbox x-bind:checked="word.learned" @click="toggle(word)" />
                                </flux:checkbox.group>
                            </x-table.td>
                            <x-table.td x-text="word.romaji" />
                            <x-table.td x-text="word.kana" />
                            <x-table.td x-text="word.meaning" />
                            <x-table.td class="text-right">
                                <div class="flex items-center gap-2 justify-end">
                                    <a x-bind:href="'/word/edit/' + word.id" class="text-md bg-green-700 text-white rounded-xl px-4 py-2 hover:underline cursor-pointer">
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

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-4">
            <template x-for="word in filtered(learnedWords)" x-bind:key="word.id">
                <div x-data="{ open: false }" class="bg-gray-800 text-white rounded-lg shadow border px-4 py-3">
                    <div class="flex justify-between items-center">
                        <div class="font-semibold w-1/3" x-text="word.romaji"></div>
                        <flux:button
                            @click.stop="playAudio(word.kana)"
                            icon="play"
                            class="text-sm cursor-pointer"
                        />
                        <button @click="open = !open" class="text-sm text-blue-400 hover:underline">
                            <span x-show="!open">Show</span><span x-show="open">Hide</span>
                        </button>
                    </div>
                    <div x-show="open" x-transition class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Kana:</span><span x-text="word.kana" />
                        </div>
                        <div class="flex justify-between">
                            <span>Meaning:</span><span x-text="word.meaning" />
                        </div>
                        <div class="flex justify-between items-center pt-2">
                            <flux:checkbox.group>
                                <flux:checkbox x-bind:checked="word.learned" @click="toggle(word)" /><span>Learned</span>
                            </flux:checkbox.group>
                            <div class="flex items-center gap-2 justify-end">
                                <a x-bind:href="'/word/edit/' + word.id" class="text-md bg-green-700 text-white rounded-xl px-4 py-2 hover:underline cursor-pointer">
                                    Edit
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

        <!-- Unlearned Section (same structure) -->
    <div>
        <h2 class="text-xl font-semibold text-blue-400 mb-4">Unlearned Words</h2>
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
                    <template x-for="word in filtered(unlearnedWords)" x-bind:key="word.id">
                        <x-table.tr>
                            <x-table.td>
                                <flux:checkbox.group>
                                    <flux:checkbox x-bind:checked="word.learned" @click="toggle(word)" />
                                </flux:checkbox.group>
                            </x-table.td>
                            <x-table.td x-text="word.romaji" />
                            <x-table.td x-text="word.kana" />
                            <x-table.td x-text="word.meaning" />
                            <x-table.td class="text-right">
                                <div class="flex items-center gap-2 justify-end">
                                    <a x-bind:href="'/word/edit/' + word.id" class="text-md bg-green-700 text-white rounded-xl px-4 py-2 hover:underline cursor-pointer">
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
            <template x-for="word in filtered(unlearnedWords)" x-bind:key="word.id">
                <div x-data="{ open: false }" class="bg-gray-800 text-white rounded-lg shadow border px-4 py-3">
                    <div class="flex justify-between items-center">
                        <div class="font-semibold w-1/3" x-text="word.romaji"></div>
                        <flux:button
                            @click.stop="playAudio(word.kana)"
                            icon="play"
                            class="text-sm cursor-pointer"
                        />
                        <button @click="open = !open" class="text-sm text-blue-400 hover:underline">
                            <span x-show="!open">Show</span><span x-show="open">Hide</span>
                        </button>
                    </div>
                    <div x-show="open" x-transition class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Kana:</span><span x-text="word.kana" /></div>
                        <div class="flex justify-between">
                            <span>Meaning:</span><span x-text="word.meaning" />
                        </div>
                        <div class="flex justify-between items-center pt-2">
                            <flux:checkbox.group>
                                <flux:checkbox x-bind:checked="word.learned" @click="toggle(word)" /><span>Learned</span>
                            </flux:checkbox.group>
                            <div class="flex items-center gap-2 justify-end">
                                <a x-bind:href="'/word/edit/' + word.id" class="text-md bg-green-700 text-white rounded-xl px-4 py-2 hover:underline cursor-pointer">
                                    Edit
                                </a>
                                <flux:button
                                    @click.stop="playAudio(word.kana)"
                                    icon="play"
                                    class="text-sm cursor-pointer"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function vocabState(words) {
  return {
    search: '',
    allWords: words,
    learnedWords: [],
    unlearnedWords: [],
    loading: false,

    init() {
      this.learnedWords = this.allWords.filter(w => w.learned);
      this.unlearnedWords = this.allWords.filter(w => !w.learned);
    },

    toggle(word) {
      const prev = word.learned;
      word.learned = !prev;
      this.loading = true;

      if (word.learned) {
        this.unlearnedWords = this.unlearnedWords.filter(w => w.id !== word.id);
        this.learnedWords.push(word);
      } else {
        this.learnedWords = this.learnedWords.filter(w => w.id !== word.id);
        this.unlearnedWords.push(word);
      }

      $wire.call('toggleLearned', word.id)
        .then(() => Toast({ text: 'Word updated', variant: 'success' }))
        .catch(() => {
          word.learned = prev;
          if (prev) {
            this.learnedWords = this.learnedWords.filter(w => w.id !== word.id);
            this.unlearnedWords.push(word);
          } else {
            this.unlearnedWords = this.unlearnedWords.filter(w => w.id !== word.id);
            this.learnedWords.push(word);
          }
          Toast({ text: 'Update failed', variant: 'danger' });
        })
        .finally(() => this.loading = false);
    },

    filtered(list) {
      if (!this.search) return list;
      const s = this.search.toLowerCase();
      return list.filter(w =>
        w.romaji.toLowerCase().includes(s) ||
        w.kana.includes(s) ||
        w.meaning.toLowerCase().includes(s)
      );
    }
  }
}
</script>
