<?php



namespace App\Http\Livewire;

use App\Models\Hiragana;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Session;
use Livewire\Volt\Component;
use App\Models\PracticeSet;
use App\Models\Word;

new class extends Component {


    public function with(): array {
        $hiragana_list = Hiragana::all();
        return [
            'hiragana_list' => $hiragana_list
        ];
    }

    #[On('toggle')]
    #[Renderless]
    public function toggleLearned($id) {
        $hiragana = Hiragana::findOrFail($id);
        $hiragana->learned = !$hiragana->learned;
        $hiragana->save();
    }
}
?>
<div
    class="max-w-6xl mx-auto px-4 py-6 dark"
>
    <div wire:loading class="fixed right-4 top-4"><flux:icon.loading /></div>

    <h1 class="text-3xl font-bold text-center mb-8 text-white">Hiragana</h1>

    <div class="flex flex-col gap-4">
        <div class="flex items-center gap-4 mb-4 w-full flex-wrap border border-zinc-600 p-2 rounded-xl bg-zinc-700">
            @foreach ($hiragana_list as $index => $hiragana)
            <x-hiragana :hiragana="$hiragana" :isLearned="$hiragana->learned" checkboxEvent="toggle" wire:key="$hiragana->id" data-index="{{ $index }}"/>
            @if ($index == 45 || $index == 70)
        </div>
        <div class="flex items-center gap-4 mb-4 w-full flex-wrap border border-zinc-600 p-2 rounded-xl bg-zinc-700">
            @endif
            @endforeach
        </div>
    </div>
</div>
