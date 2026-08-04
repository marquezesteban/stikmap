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
        <p class="page-subtitle mb-0">
            <?= $isEditing ? 'Actualizá los datos y la letra.' : 'Completá lo esencial y, si querés, sumá la letra y el audio ahora.' ?>
        </p>
    </div>

    <form
        class="song-form"
        method="post"
        action="<?= escape($isEditing ? appUrl('update', ['id' => (int) $song['id']]) : appUrl('store')) ?>"
        enctype="multipart/form-data"
        novalidate
    >
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

        <div class="field-group">
            <label for="lyrics">Letra <span class="optional">Opcional</span></label>
            <textarea
                class="app-input lyrics-input<?= isset($errors['lyrics']) ? ' is-invalid' : '' ?>"
                id="lyrics"
                name="lyrics"
                maxlength="50000"
                rows="14"
                spellcheck="true"
                placeholder="Pegá acá la letra respetando sus líneas…"
                aria-describedby="<?= isset($errors['lyrics']) ? 'lyrics-error' : 'lyrics-help' ?>"
            ><?= escape((string) ($song['lyrics'] ?? '')) ?></textarea>
            <?php if (isset($errors['lyrics'])): ?>
                <p class="field-error" id="lyrics-error"><?= escape($errors['lyrics']) ?></p>
            <?php else: ?>
                <p class="field-help" id="lyrics-help">Conservaremos los versos y espacios tal como los pegues.</p>
            <?php endif; ?>
        </div>

        <?php if (!$isEditing): ?>
            <div class="field-group create-audio-field">
                <label for="song-audio-file">Audio MP3 <span class="optional">Opcional</span></label>
                <label class="audio-dropzone<?= isset($errors['audio']) ? ' has-error' : '' ?>" for="song-audio-file">
                    <input
                        id="song-audio-file"
                        name="audio"
                        type="file"
                        accept=".mp3,audio/mpeg"
                        data-audio-input
                        aria-describedby="<?= isset($errors['audio']) ? 'audio-error' : 'audio-help' ?>"
                    >
                    <span class="upload-icon" aria-hidden="true">↑</span>
                    <span class="upload-copy">
                        <strong data-file-label>Elegir archivo MP3</strong>
                        <small>Hasta 38 MB · también podés cargarlo después</small>
                    </span>
                </label>
                <?php if (isset($errors['audio'])): ?>
                    <p class="field-error audio-error" id="audio-error" role="alert"><?= escape($errors['audio']) ?></p>
                <?php else: ?>
                    <p class="field-help" id="audio-help">Si lo cargás ahora, al crear la canción iremos directo al reproductor.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="form-actions">
            <a class="btn-app btn-app-secondary" href="<?= escape(appUrl()) ?>">Cancelar</a>
            <button class="btn-app btn-app-primary" type="submit">
                <?= $isEditing ? 'Guardar cambios' : 'Crear canción' ?>
            </button>
        </div>
    </form>
</section>
