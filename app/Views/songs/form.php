<?php
/** @var array<string, mixed> $song */
/** @var array<string, string> $errors */
/** @var bool $isEditing */
?>
<section class="form-page">
    <a class="back-link" href="<?= escape(appUrl()) ?>"><span aria-hidden="true">←</span> Canciones</a>

    <div class="form-heading">
        <p class="eyebrow mb-2"><?= $isEditing ? 'Ajustar datos' : 'Sumar al repertorio' ?></p>
        <h1 class="display-title mb-2"><?= $isEditing ? 'Editar canción' : 'Nueva canción' ?></h1>
        <p class="page-subtitle mb-0">Por ahora sólo necesitamos lo esencial.</p>
    </div>

    <form class="song-form" method="post" action="<?= escape($isEditing ? appUrl('update', ['id' => (int) $song['id']]) : appUrl('store')) ?>" novalidate>
        <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">

        <div class="field-group">
            <label for="title">Título <span aria-hidden="true">*</span></label>
            <input
                class="app-input<?= isset($errors['title']) ? ' is-invalid' : '' ?>"
                id="title"
                name="title"
                type="text"
                value="<?= escape((string) $song['title']) ?>"
                maxlength="120"
                autocomplete="off"
                autofocus
                required
                aria-describedby="<?= isset($errors['title']) ? 'title-error' : 'title-help' ?>"
            >
            <?php if (isset($errors['title'])): ?>
                <p class="field-error" id="title-error"><?= escape($errors['title']) ?></p>
            <?php else: ?>
                <p class="field-help" id="title-help">El nombre con el que la reconocés en el set.</p>
            <?php endif; ?>
        </div>

        <div class="field-group">
            <label for="artist">Artista <span class="optional">Opcional</span></label>
            <input
                class="app-input<?= isset($errors['artist']) ? ' is-invalid' : '' ?>"
                id="artist"
                name="artist"
                type="text"
                value="<?= escape((string) ($song['artist'] ?? '')) ?>"
                maxlength="120"
                autocomplete="off"
                aria-describedby="<?= isset($errors['artist']) ? 'artist-error' : 'artist-help' ?>"
            >
            <?php if (isset($errors['artist'])): ?>
                <p class="field-error" id="artist-error"><?= escape($errors['artist']) ?></p>
            <?php else: ?>
                <p class="field-help" id="artist-help">Podés dejarlo vacío y completarlo después.</p>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <a class="btn-app btn-app-secondary" href="<?= escape(appUrl()) ?>">Cancelar</a>
            <button class="btn-app btn-app-primary" type="submit">
                <?= $isEditing ? 'Guardar cambios' : 'Crear canción' ?>
            </button>
        </div>
    </form>
</section>
