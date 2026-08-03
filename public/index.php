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
require_once dirname(__DIR__) . '/app/Controllers/SongController.php';

$controller = new SongController(new SongRepository(database()));
$action = (string) ($_GET['action'] ?? 'index');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    match ([$method, $action]) {
        ['GET', 'index'] => $controller->index(),
        ['GET', 'create'] => $controller->create(),
        ['POST', 'store'] => $controller->store(),
        ['GET', 'edit'] => $controller->edit(),
        ['POST', 'update'] => $controller->update(),
        ['POST', 'delete'] => $controller->destroy(),
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
