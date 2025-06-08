<?php

use Livewire\Volt\Component;
use App\Models\Word;

new class extends Component
{
    public ?int $wordId = null;
    public string $romaji = '';
    public string $kana = '';
    public string $meaning = '';
    public bool $learned = false;

    public function mount(?int $wordId = null): void
    {
        $this->wordId = $wordId;

        if ($wordId) {
            $word = Word::findOrFail($wordId);
            $this->romaji = $word->romaji;
            $this->kana = $word->kana;
            $this->meaning = $word->meaning;
            $this->learned = $word->learned;
        }
    }

    public function save(): void
    {
        $this->validate([
            'romaji' => 'required|string|max:255',
            'kana' => 'required|string|max:255',
            'meaning' => 'required|string|max:255',
        ]);

        Word::updateOrCreate(
            ['id' => $this->wordId],
            [
                'romaji' => $this->romaji,
                'kana' => $this->kana,
                'meaning' => $this->meaning,
                'learned' => $this->learned,
            ]
        );

        session()->flash('message', 'Word saved successfully.');
        redirect()->route('dashboard');
    }
};
?>

<div class="max-w-xl mx-auto mt-10 p-6 bg-zinc-900 text-white shadow rounded-lg space-y-6">
    <h1 class="text-2xl font-semibold">
        {{ $wordId ? 'Edit Word' : 'Add New Word' }}
    </h1>

    <div>
        <label class="block mb-1 text-sm font-medium text-gray-300">Romaji</label>
        <input type="text" wire:model.defer="romaji"
            class="w-full px-4 py-2 bg-zinc-800 border border-gray-700 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
        @error('romaji') <span class="text-sm text-red-400">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block mb-1 text-sm font-medium text-gray-300">Kana</label>
        <input type="text" wire:model.defer="kana"
            class="w-full px-4 py-2 bg-zinc-800 border border-gray-700 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
        @error('kana') <span class="text-sm text-red-400">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block mb-1 text-sm font-medium text-gray-300">Meaning</label>
        <input type="text" wire:model.defer="meaning"
            class="w-full px-4 py-2 bg-zinc-800 border border-gray-700 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
        @error('meaning') <span class="text-sm text-red-400">{{ $message }}</span> @enderror
    </div>

    <div class="flex items-center">
        <input type="checkbox" wire:model.defer="learned" id="learned"
            class="mr-2 accent-blue-500 bg-zinc-800 border-gray-600" />
        <label for="learned" class="text-sm text-gray-300">Mark as learned</label>
    </div>

    <div class="flex justify-between pt-4">
        <a href="{{ route('dashboard') }}" class="text-blue-400 hover:underline">← Back to list</a>
        <button wire:click="save"
            class="bg-blue-600 hover:bg-blue-700 px-4 py-2 text-white rounded shadow transition">
            {{ $wordId ? 'Update Word' : 'Add Word' }}
        </button>
    </div>
</div>
