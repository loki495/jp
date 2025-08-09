@props([
    'kana' => null,
    'word' => null,
    'isLearned' => null,
    'checkboxEvent' => null,
    'hideRomaji' => false
])
<div {{ $attributes->merge(['class' => 'flex gap-2 flex-wrap justify-start no-flip']) }}
    @touchstart.stop
    @touchend.stop
    x-data="{
        isLearned: @js($isLearned),
        init() {
            @if ($checkboxEvent && ($kana['id'] ?? false))
            this.$watch('isLearned', (val) => {
                $dispatch('{{ $checkboxEvent }}', { id: {{ $kana['id'] }} })
            });
            @endif
        },
    }"
    >
    @if ($kana)
        <div class="flex flex-col p-0 border border-zinc-300 dark:border-zinc-800/90 cursor-pointer rounded-xl active:bg-white/10 transition-colors" :class="isLearned ? 'bg-green-100 dark:bg-green-950 hover:bg-green-200/50 dark:hover:bg-green-900/80' : 'hover:bg-zinc-200/10 dark:hover:bg-zinc-500/20'" @click.stop.prevent="playAudio('{{ $kana['kana'] }}')">
            <span class="text-2xl p-2 text-center">{{ $kana['kana'] }}</span>
            @if (!$hideRomaji)
                <span class="text-sm border-y border-zinc-400 dark:border-zinc-800/90 text-center text-zinc-500 dark:text-zinc-400 px-2 py-1">{{ $kana['romaji'] }}</span>
            @endif
            @if (isset($isLearned))
            <div class="flex justify-center items-center p-2">
                <flux:checkbox class="cursor-pointer m-1 text-green-600 scale-130" x-model="isLearned" wire:loading.attr="disabled" x-bind:checked="isLearned" />
            </div>
            @endif
        </div>
    @elseif ($word)
    @foreach (mb_str_split($word['kana']) as $char)
    @php
        $kana = App\Models\Kana::where('kana', $char)->first();
        if (!$kana) {
            $kana = new App\Models\Kana();
        }
    @endphp
        <div class="flex flex-col p-0 border border-zinc-300 dark:border-zinc-800/90 cursor-pointer rounded-xl active:bg-white/10 transition-colors" class="hover:bg-zinc-200/10 dark:hover:bg-zinc-500/20" @click.stop.prevent="playAudio('{{ $char }}')">
            <span class="p-2 text-center">{{ $char }}</span>
            @if (!$hideRomaji)
                <span class="text-sm border-y border-zinc-400 dark:border-zinc-800/90 text-center text-zinc-500 dark:text-zinc-400 px-2 py-1">{{ $kana['romaji'] }}</span>
            @endif
        </div>
    @endforeach
    @else
    <template x-for="char, index in word.kana" :key="'kana-word-' + word.type + '-' + word.id + '-' + index">
        <div class="flex flex-col p-0 gap-2 border border-zinc-300 dark:border-zinc-700 cursor-pointer active:bg-white/10 transition-colors" @click.stop.prevent="playAudio(char)" >
            <span x-text="char" class="text-2xl p-2"></span>
            <span x-text="all_kana[char]" class="text-sm border-t border-zinc-300 dark:border-zinc-700 text-center text-zinc-500 dark:text-zinc-400"></span>
        </div>
    </template>
    @endif
</div>
