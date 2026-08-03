<?php /** @var string $message */ ?>
<section class="empty-state error-state">
    <p class="eyebrow">DrumMap</p>
    <h1>Algo salió mal</h1>
    <p><?= escape($message) ?></p>
    <a class="btn-app btn-app-primary" href="<?= escape(appUrl()) ?>">Volver a canciones</a>
</section>
