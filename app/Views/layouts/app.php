<?php
/** @var string $content */
/** @var string $pageTitle */
/** @var list<string>|null $pageScripts */
$pageScripts = $pageScripts ?? [];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#111315">
    <title><?= escape($pageTitle) ?> · StikMap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= escape(assetUrl('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
    <header class="app-header">
        <div class="container app-shell d-flex align-items-center justify-content-between gap-3">
            <a class="brand" href="<?= escape(appUrl()) ?>" aria-label="Ir a canciones">
                <span class="brand-mark" aria-hidden="true"><i></i><i></i><i></i></span>
                <span>StikMap</span>
            </a>
            <span class="header-context d-none d-sm-inline">Mapa de batería</span>
        </div>
    </header>

    <main class="container app-shell app-main">
        <?= $content ?>
    </main>

    <footer class="app-footer">
        <div class="container app-shell app-footer-content">
            <div class="app-footer-author">
                <span>Diseñado y creado por Esteban Marquez · © 2026</span>
                <a href="mailto:marquezesteban@gmail.com">marquezesteban@gmail.com</a>
            </div>
            <div class="app-footer-legal">
                <span class="app-version" aria-label="Versión <?= escape(appVersion()) ?>">v<?= escape(appVersion()) ?></span>
                <a href="https://github.com/marquezesteban/stikmap/blob/main/LICENSE" target="_blank" rel="noopener noreferrer">
                    Código bajo GNU AGPL v3
                </a>
            </div>
        </div>
    </footer>

    <script src="<?= escape(assetUrl('assets/js/app.js')) ?>"></script>
    <?php foreach ($pageScripts as $script): ?>
        <script src="<?= escape($script) ?>"></script>
    <?php endforeach; ?>
</body>
</html>
