<?php
/** @var array<string, mixed> $song */
/** @var list<array<string, mixed>> $markers */
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
                <p class="cheat-kicker">Machete de batería</p>
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
            <p>Volvé al reproductor y agregá las referencias que quieras incluir en el machete.</p>
        </section>
    <?php else: ?>
        <ol class="cheat-timeline" aria-label="Marcas de la canción">
            <?php foreach ($markers as $marker): ?>
                <?php
                $lyricContent = $marker['lyric_line_content'] === null
                    ? null
                    : (string) $marker['lyric_line_content'];
                $lyricIsSection = $lyricContent !== null && isLyricSection($lyricContent);
                ?>
                <li class="cheat-marker" data-marker-type="<?= escape((string) $marker['type_code']) ?>">
                    <time datetime="PT<?= number_format((int) $marker['time_ms'] / 1000, 3, '.', '') ?>S">
                        <?= escape(formatMarkerTime((int) $marker['time_ms'])) ?>
                    </time>
                    <span class="cheat-dot" aria-hidden="true"></span>
                    <div class="cheat-marker-content">
                        <h2><?= escape((string) $marker['type_label']) ?></h2>
                        <?php if ($marker['note'] !== null): ?>
                            <p class="cheat-note"><?= escape((string) $marker['note']) ?></p>
                        <?php endif; ?>
                        <?php if ($lyricContent !== null): ?>
                            <p class="cheat-reference<?= $lyricIsSection ? ' is-section' : '' ?>">
                                <?php if ($lyricIsSection): ?>
                                    Sección · <?= escape(lyricSectionLabel($lyricContent)) ?>
                                <?php else: ?>
                                    <span aria-hidden="true">“</span><?= escape($lyricContent) ?><span aria-hidden="true">”</span>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <footer class="cheat-footer">
        <span>Diseñado y creado por Esteban Marquez · © 2026</span>
        <span>StikMap v<?= escape(appVersion()) ?> · GNU AGPL v3</span>
    </footer>
</main>
