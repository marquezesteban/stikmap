<?php
/** @var array<string, mixed> $song */
/** @var string|null $audioError */
/** @var array{type: string, message: string}|null $flashMessage */
/** @var list<array<string, mixed>> $markers */
/** @var list<array{id: int, code: string, label: string}> $markerTypes */
/** @var list<array{id: int, line_number: int, content: string}> $lyricLines */
/** @var array<string, string> $markerErrors */
/** @var array<string, mixed> $markerInput */
/** @var int $resumeMs */
/** @var bool $resumePlaying */
$hasAudio = $song['audio_filename'] !== null;
$markerFormOpen = $markerErrors !== [];
$selectedMarkerType = (int) ($markerInput['marker_type_id'] ?? 0);
$selectedTime = (int) ($markerInput['time_ms'] ?? 0);
$selectedLyricLineId = (int) ($markerInput['lyric_line_id'] ?? 0);
$editingMarkerId = (int) ($markerInput['id'] ?? 0);
$markerCreateAction = appUrl('marker-store', ['id' => (int) $song['id']]);
$markerFormAction = $editingMarkerId > 0
    ? appUrl('marker-update', ['id' => (int) $song['id'], 'marker_id' => $editingMarkerId])
    : $markerCreateAction;
$lyrics = trim((string) ($song['lyrics'] ?? ''));
$lyricsDisplayLines = $lyrics === '' ? [] : preg_split('/\R/u', $lyrics);
$lyricsDisplayLines = $lyricsDisplayLines === false ? [] : $lyricsDisplayLines;
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
            data-resume-ms="<?= $resumeMs ?>"
            data-resume-playing="<?= $resumePlaying ? '1' : '0' ?>"
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

            <div class="zoom-controls" aria-label="Zoom de la forma de onda">
                <button class="zoom-button" type="button" data-zoom-step="-10" aria-label="Alejar forma de onda" disabled>−</button>
                <input
                    type="range"
                    min="0"
                    max="180"
                    step="10"
                    value="0"
                    data-player-zoom
                    aria-label="Nivel de zoom"
                    disabled
                >
                <button class="zoom-button" type="button" data-zoom-step="10" aria-label="Acercar forma de onda" disabled>+</button>
                <button class="zoom-reset" type="button" data-zoom-reset disabled>Ver completa</button>
                <span class="zoom-value" data-zoom-value>Vista completa</span>
            </div>

            <div class="player-controls">
                <button class="transport-button transport-skip" type="button" data-player-skip="-10" aria-label="Retroceder 10 segundos">−10</button>
                <button class="transport-button transport-main" type="button" data-player-toggle aria-label="Reproducir" disabled>
                    <span data-player-icon aria-hidden="true">▶</span>
                </button>
                <button class="transport-button transport-skip" type="button" data-player-skip="10" aria-label="Adelantar 10 segundos">+10</button>
                <div class="player-time" aria-live="off">
                    <span data-player-current>0:00.000</span>
                    <span aria-hidden="true">/</span>
                    <span data-player-duration>0:00.000</span>
                </div>
            </div>

            <button class="mark-now-button" type="button" data-marker-capture disabled>
                <span aria-hidden="true">＋</span>
                Marcar ahora
            </button>

            <p class="player-error" data-player-error role="alert" hidden></p>
        </section>

        <section class="marker-composer" data-marker-composer<?= $markerFormOpen ? '' : ' hidden' ?>>
            <div class="marker-composer-heading">
                <div>
                    <p class="eyebrow mb-1" data-marker-mode><?= $editingMarkerId > 0 ? 'Editar marca' : 'Nueva marca' ?></p>
                    <div class="marker-time-editor-row">
                        <h2>Instante</h2>
                        <input
                            class="marker-time-editor"
                            type="text"
                            value="<?= escape(formatMarkerTime($selectedTime)) ?>"
                            inputmode="decimal"
                            pattern="[0-9]+:[0-5][0-9]([.,][0-9]{1,3})?"
                            title="Usá el formato minutos:segundos.milisegundos, por ejemplo 1:23.456"
                            aria-label="Tiempo exacto de la marca"
                            data-marker-time-editor
                            required
                        >
                    </div>
                    <small class="marker-time-help">Formato: 1:23.456</small>
                    <button class="marker-use-current" type="button" data-marker-use-current>
                        Usar posición actual
                    </button>
                </div>
                <button class="marker-close" type="button" data-marker-cancel aria-label="Cerrar formulario">×</button>
            </div>

            <form
                method="post"
                action="<?= escape($markerFormAction) ?>"
                data-create-action="<?= escape($markerCreateAction) ?>"
            >
                <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
                <input type="hidden" name="time_ms" value="<?= $selectedTime ?>" data-marker-time-input>
                <input type="hidden" name="resume_playing" value="0" data-marker-resume-playing>

                <div class="marker-form-grid">
                    <div class="field-group">
                        <label for="marker-type">Tipo</label>
                        <select
                            class="app-input<?= isset($markerErrors['marker_type_id']) ? ' is-invalid' : '' ?>"
                            id="marker-type"
                            name="marker_type_id"
                            required
                        >
                            <option value="">Elegir tipo…</option>
                            <?php foreach ($markerTypes as $type): ?>
                                <option value="<?= (int) $type['id'] ?>"<?= $selectedMarkerType === (int) $type['id'] ? ' selected' : '' ?>>
                                    <?= escape($type['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($markerErrors['marker_type_id'])): ?>
                            <p class="field-error"><?= escape($markerErrors['marker_type_id']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field-group">
                        <label for="marker-note">Nota <span class="optional">Opcional</span></label>
                        <textarea
                            class="app-input app-textarea<?= isset($markerErrors['note']) ? ' is-invalid' : '' ?>"
                            id="marker-note"
                            name="note"
                            maxlength="240"
                            rows="3"
                            placeholder="Ej.: entrada con toms"
                        ><?= escape((string) ($markerInput['note'] ?? '')) ?></textarea>
                        <?php if (isset($markerErrors['note'])): ?>
                            <p class="field-error"><?= escape($markerErrors['note']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field-group marker-lyric-field">
                        <label for="marker-lyric-line">Línea de letra <span class="optional">Opcional</span></label>
                        <?php if ($lyricLines === []): ?>
                            <select class="app-input" id="marker-lyric-line" name="lyric_line_id" disabled>
                                <option value="">Primero agregá la letra de la canción</option>
                            </select>
                            <p class="field-help">Podés guardar la marca ahora y asociarla después.</p>
                        <?php else: ?>
                            <select
                                class="app-input<?= isset($markerErrors['lyric_line_id']) ? ' is-invalid' : '' ?>"
                                id="marker-lyric-line"
                                name="lyric_line_id"
                            >
                                <option value="">Sin línea asociada</option>
                                <?php foreach ($lyricLines as $line): ?>
                                    <option value="<?= (int) $line['id'] ?>"<?= $selectedLyricLineId === (int) $line['id'] ? ' selected' : '' ?>>
                                        <?php if (isLyricSection((string) $line['content'])): ?>
                                            <?= escape('SECCIÓN · ' . lyricSectionLabel((string) $line['content'])) ?>
                                        <?php else: ?>
                                            <?= escape('LETRA ' . (string) $line['line_number'] . ' · ' . (string) $line['content']) ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($markerErrors['lyric_line_id'])): ?>
                                <p class="field-error"><?= escape($markerErrors['lyric_line_id']) ?></p>
                            <?php else: ?>
                                <p class="field-help">Elegí el verso que te sirve como referencia para tocar.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (isset($markerErrors['time_ms'])): ?>
                    <p class="field-error marker-time-error"><?= escape($markerErrors['time_ms']) ?></p>
                <?php endif; ?>

                <div class="marker-form-actions">
                    <button class="btn-app btn-app-secondary" type="button" data-marker-cancel>Cancelar</button>
                    <button class="btn-app btn-app-primary" type="submit" data-marker-submit>
                        <?= $editingMarkerId > 0 ? 'Guardar cambios' : 'Guardar marca' ?>
                    </button>
                </div>
            </form>
        </section>

        <section class="markers-panel" aria-labelledby="markers-title">
            <div class="markers-heading">
                <div>
                    <p class="eyebrow mb-1">Mapa temporal</p>
                    <h2 id="markers-title">Marcas</h2>
                </div>
                <span><?= count($markers) ?></span>
            </div>

            <?php if ($markers === []): ?>
                <div class="markers-empty">
                    <p>Todavía no hay marcas.</p>
                    <span>Reproducí la canción y tocá “Marcar ahora” en el instante que quieras recordar.</span>
                </div>
            <?php else: ?>
                <div class="marker-list">
                    <?php foreach ($markers as $marker): ?>
                        <article
                            class="marker-item"
                            data-marker-type="<?= escape((string) $marker['type_code']) ?>"
                        >
                            <button
                                class="marker-main"
                                type="button"
                                data-marker-seek="<?= (int) $marker['time_ms'] ?>"
                                data-marker-id="<?= (int) $marker['id'] ?>"
                                data-marker-label="<?= escape((string) $marker['type_label']) ?>"
                                aria-label="Ir a <?= escape(formatMarkerTime((int) $marker['time_ms'])) ?>, <?= escape((string) $marker['type_label']) ?>"
                            >
                                <span class="marker-time"><?= escape(formatMarkerTime((int) $marker['time_ms'])) ?></span>
                                <span class="marker-dot" aria-hidden="true"></span>
                                <span class="marker-content">
                                    <strong><?= escape((string) $marker['type_label']) ?></strong>
                                    <?php if ($marker['note'] !== null): ?>
                                        <small><?= escape((string) $marker['note']) ?></small>
                                    <?php endif; ?>
                                    <?php if ($marker['lyric_line_content'] !== null): ?>
                                        <?php $associatedSection = isLyricSection((string) $marker['lyric_line_content']); ?>
                                        <span class="marker-lyric<?= $associatedSection ? ' is-section' : '' ?>">
                                            <?php if ($associatedSection): ?>
                                                Sección · <?= escape(lyricSectionLabel((string) $marker['lyric_line_content'])) ?>
                                            <?php else: ?>
                                                <span aria-hidden="true">“</span><?= escape((string) $marker['lyric_line_content']) ?><span aria-hidden="true">”</span>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                                <span class="marker-jump" aria-hidden="true">▶</span>
                            </button>

                            <div class="marker-actions">
                                <button
                                    class="marker-action"
                                    type="button"
                                    data-marker-edit
                                    data-marker-id="<?= (int) $marker['id'] ?>"
                                    data-time-ms="<?= (int) $marker['time_ms'] ?>"
                                    data-marker-type-id="<?= (int) $marker['marker_type_id'] ?>"
                                    data-note="<?= escape((string) ($marker['note'] ?? '')) ?>"
                                    data-lyric-line-id="<?= (int) ($marker['lyric_line_id'] ?? 0) ?>"
                                    data-update-action="<?= escape(appUrl('marker-update', ['id' => (int) $song['id'], 'marker_id' => (int) $marker['id']])) ?>"
                                >Editar</button>
                                <form
                                    method="post"
                                    action="<?= escape(appUrl('marker-delete', ['id' => (int) $song['id'], 'marker_id' => (int) $marker['id']])) ?>"
                                    data-confirm-marker-delete="<?= escape(formatMarkerTime((int) $marker['time_ms']) . ' · ' . (string) $marker['type_label']) ?>"
                                    data-marker-delete-form
                                >
                                    <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
                                    <input type="hidden" name="resume_ms" value="<?= (int) $marker['time_ms'] ?>" data-delete-resume-ms>
                                    <input type="hidden" name="resume_playing" value="0" data-delete-resume-playing>
                                    <button class="marker-action is-danger" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <details class="replace-audio"<?= $audioError !== null ? ' open' : '' ?>>
            <summary>Reemplazar MP3</summary>
            <div class="replace-audio-content">
                <p>El archivo actual será eliminado cuando termine correctamente la nueva carga.</p>
                <?php require __DIR__ . '/_audio_form.php'; ?>
            </div>
        </details>
    <?php endif; ?>

    <section class="lyrics-panel" aria-labelledby="lyrics-title">
        <div class="lyrics-heading">
            <div>
                <p class="eyebrow mb-1">Guía de la canción</p>
                <h2 id="lyrics-title">Letra</h2>
            </div>
            <a class="lyrics-edit" href="<?= escape(appUrl('edit', ['id' => (int) $song['id']])) ?>">
                <?= $lyrics === '' ? 'Agregar letra' : 'Editar letra' ?>
            </a>
        </div>

        <?php if ($lyrics === ''): ?>
            <div class="lyrics-empty">
                <p>Todavía no cargaste la letra.</p>
                <span>Podés pegarla completa y conservar sus versos para el próximo paso: asociarlos con las marcas.</span>
            </div>
        <?php else: ?>
            <div class="lyrics-content">
                <?php foreach ($lyricsDisplayLines as $line): ?>
                    <?php $isSection = isLyricSection($line); ?>
                    <div class="lyrics-line<?= $isSection ? ' is-section' : ($line === '' ? ' is-empty' : '') ?>">
                        <?= $isSection ? escape(lyricSectionLabel($line)) : escape($line) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</section>
