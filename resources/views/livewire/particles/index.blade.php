<?php



namespace App\Http\Livewire;

use App\Models\Kana;
use App\Models\Particle;
use Livewire\Attributes\Session;
use Livewire\Volt\Component;
use App\Models\PracticeSet;
use App\Models\Word;

new class extends Component {

    public $search = '';

    public function with(): array
    {
        $particles = Kana::query()
            ->where('type', '=', 'particle')
            ->where(function ($q) {
                $q->when($this->search, function ($query, $search) {
                    $query
                        ->where('romaji', 'like', "%{$search}%")
                        ->orWhere('kana', 'like', "%{$search}%")
                        ->orWhere('meaning', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("kana like '%{$this->search}%' desc")
            ->get();


        return [
            'particles' => $particles
        ];
    }

}
?>
<div>
    <div class="text-3xl font-bold text-center">Particles</div>
    <div class="flex items-center gap-4 mb-4">
        <label class="text-white font-semibold">Search:</label>
        <flux:input type="text" wire:model.live="search" placeholder="Search particles..." />
    </div>
    <div class="flex gap-4 flex-wrap ">
        <x-table>
            <x-slot name="head">
                <x-table.tr>
                    <x-table.th>Use</x-table.th>
                    <x-table.th>Particle</x-table.th>
                    <x-table.th>Example</x-table.th>
                </x-table.tr>
            </x-slot>

            <x-slot name="body">
                @foreach($particles as $particle)
                <x-table.tr class="border-b border-zinc-700/60" x-cloak>
                    <x-table.td>{{ $particle->meaning }}</x-table.td>
                    <x-table.td><div class="flex justify-center"><x-kana :kana="$particle" /></div></x-table.td>
                    @php
                    preg_match('/(.*?) (\(.*?\))/', $particle->examples, $matches);
                    @endphp
                    <x-table.td>
                        <button class="text-left text-md bg-zinc-700/60 active:bg-zinc-700 text-white rounded-xl px-4 py-2 cursor-pointer" onclick="playAudio('{{ $matches[1] }}')">{{ $particle->examples }}</button>
                    </x-table.td>
                </x-table.tr>
                @endforeach
            </x-slot>
        </x-table>
    </div>
</div>
<script>
function playAudio(text) {
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'ja-JP';
    utterance.rate = 0.1;
    speechSynthesis.speak(utterance);
}
</script>
