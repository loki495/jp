<?php

use Livewire\Volt\Component;
use App\Models\Word;

new class extends Component
{
    public string $mode = 'romaji';
    public array $currentWord = [];

    public function mount(): void
    {
        $this->next();
    }

    public function next(): void
    {
        $word = Word::where('learned', true)
            ->where('kana', '!=', $this->currentWord['kana'] ?? '')
            ->inRandomOrder()->first();
        if ($word) {
            $this->currentWord = $word->toArray();
        }
    }

    public function setMode($mode): void
    {
        $this->mode = $mode;
        $this->next();
    }
};
?>

<div
    x-data="{
        flipped: false,
        front_height: 0,
        back_height: 0,
        current_height: 0,
        flipping: false,
        flip() {
            this.flipped = !this.flipped;
            this.$nextTick(() => {
                this.front_height = this.$refs.front.offsetHeight;
                this.back_height = this.$refs.back.offsetHeight;
                this.current_height = this.flipped ? this.back_height : this.front_height;
            });
            this.flipping = true;
            setTimeout(() => this.flipping = false, 250);
        },
        init() {
            this.flipped = false;
            this.$nextTick(() => {
                this.front_height = this.$refs.front.offsetHeight;
                this.back_height = this.$refs.back.offsetHeight;
                this.current_height = this.front_height;
            });
        },

        setNextFlipping() {

            this.flipping = true;
            el = this
            setTimeout(function () {
                el.$nextTick(() => {
                    el.flipping = false;
                    el.front_height = el.$refs.front.offsetHeight;
                    el.back_height = el.$refs.back.offsetHeight;
                    el.current_height = el.front_height;
                });
            }, 375);
        }

    }"
    x-init="init"
    class="w-full max-w-xs flex flex-col justify-center items-center gap-4 perspective max-w-screen max-w-screen-sm mx-auto"
>
    <div class="text-3xl font-bold text-center">Flashcards</div>

    <!-- Mode Selection -->
    <div class="flex gap-2 justify-center mb-4">
        <button wire:click="setMode('romaji')" @click="flipped = false; setNextFlipping()" class="px-4 py-2 rounded hover:bg-blue-700/60 cursor-pointer bg-blue-500/20" :class="{ '!bg-blue-800/70': $wire.mode === 'romaji' }">Romaji</button>
        <button wire:click="setMode('kana')" @click="flipped = false; setNextFlipping()" class="px-4 py-2 rounded hover:bg-blue-700/60 cursor-pointer bg-blue-500/20" :class="{ '!bg-blue-800/70': $wire.mode === 'kana' }">Kana</button>
        <button wire:click="setMode('meaning')" @click="flipped = false; setNextFlipping()" class="px-4 py-2 rounded hover:bg-blue-700/60 cursor-pointer bg-blue-500/20" :class="{ '!bg-blue-800/70': $wire.mode === 'meaning' }">English</button>
    </div>

    <!-- Flip Container (we control height here) -->
    <div
        class="relative w-full transition-transform duration-500 transform-style preserve-3d transition cursor-pointer"
        :style="'height: ' + current_height + 'px'"
        :class="{ 'rotate-y-180': flipped }"
        @click="flip"
    >
        <!-- Front Face -->
        <div x-ref="front"
            class="flex-col inset-0 rounded-lg shadow-lg bg-zinc-500/30 text-zinc-200 font-bold text-6xl flex items-center justify-center text-center p-8 backface-hidden">
            @if($mode === 'romaji')
                {{ $currentWord['romaji'] ?? 'No word' }}
                <flux:button
                    @click.stop="playAudio('{{ $currentWord['kana'] ?? '' }}')"
                    icon="play"
                    variant="subtle"
                    class="mt-2 text-sm text-blue-300 underline hover:text-blue-400"
                />
            @elseif($mode === 'kana')
                {{ $currentWord['kana'] ?? 'No word' }}
                <flux:button
                    @click.stop="playAudio('{{ $currentWord['kana'] ?? '' }}')"
                    icon="play"
                    variant="subtle"
                    class="mt-2 text-sm text-blue-300 underline hover:text-blue-400"
                />
            @else
                <span class="text-2xl">{{ $currentWord['meaning'] ?? 'No word' }}</span>
            @endif
        </div>

        <!-- Back Face -->
        <div x-ref="back"
            class="inset-0 rounded-lg shadow-lg bg-zinc-500/30 rotate-y-180 text-center backface-hidden flex flex-col gap-2 items-center justify-center p-4 transition"
            x-bind:style="{'margin-top': '-' + front_height + 'px'}"
        >
            <div class="text-6xl text-blue-500 font-bold">{{ $currentWord['kana'] ?? '' }}</div>
            <div class="text-2xl text-green-500">{{ $currentWord['romaji'] ?? '' }}</div>
            <div class="text-2xl text-yellow-500">{{ $currentWord['meaning'] ?? '' }}</div>
            <flux:button
                @click.stop="playAudio('{{ $currentWord['kana'] ?? '' }}')"
                icon="play"
                variant="subtle"
                class="mt-2 text-sm text-blue-300 underline hover:text-blue-400"
            />
        </div>
    </div>

    <!-- Controls -->
    <div class="mt-4 flex flex-col items-center gap-2">
        <button
            wire:click="next"
            @click="flipped = false;"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded"
            x-cloak
            x-show="!flipping"
        >
            Next Word
        </button>
    </div>

    <style>
    .perspective { perspective: 1000px; }
    .backface-hidden { backface-visibility: hidden; }
    .rotate-y-180 { transform: rotateY(180deg); }
    .transform-style { transform-style: preserve-3d; }
    </style>
</div>

