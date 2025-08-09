<?php

namespace App\Http\Livewire;

use App\Models\Kana;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Session;
use Livewire\Volt\Component;

new class extends Component {

    #[Session]
    public $type = '';

    public function with(): array {
        $kana_list = Kana::query();

        if ($this->type) {
            $kana_list = $kana_list->where('type', $this->type);
        } else {
            $kana_list = $kana_list->whereIn('type', ['hiragana', 'katakana']);
        }

        return [
            'kana_list' => $kana_list->get(),
        ];
    }

    #[On('toggle')]
    #[Renderless]
    public function toggleLearned($id) {
        $kana = Kana::findOrFail($id);
        $kana->learned = !$kana->learned;
        $kana->save();
    }
}
?>
<div
    class="max-w-6xl mx-auto px-4 py-6 dark"
>
    <div wire:loading class="fixed right-4 top-4"><flux:icon.loading /></div>

    <h1 class="text-3xl font-bold text-center mb-8 text-white">Kana</h1>

    <div class="flex items-center gap-4 mb-4">
        <label class="text-white font-semibold">Current List:</label>

        <flux:select wire:model.live="type" class="bg-zinc-800 text-white rounded p-2" x-cloak>
            <flux:select.option value="">All</flux:select.option>
                <flux:select.option value="hiragana">Hiragana</flux:select.option>
                <flux:select.option value="katakana">Katakana</flux:select.option>
        </flux:select>
    </div>
    <div class="flex flex-col gap-4">
        <div class="flex items-center gap-4 mb-4 w-full flex-wrap border border-zinc-600 p-2 rounded-xl bg-zinc-700">
            @foreach ($kana_list as $index => $kana)
            <x-kana :kana="$kana" :isLearned="$kana->learned" checkboxEvent="toggle" wire:key="{{ $kana->id }}-{{ $kana->learned }}" data-index="{{ $index }}"/>
            @if ($index == 45 || $index == 70)
        </div>
        <div class="flex items-center gap-4 mb-4 w-full flex-wrap border border-zinc-600 p-2 rounded-xl bg-zinc-700">
            @endif
            @endforeach
        </div>
    </div>
</div>
