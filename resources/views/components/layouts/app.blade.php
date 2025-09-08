<x-layouts.app.sidebar>
    <x-toast />
    <flux:main>
        {{ $slot }}
    </flux:main>

<script>
  function playAudio(text, rate = 1) {
      const utterance = new SpeechSynthesisUtterance(text);
      const voices = window.speechSynthesis.getVoices();
      utterance.voice = voices.find(v => v.lang === 'ja-JP' && v.name.includes('Kyoko'));
      utterance.rate = rate;
      speechSynthesis.speak(utterance);
  }
</script>

</x-layouts.app.sidebar>
