<?php

declare(strict_types=1);

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
]);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/functions.php';
require_once dirname(__DIR__) . '/app/Repositories/SongRepository.php';
require_once dirname(__DIR__) . '/app/Repositories/MarkerRepository.php';
require_once dirname(__DIR__) . '/app/Services/AudioUploadException.php';
require_once dirname(__DIR__) . '/app/Services/AudioUploadService.php';
require_once dirname(__DIR__) . '/app/Controllers/SongController.php';

$controller = new SongController(
    new SongRepository(database()),
    new MarkerRepository(database()),
    new AudioUploadService(__DIR__ . DIRECTORY_SEPARATOR . 'uploads'),
);
$action = (string) ($_GET['action'] ?? 'index');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    match ([$method, $action]) {
        ['GET', 'index'] => $controller->index(),
        ['GET', 'show'] => $controller->show(),
        ['GET', 'print'] => $controller->printSheet(),
        ['GET', 'create'] => $controller->create(),
        ['POST', 'store'] => $controller->store(),
        ['GET', 'edit'] => $controller->edit(),
        ['POST', 'update'] => $controller->update(),
        ['POST', 'delete'] => $controller->destroy(),
        ['POST', 'audio-upload'] => $controller->uploadAudio(),
        ['POST', 'marker-store'] => $controller->storeMarker(),
        ['POST', 'marker-update'] => $controller->updateMarker(),
        ['POST', 'marker-delete'] => $controller->destroyMarker(),
        default => (function (): void {
            http_response_code(404);
            render('errors/404', ['pageTitle' => 'Página no encontrada']);
        })(),
    };
} catch (Throwable $exception) {
    error_log($exception->__toString());

    if (http_response_code() < 400) {
        http_response_code(500);
    }

    $message = http_response_code() === 419
        ? $exception->getMessage()
        : 'No pudimos completar la operación. Volvé a intentarlo.';
    render('errors/error', [
        'pageTitle' => 'Algo salió mal',
        'message' => $message,
    ]);
}
