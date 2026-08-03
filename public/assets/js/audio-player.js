(() => {
  const player = document.querySelector('[data-audio-player]');
  if (!player) return;

  const toggle = player.querySelector('[data-player-toggle]');
  const icon = player.querySelector('[data-player-icon]');
  const current = player.querySelector('[data-player-current]');
  const duration = player.querySelector('[data-player-duration]');
  const loading = player.querySelector('[data-player-loading]');
  const error = player.querySelector('[data-player-error]');

  const formatTime = (seconds) => {
    if (!Number.isFinite(seconds) || seconds < 0) return '0:00';
    const rounded = Math.floor(seconds);
    const minutes = Math.floor(rounded / 60);
    return `${minutes}:${String(rounded % 60).padStart(2, '0')}`;
  };

  if (typeof window.WaveSurfer === 'undefined') {
    loading.hidden = true;
    error.hidden = false;
    error.textContent = 'No pudimos iniciar el reproductor. Revisá la conexión y recargá la página.';
    return;
  }

  const wavesurfer = window.WaveSurfer.create({
    container: '#waveform',
    url: player.dataset.audioUrl,
    height: 112,
    waveColor: '#5f6466',
    progressColor: '#ff6b45',
    cursorColor: '#f5f1e8',
    cursorWidth: 2,
    barWidth: 3,
    barGap: 2,
    barRadius: 3,
    normalize: true,
  });

  wavesurfer.on('ready', (seconds) => {
    duration.textContent = formatTime(seconds);
    loading.hidden = true;
    toggle.disabled = false;
  });

  wavesurfer.on('timeupdate', (seconds) => {
    current.textContent = formatTime(seconds);
  });

  wavesurfer.on('play', () => {
    icon.textContent = 'Ⅱ';
    toggle.setAttribute('aria-label', 'Pausar');
  });

  const showPlayState = () => {
    icon.textContent = '▶';
    toggle.setAttribute('aria-label', 'Reproducir');
  };

  wavesurfer.on('pause', showPlayState);
  wavesurfer.on('finish', showPlayState);
  wavesurfer.on('error', () => {
    loading.hidden = true;
    error.hidden = false;
    error.textContent = 'No pudimos leer este audio. Probá reemplazándolo por otro MP3.';
  });

  toggle.addEventListener('click', () => wavesurfer.playPause());

  player.querySelectorAll('[data-player-skip]').forEach((button) => {
    button.addEventListener('click', () => wavesurfer.skip(Number(button.dataset.playerSkip)));
  });
})();
