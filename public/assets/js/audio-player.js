(() => {
  const player = document.querySelector('[data-audio-player]');
  if (!player) return;

  const toggle = player.querySelector('[data-player-toggle]');
  const icon = player.querySelector('[data-player-icon]');
  const current = player.querySelector('[data-player-current]');
  const duration = player.querySelector('[data-player-duration]');
  const loading = player.querySelector('[data-player-loading]');
  const error = player.querySelector('[data-player-error]');
  const zoom = player.querySelector('[data-player-zoom]');
  const zoomValue = player.querySelector('[data-zoom-value]');
  const zoomControls = player.querySelectorAll('[data-zoom-step], [data-zoom-reset], [data-player-zoom]');
  const markerCapture = player.querySelector('[data-marker-capture]');
  const markerComposer = document.querySelector('[data-marker-composer]');
  const markerTimeInput = markerComposer?.querySelector('[data-marker-time-input]');
  const markerTimeLabel = markerComposer?.querySelector('[data-marker-time-label]');
  const markerResumePlaying = markerComposer?.querySelector('[data-marker-resume-playing]');
  const markerForm = markerComposer?.querySelector('form');

  const formatTime = (seconds) => {
    if (!Number.isFinite(seconds) || seconds < 0) return '0:00';
    const rounded = Math.floor(seconds);
    const minutes = Math.floor(rounded / 60);
    return `${minutes}:${String(rounded % 60).padStart(2, '0')}`;
  };

  const formatPreciseTime = (milliseconds) => {
    const safe = Math.max(0, Math.round(milliseconds));
    const minutes = Math.floor(safe / 60000);
    const seconds = Math.floor((safe % 60000) / 1000);
    const remainder = safe % 1000;
    return `${minutes}:${String(seconds).padStart(2, '0')}.${String(remainder).padStart(3, '0')}`;
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
    autoScroll: true,
    autoCenter: true,
  });

  wavesurfer.on('ready', (seconds) => {
    duration.textContent = formatTime(seconds);
    loading.hidden = true;
    toggle.disabled = false;
    markerCapture.disabled = false;
    zoomControls.forEach((control) => { control.disabled = false; });

    const resumeSeconds = Math.min(seconds, Math.max(0, Number(player.dataset.resumeMs) / 1000));
    if (resumeSeconds > 0) {
      wavesurfer.setTime(resumeSeconds);
      current.textContent = formatTime(resumeSeconds);
    }

    if (player.dataset.resumePlaying === '1') {
      wavesurfer.play().catch(() => showPlayState());
    }

    if (player.dataset.resumeMs !== '0' || player.dataset.resumePlaying === '1') {
      const cleanUrl = new URL(window.location.href);
      cleanUrl.searchParams.delete('resume_ms');
      cleanUrl.searchParams.delete('autoplay');
      window.history.replaceState({}, '', cleanUrl);
    }
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

  const applyZoom = (value) => {
    const level = Math.min(Number(zoom.max), Math.max(Number(zoom.min), Number(value)));
    zoom.value = String(level);
    wavesurfer.zoom(level);
    zoomValue.textContent = level === 0 ? 'Vista completa' : `${level} px/s`;
  };

  zoom.addEventListener('input', () => applyZoom(zoom.value));

  player.querySelectorAll('[data-zoom-step]').forEach((button) => {
    button.addEventListener('click', () => applyZoom(Number(zoom.value) + Number(button.dataset.zoomStep)));
  });

  player.querySelector('[data-zoom-reset]').addEventListener('click', () => applyZoom(0));

  markerCapture.addEventListener('click', () => {
    const timeMs = Math.round(wavesurfer.getCurrentTime() * 1000);
    markerTimeInput.value = String(timeMs);
    markerTimeLabel.textContent = formatPreciseTime(timeMs);
    markerComposer.hidden = false;
    markerComposer.querySelector('select').focus();
    markerComposer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  });

  markerForm.addEventListener('submit', () => {
    markerResumePlaying.value = wavesurfer.isPlaying() ? '1' : '0';
  });

  document.querySelectorAll('[data-marker-cancel]').forEach((button) => {
    button.addEventListener('click', () => { markerComposer.hidden = true; });
  });

  document.querySelectorAll('[data-marker-seek]').forEach((button) => {
    button.addEventListener('click', () => {
      const seconds = Number(button.dataset.markerSeek) / 1000;
      wavesurfer.setTime(seconds);
      current.textContent = formatTime(seconds);
    });
  });
})();
