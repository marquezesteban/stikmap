<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Repositories/SongRepository.php';
require_once dirname(__DIR__) . '/app/Repositories/MarkerRepository.php';

function assertMarkerValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            $message . PHP_EOL
            . 'Esperado: ' . var_export($expected, true) . PHP_EOL
            . 'Recibido: ' . var_export($actual, true)
        );
    }
}

$pdo = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec('PRAGMA foreign_keys = ON');
$schema = file_get_contents(dirname(__DIR__) . '/database/migrations/001_initial_schema.sql');

if ($schema === false) {
    throw new RuntimeException('No se pudo leer el esquema de prueba.');
}

$pdo->exec($schema);
$songs = new SongRepository($pdo);
$markers = new MarkerRepository($pdo);
$songA = $songs->create('Canción A', null, null);
$songB = $songs->create('Canción B', null, null);
$types = $markers->types();

assertMarkerValue(10, count($types), 'Deben estar disponibles los diez tipos iniciales.');

$fillType = array_values(array_filter(
    $types,
    static fn (array $type): bool => $type['code'] === 'fill'
))[0];
$markers->create($songA, (int) $fillType['id'], 45321, 'Entrada de toms');
$editableId = $markers->create($songA, (int) $fillType['id'], 12000, null);
$markers->create($songB, (int) $fillType['id'], 999, null);

$songMarkers = $markers->allForSong($songA);
assertMarkerValue(2, count($songMarkers), 'El listado debe aislar las marcas por canción.');
assertMarkerValue(12000, $songMarkers[0]['time_ms'], 'Las marcas deben ordenarse por tiempo.');
assertMarkerValue(45321, $songMarkers[1]['time_ms'], 'Debe conservarse el milisegundo exacto.');
assertMarkerValue('Fill', $songMarkers[1]['type_label'], 'Debe incluirse el nombre del tipo.');
assertMarkerValue('Entrada de toms', $songMarkers[1]['note'], 'La nota opcional debe persistirse.');

$markers->update($editableId, $songA, (int) $fillType['id'], 18000, 'Corregida');
$edited = $markers->findForSong($editableId, $songA);
assertMarkerValue(18000, $edited['time_ms'] ?? null, 'El tiempo debe poder editarse.');
assertMarkerValue('Corregida', $edited['note'] ?? null, 'La nota debe poder editarse.');
assertMarkerValue(null, $markers->findForSong($editableId, $songB), 'Una canción no debe acceder a marcas ajenas.');

$markers->delete($editableId, $songA);
assertMarkerValue(null, $markers->findForSong($editableId, $songA), 'La marca debe poder eliminarse.');

echo 'MarkerRepositoryTest: OK' . PHP_EOL;
