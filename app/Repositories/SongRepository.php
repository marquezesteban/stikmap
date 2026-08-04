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
        $statement = $this->pdo->prepare(
            'INSERT INTO songs (title, artist, lyrics) VALUES (:title, :artist, :lyrics)'
        );
        $statement->execute([
            'title' => $title,
            'artist' => $artist,
            'lyrics' => $lyrics,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $title, ?string $artist, ?string $lyrics): bool
    {
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

        return $statement->rowCount() > 0;
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
}
