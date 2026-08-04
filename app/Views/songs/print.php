<?php
/** @var array<string, mixed> $song */
/** @var list<array<string, mixed>> $markers */
/** @var list<array{id: int, line_number: int, content: string}> $lyricLines */
$lyrics = trim((string) ($song['lyrics'] ?? ''));
$lyricsDisplayLines = $lyrics === '' ? [] : preg_split('/\R/u', $lyrics);
$lyricsDisplayLines = $lyricsDisplayLines === false ? [] : $lyricsDisplayLines;
$lyricLinesByNumber = [];
$markersByLyricLine = [];
$unassociatedMarkers = [];

foreach ($lyricLines as $line) {
    $lyricLinesByNumber[(int) $line['line_number']] = $line;
}

foreach ($markers as $marker) {
    $lyricLineId = (int) ($marker['lyric_line_id'] ?? 0);

    if ($lyricLineId > 0) {
        $markersByLyricLine[$lyricLineId][] = $marker;
    } else {
        $unassociatedMarkers[] = $marker;
    }
}
?>
<div class="print-toolbar" aria-label="Acciones de impresión">
    <a href="<?= escape(appUrl('show', ['id' => (int) $song['id']])) ?>">← Volver a la canción</a>
    <button type="button" data-print-trigger>Imprimir o guardar PDF</button>
</div>

<main class="cheat-sheet">
    <header class="cheat-header">
        <div class="cheat-brand">
            <span class="cheat-brand-mark" aria-hidden="true"><i></i><i></i><i></i></span>
            <span>StikMap</span>
        </div>
        <div class="cheat-title-row">
            <div>
                <p class="cheat-kicker">Cancionero de batería</p>
                <h1><?= escape((string) $song['title']) ?></h1>
                <p class="cheat-artist"><?= escape($song['artist'] ?: 'Artista sin especificar') ?></p>
            </div>
            <div class="cheat-summary">
                <strong><?= count($markers) ?></strong>
                <span><?= count($markers) === 1 ? 'marca' : 'marcas' ?></span>
            </div>
        </div>
    </header>

    <?php if ($markers === []): ?>
        <section class="cheat-empty">
            <h2>Esta canción todavía no tiene marcas</h2>
            <p>Volvé al reproductor y agregá las referencias que quieras incluir en el cancionero.</p>
        </section>
    <?php elseif ($lyricsDisplayLines === []): ?>
        <section class="print-fallback-heading">
            <h2>Mapa temporal</h2>
            <p>Agregá la letra para convertir esta lista en un cancionero anotado.</p>
        </section>
        <ol class="cheat-timeline" aria-label="Marcas de la canción">
            <?php foreach ($markers as $marker): ?>
                <li class="cheat-marker" data-marker-type="<?= escape((string) $marker['type_code']) ?>">
                    <time><?= escape(formatMarkerTime((int) $marker['time_ms'])) ?></time>
                    <span class="cheat-dot" aria-hidden="true"></span>
                    <div class="cheat-marker-content">
                        <h2><?= escape((string) $marker['type_label']) ?></h2>
                        <?php if ($marker['note'] !== null): ?>
                            <p class="cheat-note"><?= escape((string) $marker['note']) ?></p>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php else: ?>
        <section class="songbook" aria-label="Letra anotada de la canción">
            <?php foreach ($lyricsDisplayLines as $index => $lineContent): ?>
                <?php
                $lineNumber = $index + 1;
                $lyricLine = $lyricLinesByNumber[$lineNumber] ?? null;
                $lineMarkers = $lyricLine === null
                    ? []
                    : ($markersByLyricLine[(int) $lyricLine['id']] ?? []);
                $lineIsSection = isLyricSection($lineContent);
                ?>

                <?php if ($lineContent === ''): ?>
                    <div class="songbook-spacer" aria-hidden="true"></div>
                <?php else: ?>
                    <div class="songbook-entry<?= $lineIsSection ? ' is-section' : '' ?>">
                        <?php if ($lineMarkers !== []): ?>
                            <div class="songbook-cues" aria-label="Indicaciones antes de esta línea">
                                <?php foreach ($lineMarkers as $marker): ?>
                                    <div class="songbook-cue" data-marker-type="<?= escape((string) $marker['type_code']) ?>">
                                        <time><?= escape(formatMarkerTime((int) $marker['time_ms'])) ?></time>
                                        <strong><?= escape((string) $marker['type_label']) ?></strong>
                                        <?php if ($marker['note'] !== null): ?>
                                            <span><?= escape((string) $marker['note']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($lineIsSection): ?>
                            <h2><?= escape(lyricSectionLabel($lineContent)) ?></h2>
                        <?php else: ?>
                            <p><?= escape($lineContent) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </section>

        <?php if ($unassociatedMarkers !== []): ?>
            <section class="unassociated-markers" aria-labelledby="unassociated-title">
                <div>
                    <h2 id="unassociated-title">Marcas sin ubicación en la letra</h2>
                    <p>Asocialas con una línea o sección para verlas dentro del cancionero.</p>
                </div>
                <ul>
                    <?php foreach ($unassociatedMarkers as $marker): ?>
                        <li data-marker-type="<?= escape((string) $marker['type_code']) ?>">
                            <time><?= escape(formatMarkerTime((int) $marker['time_ms'])) ?></time>
                            <strong><?= escape((string) $marker['type_label']) ?></strong>
                            <?php if ($marker['note'] !== null): ?>
                                <span><?= escape((string) $marker['note']) ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>
    <?php endif; ?>

    <footer class="cheat-footer">
        <span>Diseñado y creado por Esteban Marquez · © 2026</span>
        <span>StikMap v<?= escape(appVersion()) ?> · GNU AGPL v3</span>
    </footer>
</main>
