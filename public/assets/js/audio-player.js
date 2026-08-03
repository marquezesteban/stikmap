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
  const markerMode = markerComposer?.querySelector('[data-marker-mode]');
  const markerType = markerComposer?.querySelector('[name="marker_type_id"]');
  const markerNote = markerComposer?.querySelector('[name="note"]');
  const markerSubmit = markerComposer?.querySelector('[data-marker-submit]');

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

  const regions = window.WaveSurfer.Regions
    ? wavesurfer.registerPlugin(window.WaveSurfer.Regions.create())
    : null;

  const markerColors = {
    intro: '#ff6b45',
    verse: '#79a8ff',
    chorus: '#ffd166',
    fill: '#63d69a',
    break: '#ff7b7b',
    ride: '#62d8db',
    solo: '#c792ea',
    intensity_up: '#ff9f43',
    intensity_down: '#8b96a5',
    ending: '#f5f1e8',
  };

  wavesurfer.on('ready', (seconds) => {
    duration.textContent = formatTime(seconds);
    loading.hidden = true;
    toggle.disabled = false;
    markerCapture.disabled = false;
    zoomControls.forEach((control) => { control.disabled = false; });

    if (regions) {
      document.querySelectorAll('[data-marker-seek]').forEach((button) => {
        const markerPoint = document.createElement('span');
        const markerItem = button.closest('[data-marker-type]');
        const color = markerColors[markerItem?.dataset.markerType] || '#ff6b45';
        markerPoint.title = `${button.dataset.markerLabel} · ${formatPreciseTime(button.dataset.markerSeek)}`;
        markerPoint.setAttribute('aria-label', markerPoint.title);
        Object.assign(markerPoint.style, {
          width: '14px',
          height: '14px',
          display: 'block',
          border: '2px solid #111315',
          borderRadius: '50%',
          background: color,
          boxShadow: `0 0 0 3px ${color}33`,
          cursor: 'pointer',
          transform: 'translate(-50%, -50%)',
        });

        regions.addRegion({
          id: `marker-${button.dataset.markerId}`,
          start: Number(button.dataset.markerSeek) / 1000,
          content: markerPoint,
          color: 'transparent',
          drag: false,
          resize: false,
        });
      });
    }

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
    markerForm.action = markerForm.dataset.createAction;
    markerTimeInput.value = String(timeMs);
    markerTimeLabel.textContent = formatPreciseTime(timeMs);
    markerMode.textContent = 'Nueva marca';
    markerType.value = '';
    markerNote.value = '';
    markerSubmit.textContent = 'Guardar marca';
    markerComposer.hidden = false;
    markerType.focus();
    markerComposer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  });

  markerComposer.querySelector('[data-marker-use-current]').addEventListener('click', () => {
    const timeMs = Math.round(wavesurfer.getCurrentTime() * 1000);
    markerTimeInput.value = String(timeMs);
    markerTimeLabel.textContent = formatPreciseTime(timeMs);
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

  document.querySelectorAll('[data-marker-edit]').forEach((button) => {
    button.addEventListener('click', () => {
      const timeMs = Number(button.dataset.timeMs);
      markerForm.action = button.dataset.updateAction;
      markerTimeInput.value = String(timeMs);
      markerTimeLabel.textContent = formatPreciseTime(timeMs);
      markerMode.textContent = 'Editar marca';
      markerType.value = button.dataset.markerTypeId;
      markerNote.value = button.dataset.note;
      markerSubmit.textContent = 'Guardar cambios';
      wavesurfer.setTime(timeMs / 1000);
      markerComposer.hidden = false;
      markerType.focus();
      markerComposer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
  });

  document.querySelectorAll('[data-marker-delete-form]').forEach((form) => {
    form.addEventListener('submit', () => {
      form.querySelector('[data-delete-resume-ms]').value = String(Math.round(wavesurfer.getCurrentTime() * 1000));
      form.querySelector('[data-delete-resume-playing]').value = wavesurfer.isPlaying() ? '1' : '0';
    });
  });

  regions?.on('region-clicked', (region, event) => {
    event.stopPropagation();
    wavesurfer.setTime(region.start);
    current.textContent = formatTime(region.start);
  });
})();
