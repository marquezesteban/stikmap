<?php

declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';

try {
    $pdo = database();
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS schema_migrations (
            migration TEXT PRIMARY KEY,
            applied_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $migrationDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database'
        . DIRECTORY_SEPARATOR . 'migrations';
    $migrationFiles = glob($migrationDirectory . DIRECTORY_SEPARATOR . '*.sql') ?: [];
    sort($migrationFiles, SORT_STRING);

    $alreadyApplied = $pdo->prepare(
        'SELECT 1 FROM schema_migrations WHERE migration = :migration'
    );
    $recordMigration = $pdo->prepare(
        'INSERT INTO schema_migrations (migration) VALUES (:migration)'
    );

    foreach ($migrationFiles as $migrationFile) {
        $migrationName = basename($migrationFile);
        $alreadyApplied->execute(['migration' => $migrationName]);

        if ($alreadyApplied->fetchColumn()) {
            echo "Omitida: {$migrationName}" . PHP_EOL;
            continue;
        }

        $sql = file_get_contents($migrationFile);
        if ($sql === false) {
            throw new RuntimeException("No se pudo leer la migración: {$migrationName}");
        }

        $pdo->beginTransaction();
        try {
            $pdo->exec($sql);
            $recordMigration->execute(['migration' => $migrationName]);
            $pdo->commit();
            echo "Aplicada: {$migrationName}" . PHP_EOL;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    echo 'Base de datos lista en: ' . databasePath() . PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, 'Error: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
