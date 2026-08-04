<?php

declare(strict_types=1);

function escape(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * @param array<string, scalar> $parameters
 */
function appUrl(string $action = 'index', array $parameters = []): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $query = $action === 'index' ? $parameters : ['action' => $action] + $parameters;

    return $script . ($query === [] ? '' : '?' . http_build_query($query));
}

/**
 * @param array<string, scalar> $parameters
 */
function redirectTo(string $action = 'index', array $parameters = []): never
{
    header('Location: ' . appUrl($action, $parameters));
    exit;
}

function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(): void
{
    $submittedToken = $_POST['csrf_token'] ?? '';

    if (!is_string($submittedToken) || !hash_equals(csrfToken(), $submittedToken)) {
        http_response_code(419);
        throw new RuntimeException('La sesión del formulario venció. Volvé a intentarlo.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * @return array{type: string, message: string}|null
 */
function pullFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : null;
}

function audioUrl(string $filename): string
{
    return dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php') . '/uploads/' . rawurlencode($filename);
}

function assetUrl(string $relativePath): string
{
    $normalizedPath = ltrim(str_replace('\\', '/', $relativePath), '/');
    $filePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR
        . str_replace('/', DIRECTORY_SEPARATOR, $normalizedPath);
    $version = is_file($filePath) ? (string) filemtime($filePath) : '1';

    return dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php') . '/' . $normalizedPath
        . '?v=' . rawurlencode($version);
}

function appVersion(): string
{
    static $version = null;

    if ($version === null) {
        $versionFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'VERSION';
        $storedVersion = is_file($versionFile) ? trim((string) file_get_contents($versionFile)) : '';
        $version = preg_match('/^\d+\.\d+\.\d+$/', $storedVersion) === 1 ? $storedVersion : '0.0.0';
    }

    return $version;
}

function formatFileSize(?int $bytes): string
{
    if ($bytes === null || $bytes <= 0) {
        return '';
    }

    return number_format($bytes / 1024 / 1024, 1, ',', '.') . ' MB';
}

function formatMarkerTime(int $milliseconds): string
{
    $milliseconds = max(0, $milliseconds);
    $minutes = intdiv($milliseconds, 60000);
    $seconds = intdiv($milliseconds % 60000, 1000);
    $remainder = $milliseconds % 1000;

    return sprintf('%d:%02d.%03d', $minutes, $seconds, $remainder);
}

/**
 * @param array<string, mixed> $data
 */
function render(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);

    ob_start();
    require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR
        . 'Views' . DIRECTORY_SEPARATOR . $view . '.php';
    $content = (string) ob_get_clean();

    require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR
        . 'Views' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
}
