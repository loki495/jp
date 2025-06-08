<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="mr-5 flex items-center space-x-2" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group heading="Words" class="grid">
                    <flux:navlist.item icon="list-bullet" :href="route('words.index')" :current="request()->routeIs('words.index')" wire:navigate>{{ __('Word List') }}</flux:navlist.item>
                    <flux:navlist.item icon="plus" :href="route('words.create')" :current="request()->routeIs('words.create')" wire:navigate>{{ __('Add Word') }}</flux:navlist.item>
                    <flux:navlist.item icon="calendar" :href="route('words.flashcards')" :current="request()->routeIs('words.flashcards')" wire:navigate>{{ __('Flashcards') }}</flux:navlist.item>
                </flux:navlist.group>

            </flux:navlist>

        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
