<?php

declare(strict_types=1);

final class SongRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        $statement = $this->pdo->query(
            'SELECT id, title, artist, audio_filename, created_at, updated_at
             FROM songs
             ORDER BY lower(title), id'
        );

        return $statement->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, title, artist, lyrics, audio_filename, audio_original_name,
                    audio_mime_type, audio_size_bytes, duration_ms, created_at, updated_at
             FROM songs
             WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
        $song = $statement->fetch();

        return $song === false ? null : $song;
    }

    public function create(string $title, ?string $artist, ?string $lyrics): int
    {
        $ownsTransaction = !$this->pdo->inTransaction();

        if ($ownsTransaction) {
            $this->pdo->beginTransaction();
        }

        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO songs (title, artist, lyrics) VALUES (:title, :artist, :lyrics)'
            );
            $statement->execute([
                'title' => $title,
                'artist' => $artist,
                'lyrics' => $lyrics,
            ]);
            $songId = (int) $this->pdo->lastInsertId();
            $this->syncLyricLines($songId, $lyrics);

            if ($ownsTransaction) {
                $this->pdo->commit();
            }
        } catch (Throwable $exception) {
            if ($ownsTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }

        return $songId;
    }

    public function update(int $id, string $title, ?string $artist, ?string $lyrics): bool
    {
        $ownsTransaction = !$this->pdo->inTransaction();

        if ($ownsTransaction) {
            $this->pdo->beginTransaction();
        }

        try {
            $statement = $this->pdo->prepare(
                'UPDATE songs
                 SET title = :title,
                     artist = :artist,
                     lyrics = :lyrics,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $statement->execute([
                'id' => $id,
                'title' => $title,
                'artist' => $artist,
                'lyrics' => $lyrics,
            ]);
            $updated = $statement->rowCount() > 0;

            if ($updated) {
                $this->syncLyricLines($id, $lyrics);
            }

            if ($ownsTransaction) {
                $this->pdo->commit();
            }
        } catch (Throwable $exception) {
            if ($ownsTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }

        return $updated;
    }

    /**
     * @return list<array{id: int, line_number: int, content: string}>
     */
    public function lyricLinesForSong(int $songId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, line_number, content
             FROM lyric_lines
             WHERE song_id = :song_id
             ORDER BY line_number, id'
        );
        $statement->execute(['song_id' => $songId]);

        return $statement->fetchAll();
    }

    /**
     * Completa las líneas de letras guardadas antes de v0.4.1.
     *
     * @return list<array{id: int, line_number: int, content: string}>
     */
    public function ensureLyricLinesForSong(int $songId, ?string $lyrics): array
    {
        $lines = $this->lyricLinesForSong($songId);

        if ($lines !== [] || trim((string) $lyrics) === '') {
            return $lines;
        }

        $ownsTransaction = !$this->pdo->inTransaction();

        if ($ownsTransaction) {
            $this->pdo->beginTransaction();
        }

        try {
            $this->syncLyricLines($songId, $lyrics);

            if ($ownsTransaction) {
                $this->pdo->commit();
            }
        } catch (Throwable $exception) {
            if ($ownsTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }

        return $this->lyricLinesForSong($songId);
    }

    /**
     * @param array{filename: string, original_name: string, mime_type: string, size_bytes: int} $audio
     */
    public function updateAudio(int $id, array $audio): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE songs
             SET audio_filename = :filename,
                 audio_original_name = :original_name,
                 audio_mime_type = :mime_type,
                 audio_size_bytes = :size_bytes,
                 duration_ms = NULL,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'filename' => $audio['filename'],
            'original_name' => $audio['original_name'],
            'mime_type' => $audio['mime_type'],
            'size_bytes' => $audio['size_bytes'],
        ]);

        return $statement->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM songs WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }

    private function syncLyricLines(int $songId, ?string $lyrics): void
    {
        $newLines = [];
        $sourceLines = $lyrics === null ? [] : preg_split('/\R/u', $lyrics);

        foreach ($sourceLines === false ? [] : $sourceLines as $index => $content) {
            $content = trim($content);

            if ($content !== '') {
                $newLines[] = [
                    'line_number' => $index + 1,
                    'content' => $content,
                ];
            }
        }

        $oldLines = $this->lyricLinesForSong($songId);
        $oldByContent = [];

        foreach ($oldLines as $line) {
            $oldByContent[$line['content']][] = $line;
        }

        $assignments = [];
        $reusedIds = [];

        foreach ($newLines as $line) {
            $matchingLine = null;

            if (($oldByContent[$line['content']] ?? []) !== []) {
                $matchingLine = array_shift($oldByContent[$line['content']]);
            }

            $line['id'] = $matchingLine['id'] ?? null;

            if ($line['id'] !== null) {
                $reusedIds[(int) $line['id']] = true;
            }

            $assignments[] = $line;
        }

        if ($oldLines !== []) {
            $moveStatement = $this->pdo->prepare(
                'UPDATE lyric_lines
                 SET line_number = 1000000000 + id
                 WHERE song_id = :song_id'
            );
            $moveStatement->execute(['song_id' => $songId]);
        }

        $deleteStatement = $this->pdo->prepare(
            'DELETE FROM lyric_lines WHERE id = :id AND song_id = :song_id'
        );

        foreach ($oldLines as $line) {
            if (!isset($reusedIds[(int) $line['id']])) {
                $deleteStatement->execute(['id' => $line['id'], 'song_id' => $songId]);
            }
        }

        $updateStatement = $this->pdo->prepare(
            'UPDATE lyric_lines
             SET line_number = :line_number, content = :content
             WHERE id = :id AND song_id = :song_id'
        );
        $insertStatement = $this->pdo->prepare(
            'INSERT INTO lyric_lines (song_id, line_number, content)
             VALUES (:song_id, :line_number, :content)'
        );

        foreach ($assignments as $line) {
            $parameters = [
                'song_id' => $songId,
                'line_number' => $line['line_number'],
                'content' => $line['content'],
            ];

            if ($line['id'] === null) {
                $insertStatement->execute($parameters);
                continue;
            }

            $updateStatement->execute(['id' => $line['id']] + $parameters);
        }
    }
}
