<?php

declare(strict_types=1);

final class AudioUploadService
{
    private const MAX_FILE_SIZE = 38 * 1024 * 1024;

    public function __construct(private readonly string $uploadDirectory)
    {
    }

    /**
     * @param array<string, mixed> $file
     * @return array{filename: string, original_name: string, mime_type: string, size_bytes: int}
     */
    public function store(array $file, int $songId): array
    {
        $this->validateUploadStatus($file);

        $temporaryPath = (string) ($file['tmp_name'] ?? '');
        $originalName = basename((string) ($file['name'] ?? ''));
        $size = (int) ($file['size'] ?? 0);

        if ($originalName === '' || strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) !== 'mp3') {
            throw new AudioUploadException('Elegí un archivo con extensión .mp3.');
        }

        if ($size <= 0) {
            throw new AudioUploadException('El archivo MP3 está vacío.');
        }

        if ($size > self::MAX_FILE_SIZE) {
            throw new AudioUploadException('El MP3 no puede superar los 38 MB.');
        }

        if (!is_uploaded_file($temporaryPath)) {
            throw new AudioUploadException('No pudimos verificar el archivo subido.');
        }

        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
        if (!in_array($mimeType, ['audio/mpeg', 'audio/mp3'], true)) {
            throw new AudioUploadException('El archivo no parece ser un MP3 válido.');
        }

        if (!is_dir($this->uploadDirectory)
            && !mkdir($this->uploadDirectory, 0775, true)
            && !is_dir($this->uploadDirectory)) {
            throw new RuntimeException('No se pudo preparar la carpeta de audio.');
        }

        $filename = sprintf('song-%d-%s.mp3', $songId, bin2hex(random_bytes(12)));
        $destination = $this->uploadDirectory . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($temporaryPath, $destination)) {
            throw new RuntimeException('No se pudo guardar el archivo MP3.');
        }

        return [
            'filename' => $filename,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size_bytes' => $size,
        ];
    }

    public function delete(?string $filename): void
    {
        if ($filename === null || $filename === '' || basename($filename) !== $filename) {
            return;
        }

        $path = $this->uploadDirectory . DIRECTORY_SEPARATOR . $filename;
        if (is_file($path) && !unlink($path)) {
            error_log("No se pudo eliminar el audio: {$path}");
        }
    }

    /**
     * @param array<string, mixed> $file
     */
    private function validateUploadStatus(array $file): void
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        $message = match ($error) {
            UPLOAD_ERR_OK => null,
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El MP3 supera el tamaño permitido por el servidor.',
            UPLOAD_ERR_PARTIAL => 'La carga quedó incompleta. Volvé a intentarlo.',
            UPLOAD_ERR_NO_FILE => 'Elegí un archivo MP3 para continuar.',
            default => 'No pudimos recibir el archivo MP3.',
        };

        if ($message !== null) {
            throw new AudioUploadException($message);
        }
    }
}
