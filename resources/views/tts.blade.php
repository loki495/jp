<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Voice Tester</title>
  <style>
    body { font-family: sans-serif; padding: 1em; }
    input { margin-bottom: 1em; }
    .voice-item { margin-bottom: 0.5em; }
  </style>
</head>
<body>
  <h1>Browser TTS Voice Tester</h1>
  <label>
    Language Code (e.g., en-US, es-ES, ja-JP):
    <br>
    <input type="text" id="langInput" value="ja-JP">
    <br>
    <button onclick="loadVoices()">Load Voices</button>
  </label>
  <div id="voiceList"></div>

  <script>
    let voices = [];

    function populateVoices() {
      voices = window.speechSynthesis.getVoices();
    }

    function loadVoices() {
      const lang = document.getElementById('langInput').value.trim();
      const list = document.getElementById('voiceList');
      list.innerHTML = '';

      if (!voices.length) {
        populateVoices();
      }

      const filtered = voices.filter(v => v.lang.toLowerCase().includes(lang.toLowerCase()));
      if (!filtered.length) {
        list.innerHTML = `<p>No voices found for <strong>${lang}</strong>.</p>`;
        return;
      }

      filtered.forEach(voice => {
        const div = document.createElement('div');
        div.className = 'voice-item';

        const name = `${voice.name} (${voice.lang})${voice.default ? ' [default]' : ''}`;
        const button = document.createElement('button');
        button.textContent = 'Speak';
        button.onclick = () => {
          const utterance = new SpeechSynthesisUtterance(`こんにちは, 水, ください`);
          utterance.voice = voice;
          speechSynthesis.speak(utterance);
        };

        div.textContent = name + ' ';
        div.appendChild(button);
        list.appendChild(div);
      });
    }

    // Some browsers load voices asynchronously
    if (typeof speechSynthesis !== 'undefined') {
      speechSynthesis.onvoiceschanged = populateVoices;
    }

    window.onload = populateVoices;
  </script>
</body>
</html>
