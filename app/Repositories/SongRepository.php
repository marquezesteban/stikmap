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
            'SELECT id, title, artist, created_at, updated_at
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
            'SELECT id, title, artist, lyrics, audio_filename, created_at, updated_at
             FROM songs
             WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
        $song = $statement->fetch();

        return $song === false ? null : $song;
    }

    public function create(string $title, ?string $artist): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO songs (title, artist) VALUES (:title, :artist)'
        );
        $statement->execute([
            'title' => $title,
            'artist' => $artist,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $title, ?string $artist): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE songs
             SET title = :title, artist = :artist, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'title' => $title,
            'artist' => $artist,
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
