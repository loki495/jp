<?php

declare(strict_types=1);

use App\Models\PracticeSet;
use Livewire\Attributes\Session;
use Livewire\Volt\Component;

new class extends Component
{
    #[Session]
    public string $mode = '';

    public array $currentWord = [];

    #[Session]
    public $activeSetName = '';

    public function mount(): void
    {
        if (! $this->mode) {
            $this->mode = 'romaji';
        }

        if (! $this->activeSetName) {
            $this->activeSetName = PracticeSet::LEARNED_SET;
        }

        $this->next();
    }

    public function updatedActiveSetName(): void
    {
        if ($this->activeSetName == PracticeSet::HIRAGANA_SET &&
            $this->mode == 'meaning') {
            $this->mode = 'kana';
        }
        $this->next();
    }

    public function next(): void
    {
        $practiceSet = new PracticeSet($this->activeSetName);

        $words = collect($practiceSet->words());

        $currentWord = $this->currentWord;

        if (count($words)) {
            $this->currentWord = $words
                ->filter(function ($word) use ($currentWord) {
                    return $word['learned'] && ($word['id'] != ($currentWord['id'] ?? null));
                })
                ->random();
        }

        // $this->currentWord = Word::query()
        // ->where('romaji', 'like', 'jakk%')
        // ->first()->toArray();
    }

    public function setMode($mode): void
    {
        $this->mode = $mode;
        $this->next();
    }

    public function with(): array
    {
        $sets = PracticeSet::available_sets();

        return [
            'mode' => $this->mode,
            'currentWord' => $this->currentWord,
            'sets' => $sets,
            'word' => $this->currentWord,
            'activeSetName' => $this->activeSetName,
        ];
    }
};
?>

<div
    x-data="{
        // Card state: flip is a simple toggle; drag/swipe drives dx (horizontal
        // offset in px) while dragging or animating, and both are combined into
        // one transform below so they never fight each other.
        flipped: false,
        dragging: false,
        dragged: false,
        animating: false,
        pointerId: null,
        startX: 0,
        startY: 0,
        startTime: 0,
        dx: 0,
        threshold: 100, // px of drag before a release counts as a swipe
        velocityThreshold: 0.5, // px/ms; a fast flick counts even under the distance threshold

        flip() {
            if (this.dragged || this.animating) return;
            this.flipped = !this.flipped;
        },

        cardStyle() {
            const tilt = this.dx / 20;
            return {
                transform: `translateX(${this.dx}px) rotate(${tilt}deg) rotateY(${this.flipped ? 180 : 0}deg)`,
                opacity: 1 - Math.min(Math.abs(this.dx) / 400, 0.6),
                transition: this.dragging ? 'none' : 'transform 0.25s ease, opacity 0.25s ease',
            };
        },

        pointerDown(event) {
            if (this.animating) return;
            this.pointerId = event.pointerId;
            this.$refs.card.setPointerCapture(event.pointerId);
            this.dragging = true;
            this.dragged = false;
            this.startX = event.clientX;
            this.startY = event.clientY;
            this.startTime = Date.now();
            this.dx = 0;
        },
        pointerMove(event) {
            if (!this.dragging || event.pointerId !== this.pointerId) return;
            this.dx = event.clientX - this.startX;
            // A vertical drag (scrolling past a long card) still counts as dragged, so
            // the tap-to-flip that follows pointerup does not fire; only dx moves the card.
            const dy = event.clientY - this.startY;
            if (Math.abs(this.dx) > 5 || Math.abs(dy) > 5) this.dragged = true;
        },
        pointerUp(event) {
            if (!this.dragging || event.pointerId !== this.pointerId) return;
            this.dragging = false;

            const elapsed = Math.max(Date.now() - this.startTime, 1);
            const velocity = Math.abs(this.dx) / elapsed;
            const passedThreshold = Math.abs(this.dx) > this.threshold || velocity > this.velocityThreshold;

            if (passedThreshold) {
                this.next();
            } else {
                this.dx = 0;
                // Keep `dragged` true through the click event that follows this
                // pointerup, so the drag isn't also read as a tap-to-flip; clear
                // it right after so the next plain tap can flip normally.
                setTimeout(() => { this.dragged = false; }, 0);
            }
        },
        next(direction) {
            this.animating = true;
            this.dx = (direction ?? this.dx) >= 0 ? window.innerWidth : -window.innerWidth;
            setTimeout(() => {
                this.dx = 0;
                this.flipped = false;
                this.animating = false;
                this.dragged = false;
                $wire.next();
            }, 250);
        },
    }"
    class="card-wrapper w-fit max-h-[90dvh] overflow-hidden flex flex-col justify-center items-center gap-4 perspective max-w-screen max-w-(--breakpoint-sm) mx-auto touch-pan-y select-none"
>
    @teleport('body')
    <div wire:loading class="fixed right-4 top-4"><flux:icon.loading /></div>
    @endteleport

    <div class="text-3xl font-bold text-center">Flashcards</div>

    <div class="flex items-center gap-4 mb-4">
        <label class="text-white font-semibold">Current List:</label>

        <select wire:model.live="activeSetName" class="bg-zinc-800 text-white rounded p-2" x-cloak>
            @foreach ($sets as $list)
                <option value="{{ $list }}">{{ $list }}</option>
            @endforeach
        </select>
    </div>

    <!-- Mode Selection -->
    <div class="flex gap-2 justify-center mb-4" role="group" aria-label="Flashcard mode">
        <button
            wire:click="setMode('romaji')"
            @click="flipped = false"
            class="px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400 transition bg-blue-500/20 hover:bg-blue-700/60"
            :class="{ 'bg-blue-800/70!': $wire.mode === 'romaji' }"
            aria-pressed="{{ $mode === 'romaji' ? 'true' : 'false' }}"
        >Romaji</button>
        <button
            wire:click="setMode('kana')"
            @click="flipped = false"
            class="px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400 transition bg-blue-500/20 hover:bg-blue-700/60"
            :class="{ 'bg-blue-800/70!': $wire.mode === 'kana' }"
            aria-pressed="{{ $mode === 'kana' ? 'true' : 'false' }}"
        >Kana</button>
        <button
            wire:click="setMode('meaning')"
            @click="flipped = false"
            class="px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400 transition {{ $activeSetName == PracticeSet::HIRAGANA_SET ? 'bg-gray-600 opacity-50 cursor-not-allowed' : 'bg-blue-500/20 hover:bg-blue-700/60' }}"
            :class="{ 'bg-blue-800/70!': $wire.mode === 'meaning' }"
            {{ $activeSetName == PracticeSet::HIRAGANA_SET ? 'disabled' : '' }}
            aria-pressed="{{ $mode === 'meaning' ? 'true' : 'false' }}"
        >English</button>

    </div>

    <span class="text-sm text-zinc-400 text-center mt-2">Drag, swipe or click Next for the next word</span>

    <!-- Card -->
    <div
        x-ref="card"
        class="relative w-full transform-style preserve-3d cursor-pointer touch-pan-y select-none"
        :style="cardStyle()"
        @click="if (!$event.target.closest('.no-flip')) flip()"
        @pointerdown="pointerDown"
        @pointermove="pointerMove"
        @pointerup="pointerUp"
        @pointercancel="pointerUp"
    >

        <!-- Front Face -->
        <div x-ref="front"
            class="relative w-full md:min-w-max max-h-[55dvh] overflow-y-auto rounded-lg shadow-lg bg-zinc-500/30 text-center text-zinc-200 font-bold flex flex-col gap-2 items-center justify-center p-4 transition backface-hidden pt-12 touch-pan-y select-none"
            wire:loading.class="opacity-0"
        >
            @if($mode === 'romaji')
                <div class="md:mt-2 text-6xl">
                    {{ $currentWord['romaji'] ?? 'No word' }}
                </div>
                <flux:button
                    @click.stop="playAudio('{{ $currentWord['kana'] ?? '' }}')"
                    @pointerdown.stop=""
                    icon="play"
                    variant="subtle"
                    class="mt-4 text-sm text-blue-300 underline hover:text-blue-400"
                />
            @elseif($mode === 'kana')
                <div class="md:mt-2 text-6xl">
                    <x-kana :word="$currentWord" hideRomaji="true" />
                </div>
                <flux:button
                    @click.stop="playAudio('{{ $currentWord['kana'] ?? '' }}')"
                    @pointerdown.stop=""
                    icon="play"
                    variant="subtle"
                    class="mt-4 text-sm text-blue-300 underline hover:text-blue-400"
                />
            @else
                <div class="md:mt-2 ">
                    <span class="text-2xl">{{ $currentWord['meaning'] ?? 'No word' }}</span>
                </div>
            @endif

            <button class="absolute top-2 right-2 bg-transparent hover:bg-zinc-700/40 hover:text-zinc-300 font-bold py-1 px-1 border border-zinc-400 rounded cursor-pointer" @click.stop="next(-1)" @pointerdown.stop="">Next</button>
        </div>

        <!-- Back Face -->
        <div x-ref="back"
            class="absolute w-full break-all max-w-full max-h-[55dvh] overflow-y-auto whitespace-normal top-0 left-1/2 -translate-x-1/2 rounded-lg shadow-lg bg-zinc-500/30 rotate-y-180 text-center backface-hidden flex flex-col gap-2 items-center justify-center p-4 transition pt-12 touch-pan-y select-none"
            wire:loading.remove
        >
            <div class="md:mt-2 text-6xl text-blue-500 font-bold w-full flex justify-center">
                <x-kana :word="$currentWord" />
            </div>

            @if ($activeSetName !== 'kana')
            <div class="text-2xl text-yellow-500 w-full">{{ $currentWord['meaning'] ?? '' }}</div>
            @endif

            <flux:button
                @click.stop="playAudio('{{ $currentWord['kana'] ?? '' }}')"
                @pointerdown.stop=""
                icon="play"
                variant="subtle"
                class="mt-0 text-sm text-blue-300 underline hover:text-blue-400"
            />

            <button class="absolute top-2 right-2 bg-transparent hover:bg-zinc-700/40 hover:text-zinc-300 font-bold py-1 px-1 border border-zinc-400 rounded cursor-pointer" @click.stop="next(-1)" @pointerdown.stop="">Next</button>
        </div>

    </div>

    <style>
    .perspective { perspective: 1000px; }
    .backface-hidden { backface-visibility: hidden; }
    .rotate-y-180 { transform: rotateY(180deg); }
    .transform-style { transform-style: preserve-3d; }
    </style>
</div>
