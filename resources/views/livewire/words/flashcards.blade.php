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
        if (!$this->mode) {
            $this->mode = 'romaji';
        }

        if (!$this->activeSetName) {
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
                    return $word['id'] != ($currentWord['id'] ?? null);
                })
                ->random();
        }

        //$this->currentWord = Word::query()
            //->where('romaji', 'like', 'jakk%')
            //->first()->toArray();
    }

    public function setMode($mode): void
    {
        $this->mode = $mode;
        $this->next();
    }

    public function with(): array {
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
        flipped: false,
        flippedFalseLocked: false,
        unlock() {
            this.flippedFalseLocked = false;
            this.flipped = false;
        },
        flip(event, force) {
            if (this.flippedFalseLocked) {
                force = false;
            }
            if (force === undefined) {
                force = !this.flipped;
            }
            this.flipped = force;
        },
        init() {
            this.flipped = false;
            this.flippedFalseLocked = false;
        },

    }"
    x-init="init"
    class=" card-wrapper w-fit flex flex-col justify-center items-center gap-4 perspective max-w-screen max-w-screen-sm mx-auto"
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
            @click="flip(false)"
            class="px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400 transition bg-blue-500/20 hover:bg-blue-700/60"
            :class="{ '!bg-blue-800/70': $wire.mode === 'romaji' }"
            aria-pressed="{{ $mode === 'romaji' ? 'true' : 'false' }}"
        >Romaji</button>
        <button
            wire:click="setMode('kana')"
            @click="flip(false)"
            class="px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400 transition bg-blue-500/20 hover:bg-blue-700/60"
            :class="{ '!bg-blue-800/70': $wire.mode === 'kana' }"
            aria-pressed="{{ $mode === 'kana' ? 'true' : 'false' }}"
        >Kana</button>
        <button
            wire:click="setMode('meaning')"
            @click="flip(false)"
            class="px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400 transition {{ $activeSetName == PracticeSet::HIRAGANA_SET ? 'bg-gray-600 opacity-50 cursor-not-allowed' : 'bg-blue-500/20 hover:bg-blue-700/60' }}"
            :class="{ '!bg-blue-800/70': $wire.mode === 'meaning' }"
            {{ $activeSetName == PracticeSet::HIRAGANA_SET ? 'disabled' : '' }}
            aria-pressed="{{ $mode === 'meaning' ? 'true' : 'false' }}"
        >English</button>

    </div>
    <span x-text="flippedFalseLocked"></span>

    <!-- Card -->
    <div
        x-data="{
            startX: 0,
            startTime: 0,
            translateX: 0,
            isDragging: false,
            transition: '',
            threshold: 80, // px
            velocityThreshold: 0.5, // px/ms
            rafPending: false,
            counter: 0,
            tapThreshold: 10, // max movement in px
            tapTime: 200,     // max duration in ms

            handleTouchStart(e) {
                this.isDragging = true;
                this.startX = e.touches[0].clientX;
                this.startTime = Date.now();
                this.transition = ''; // No transition while dragging
            },
            handleTouchMove(e) {
                if (!this.isDragging) return;
                if (this.rafPending) return;

                const elapsed = Date.now() - this.startTime;
                const absX = Math.abs(this.translateX);

                if (elapsed < this.tapTime && Math.abs(this.translateX - this.startX) < this.tapThreshold) {
                    return;
                }


                this.rafPending = true;

                requestAnimationFrame(() => {
                    this.translateX = e.touches[0].clientX - this.startX;
                    this.$refs.card.style.transform = `translateX(${this.translateX}px)`;
                    this.$refs.card.style.transition = 'rotate-y-180';
                    this.rafPending = false;
                });
            },
            handleTouchEnd() {
                if (!this.isDragging) return;

                const elapsed = Date.now() - this.startTime;
                const absX = Math.abs(this.translateX);

                if (elapsed < this.tapTime && Math.abs(this.translateX - this.startX) < this.tapThreshold) {
                    return;
                }

                this.isDragging = false;

                velocity = absX / elapsed;
                if (absX > this.threshold && velocity > this.velocityThreshold) {
                    // Animate off-screen
                    this.transition = 'transform 0.3s cubic-bezier(.4,2,.6,1)';
                    this.translateX = this.translateX > 0 ? window.innerWidth : -window.innerWidth;
                    this.$refs.card.style.transform = `translateX(${this.translateX}px)`;
                    this.$refs.card.style.transition = this.transition;
                    setTimeout(() => {
                        this.transition = '';
                        this.translateX = 0;
                        this.$refs.card.style.transform = `translateX(${this.translateX}px)`;
                        this.$refs.card.style.transition = this.transition;
                        this.flippedFalseLocked = true;
                        $wire.next();
                    }, 300);

                } else {
                    // Snap back
                    this.transition = 'transform 0.3s';
                    this.translateX = 0;
                    this.translateX = 0;
                    const flip = this.$root.flipped ? 180 : 0;
                    this.$refs.card.style.transform = `translateX(0px) rotateY(${flip}deg)`;
                    setTimeout(() => {
                        this.$refs.card.style.transform = '';
                    }, 300);
                }
            }
        }"
        x-ref="card"
        class="relative w-full transition-transform duration-300 transform-style preserve-3d cursor-pointer"
        :class="{ 'rotate-y-180': flipped }"
        @click="flip"
        @touchstart="handleTouchStart"
        @touchmove="handleTouchMove"
        @touchend="handleTouchEnd"
    >
        <!-- Front Face -->
        <div x-ref="front"
            class="relative w-full md:min-w-max rounded-lg shadow-lg bg-zinc-500/30 text-center text-zinc-200 font-bold text-6xl flex flex-col gap-2 items-center justify-center p-4 transition backface-hidden pt-12"
            wire:loading.class="opacity-0"
            @click.stop="flip"
        >
            @if($mode === 'romaji')
                <div class="text-6xl">
                    {{ $currentWord['romaji'] ?? 'No word' }}
                </div>
                <flux:button
                    @click.stop="playAudio('{{ $currentWord['kana'] ?? '' }}')"
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
                    icon="play"
                    variant="subtle"
                    class="mt-4 text-sm text-blue-300 underline hover:text-blue-400"
                />
            @else
                <div>
                    <span class="text-2xl">{{ $currentWord['meaning'] ?? 'No word' }}</span>
                </div>
            @endif

        </div>

        <!-- Back Face -->
        <div x-ref="back"
            class="absolute w-full break-all max-w-full whitespace-normal top-0 left-1/2 -translate-x-1/2 rounded-lg shadow-lg bg-zinc-500/30 rotate-y-180 text-center backface-hidden flex flex-col gap-2 items-center justify-center p-4 transition pt-12"
            @click.stop="flip"
            wire:loading.remove
        >
            <div class="md:mt-2 text-6xl text-blue-500 font-bold w-full flex justify-center">
                <x-kana :word="$currentWord" />
            </div>
            <div class="text-5xl text-green-500 font-bold my-2 w-full">{{ $currentWord['romaji'] ?? '' }}</div>
            @if ($activeSetName !== 'kana')
            <div class="text-2xl text-yellow-500 w-full">{{ $currentWord['meaning'] ?? '' }}</div>
            @endif
            <flux:button
                @click.stop="playAudio('{{ $currentWord['kana'] ?? '' }}')"
                icon="play"
                variant="subtle"
2               class="mt-0 text-sm text-blue-300 underline hover:text-blue-400"
            />

        </div>
    </div>

    <style>
    .perspective { perspective: 1000px; }
    .backface-hidden { backface-visibility: hidden; }
    .rotate-y-180 { transform: rotateY(180deg); }
    .transform-style { transform-style: preserve-3d; }
    </style>
</div>
@script
<script>
Livewire.hook('morphed', ({ el, component }) => {
    document.querySelector('.card-wrapper')?._x_dataStack?.[0].unlock()
})
</script>
@endscript
