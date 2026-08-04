<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/functions.php';

if (!isLyricSection('[VERSO 1]')) {
    throw new RuntimeException('Una anotación entre corchetes debe reconocerse como sección.');
}

if (isLyricSection('El problema no fue hallarte')) {
    throw new RuntimeException('Una línea cantada no debe reconocerse como sección.');
}

if (lyricSectionLabel('[INSTRUMENTAL CON FILL]') !== 'INSTRUMENTAL CON FILL') {
    throw new RuntimeException('La etiqueta visible debe omitir los corchetes.');
}

echo 'LyricSectionTest: OK' . PHP_EOL;
