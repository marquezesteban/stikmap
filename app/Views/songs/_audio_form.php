<?php
/** @var array<string, mixed> $song */
/** @var string|null $audioError */
$hasAudio = ($song['audio_filename'] ?? null) !== null;
?>
<form
    class="audio-upload-form"
    method="post"
    action="<?= escape(appUrl('audio-upload', ['id' => (int) $song['id']])) ?>"
    enctype="multipart/form-data"
>
    <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
    <input type="hidden" name="MAX_FILE_SIZE" value="39845888">

    <label class="audio-dropzone<?= $audioError !== null ? ' has-error' : '' ?>" for="audio-file">
        <input
            id="audio-file"
            name="audio"
            type="file"
            accept=".mp3,audio/mpeg"
            data-audio-input
        >
        <span class="upload-icon" aria-hidden="true">↑</span>
        <span class="upload-copy">
            <strong data-file-label><?= $hasAudio ? 'Elegir otro MP3' : 'Elegir archivo MP3' ?></strong>
            <small>Hasta 38 MB</small>
        </span>
    </label>

    <?php if ($audioError !== null): ?>
        <p class="field-error audio-error" role="alert"><?= escape($audioError) ?></p>
    <?php endif; ?>

    <button class="btn-app btn-app-primary upload-submit" type="submit">
        <?= $hasAudio ? 'Reemplazar audio' : 'Cargar audio' ?>
    </button>
</form>
