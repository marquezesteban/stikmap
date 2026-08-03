<?php
/** @var list<array<string, mixed>> $songs */
/** @var array{type: string, message: string}|null $flashMessage */
$songCount = count($songs);
?>
<section class="page-heading d-flex align-items-end justify-content-between gap-3">
    <div>
        <p class="eyebrow mb-2">Tu repertorio</p>
        <h1 class="display-title mb-1">Canciones</h1>
        <p class="page-subtitle mb-0">
            <?= $songCount === 0 ? 'Empezá armando tu primer mapa.' : $songCount . ($songCount === 1 ? ' canción lista para trabajar.' : ' canciones listas para trabajar.') ?>
        </p>
    </div>
    <a class="btn-app btn-app-primary d-none d-sm-inline-flex" href="<?= escape(appUrl('create')) ?>">
        <span aria-hidden="true">＋</span> Nueva canción
    </a>
</section>

<?php if ($flashMessage !== null): ?>
    <div class="app-notice" role="status" data-auto-dismiss>
        <span class="notice-dot" aria-hidden="true"></span>
        <?= escape($flashMessage['message']) ?>
    </div>
<?php endif; ?>

<?php if ($songs === []): ?>
    <section class="empty-state">
        <div class="empty-visual" aria-hidden="true">
            <span></span><span></span><span></span><span></span><span></span>
        </div>
        <h2>Tu set empieza acá</h2>
        <p>Agregá una canción para comenzar a construir su mapa de batería.</p>
        <a class="btn-app btn-app-primary" href="<?= escape(appUrl('create')) ?>">
            <span aria-hidden="true">＋</span> Crear primera canción
        </a>
    </section>
<?php else: ?>
    <div class="song-grid">
        <?php foreach ($songs as $index => $song): ?>
            <article class="song-card">
                <div class="song-number" aria-hidden="true"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></div>
                <div class="song-info">
                    <h2><a href="<?= escape(appUrl('show', ['id' => (int) $song['id']])) ?>"><?= escape((string) $song['title']) ?></a></h2>
                    <p><?= escape($song['artist'] ?: 'Artista sin especificar') ?></p>
                    <span class="song-status<?= $song['audio_filename'] === null ? '' : ' is-ready' ?>">
                        <?= $song['audio_filename'] === null ? 'Sin audio' : 'Audio listo' ?>
                    </span>
                </div>
                <div class="song-actions">
                    <a class="btn-icon-text btn-open-song" href="<?= escape(appUrl('show', ['id' => (int) $song['id']])) ?>">
                        Abrir
                    </a>
                    <a class="btn-icon-text" href="<?= escape(appUrl('edit', ['id' => (int) $song['id']])) ?>">
                        Editar
                    </a>
                    <form method="post" action="<?= escape(appUrl('delete', ['id' => (int) $song['id']])) ?>" data-confirm-delete="<?= escape((string) $song['title']) ?>">
                        <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
                        <button class="btn-icon-text btn-danger-subtle" type="submit">Eliminar</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<a class="mobile-create d-sm-none" href="<?= escape(appUrl('create')) ?>" aria-label="Crear nueva canción">
    <span aria-hidden="true">＋</span>
</a>
