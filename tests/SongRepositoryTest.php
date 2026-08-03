<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Repositories/SongRepository.php';

function assertSameValue(mixed $expected, mixed $actual, string $message): void
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

$id = $songs->create('Come Together', 'The Beatles');
assertSameValue(1, $id, 'La canción debe devolver su identificador.');
assertSameValue('Come Together', $songs->find($id)['title'] ?? null, 'La canción debe poder recuperarse.');
assertSameValue(1, count($songs->all()), 'El listado debe incluir la canción creada.');

$songs->updateAudio($id, [
    'filename' => 'song-1-test.mp3',
    'original_name' => 'ensayo.mp3',
    'mime_type' => 'audio/mpeg',
    'size_bytes' => 1024,
]);
$withAudio = $songs->find($id);
assertSameValue('song-1-test.mp3', $withAudio['audio_filename'] ?? null, 'El MP3 debe asociarse a la canción.');
assertSameValue(1024, $withAudio['audio_size_bytes'] ?? null, 'El tamaño del MP3 debe persistirse.');

$songs->update($id, 'Come Together (ensayo)', null);
$updated = $songs->find($id);
assertSameValue('Come Together (ensayo)', $updated['title'] ?? null, 'El título debe actualizarse.');
assertSameValue(null, $updated['artist'] ?? null, 'El artista opcional debe aceptar null.');

$songs->delete($id);
assertSameValue(null, $songs->find($id), 'La canción debe eliminarse.');
assertSameValue([], $songs->all(), 'El listado debe quedar vacío.');

echo 'SongRepositoryTest: OK' . PHP_EOL;
