<x-layouts.app.sidebar>
    <x-toast />
    <flux:main>
        {{ $slot }}
    </flux:main>

<script>
  function playAudio(text) {
      const utterance = new SpeechSynthesisUtterance(text);
      const voices = window.speechSynthesis.getVoices();
      console.log(voices);
      utterance.voice = voices.find(v => v.lang === 'ja-JP' && v.name.includes('Kyoko'));
      speechSynthesis.speak(utterance);
  }
</script>

</x-layouts.app.sidebar>
