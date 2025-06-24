@props([
    'hiragana' => null,
    'word' => null,
    'isLearned' => null,
    'checkboxEvent' => null,
    'hideRomaji' => false
])
<div {{ $attributes->merge(['class' => 'flex gap-2 flex-wrap justify-start']) }} x-data="{ isLearned: {{ $isLearned ?? 'false' }} }">
    @if ($hiragana)
        <div class="flex flex-col p-0 border border-zinc-300 dark:border-zinc-800/90 cursor-pointer rounded-xl" :class="isLearned ? 'bg-green-100 dark:bg-green-950 hover:bg-green-200/50 dark:hover:bg-green-900/80' : 'hover:bg-zinc-200/10 dark:hover:bg-zinc-500/20'" @click.stop="playAudio('{{ $hiragana['kana'] }}')">
            <span class="text-2xl p-2 text-center">{{ $hiragana['kana'] }}</span>
            @if (!$hideRomaji)
                <span class="text-sm border-y border-zinc-400 dark:border-zinc-800/90 text-center text-zinc-500 dark:text-zinc-400 px-2 py-1">{{ $hiragana['romaji'] }}</span>
            @endif
            @if (isset($isLearned))
            <div class="flex justify-center items-center p-2">
                <flux:checkbox class="cursor-pointer m-1 text-green-600 scale-130" x-bind:checked="isLearned ?? false" @click.stop="isLearned = !isLearned" wire:change="$dispatch('{{ $checkboxEvent }}', { id: {{ $hiragana['id'] }} })" wire:loading.attr="disabled" />
            </div>
            @endif
        </div>
    @elseif ($word)
    @foreach (mb_str_split($word['kana']) as $char)
    @php
        $hiragana = App\Models\Hiragana::where('kana', $char)->first();
        if (!$hiragana) {
            $hiragana = new App\Models\Hiragana();
        }
    @endphp
        <div class="flex flex-col p-0 border border-zinc-300 dark:border-zinc-800/90 cursor-pointer rounded-xl" class="hover:bg-zinc-200/10 dark:hover:bg-zinc-500/20" @click.stop.prevent="playAudio('{{ $char }}')">
            <span class="p-2 text-center">{{ $char }}</span>
            @if (!$hideRomaji)
                <span class="text-sm border-y border-zinc-400 dark:border-zinc-800/90 text-center text-zinc-500 dark:text-zinc-400 px-2 py-1">{{ $hiragana['romaji'] }}</span>
            @endif
        </div>
    @endforeach
    @else
    <template x-for="char, index in word.kana" :key="'hiragana-word-' + word.id + '-' + index">
        <div class="flex flex-col p-0 gap-2 border border-zinc-300 dark:border-zinc-700 cursor-pointer" @click.stop="playAudio(char)" >
            <span x-text="char" class="text-2xl p-2"></span>
            <span x-text="all_hiraganas[char]" class="text-sm border-t border-zinc-300 dark:border-zinc-700 text-center text-zinc-500 dark:text-zinc-400"></span>
        </div>
    </template>
    @endif
</div>

</span>
