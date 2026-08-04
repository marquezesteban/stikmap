<?php
/** @var string $content */
/** @var string $pageTitle */
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f3f1eb">
    <title><?= escape($pageTitle) ?> · StikMap</title>
    <link href="<?= escape(assetUrl('assets/css/print.css')) ?>" rel="stylesheet">
</head>
<body class="print-document">
    <?= $content ?>
    <script src="<?= escape(assetUrl('assets/js/app.js')) ?>"></script>
</body>
</html>
