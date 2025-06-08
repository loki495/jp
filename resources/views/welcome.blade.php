<x-layouts.app.sidebar>
    <div class="max-w-6xl mx-auto px-4 py-6 dark" >
        <flex:button :href="route('words.list')">Word List</flex:button>
        <flex:button :href="route('words.create')">Add Word</flex:button>
    </div>
</x-layouts.app.sidebar>
