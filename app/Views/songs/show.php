<?php
/** @var array<string, mixed> $song */
/** @var string|null $audioError */
/** @var array{type: string, message: string}|null $flashMessage */
$hasAudio = $song['audio_filename'] !== null;
?>
<section class="song-workspace">
    <a class="back-link" href="<?= escape(appUrl()) ?>"><span aria-hidden="true">←</span> Canciones</a>

    <header class="song-detail-heading">
        <div>
            <p class="eyebrow mb-2"><?= $hasAudio ? 'Audio preparado' : 'Preparar canción' ?></p>
            <h1 class="song-detail-title mb-2"><?= escape((string) $song['title']) ?></h1>
            <p class="page-subtitle mb-0"><?= escape($song['artist'] ?: 'Artista sin especificar') ?></p>
        </div>
        <a class="btn-app btn-app-secondary" href="<?= escape(appUrl('edit', ['id' => (int) $song['id']])) ?>">Editar datos</a>
    </header>

    <?php if ($flashMessage !== null): ?>
        <div class="app-notice" role="status" data-auto-dismiss>
            <span class="notice-dot" aria-hidden="true"></span>
            <?= escape($flashMessage['message']) ?>
        </div>
    <?php endif; ?>

    <?php if (!$hasAudio): ?>
        <section class="audio-empty-panel">
            <div class="audio-empty-copy">
                <span class="section-number">01</span>
                <div>
                    <h2>Sumá el audio</h2>
                    <p>El MP3 se guarda localmente y será la base para construir el mapa temporal.</p>
                </div>
            </div>
            <?php require __DIR__ . '/_audio_form.php'; ?>
        </section>
    <?php else: ?>
        <section
            class="player-panel"
            data-audio-player
            data-audio-url="<?= escape(audioUrl((string) $song['audio_filename'])) ?>"
        >
            <div class="player-topline">
                <div>
                    <p class="eyebrow mb-1">Forma de onda</p>
                    <p class="audio-filename mb-0"><?= escape((string) $song['audio_original_name']) ?></p>
                </div>
                <span class="audio-size"><?= escape(formatFileSize((int) $song['audio_size_bytes'])) ?></span>
            </div>

            <div class="waveform-shell">
                <div id="waveform" aria-label="Forma de onda de <?= escape((string) $song['title']) ?>"></div>
                <div class="waveform-loading" data-player-loading>Cargando forma de onda…</div>
            </div>

            <div class="player-controls">
                <button class="transport-button transport-skip" type="button" data-player-skip="-10" aria-label="Retroceder 10 segundos">−10</button>
                <button class="transport-button transport-main" type="button" data-player-toggle aria-label="Reproducir" disabled>
                    <span data-player-icon aria-hidden="true">▶</span>
                </button>
                <button class="transport-button transport-skip" type="button" data-player-skip="10" aria-label="Adelantar 10 segundos">+10</button>
                <div class="player-time" aria-live="off">
                    <span data-player-current>0:00</span>
                    <span aria-hidden="true">/</span>
                    <span data-player-duration>0:00</span>
                </div>
            </div>

            <p class="player-error" data-player-error role="alert" hidden></p>
        </section>

        <details class="replace-audio"<?= $audioError !== null ? ' open' : '' ?>>
            <summary>Reemplazar MP3</summary>
            <div class="replace-audio-content">
                <p>El archivo actual será eliminado cuando termine correctamente la nueva carga.</p>
                <?php require __DIR__ . '/_audio_form.php'; ?>
            </div>
        </details>
    <?php endif; ?>
</section>
