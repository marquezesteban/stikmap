<?php

declare(strict_types=1);

final class MarkerRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function allForSong(int $songId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT markers.id, markers.song_id, markers.marker_type_id, markers.time_ms,
                    markers.note, marker_types.code AS type_code, marker_types.label AS type_label
             FROM markers
             INNER JOIN marker_types ON marker_types.id = markers.marker_type_id
             WHERE markers.song_id = :song_id
             ORDER BY markers.time_ms, markers.id'
        );
        $statement->execute(['song_id' => $songId]);

        return $statement->fetchAll();
    }

    /**
     * @return list<array{id: int, code: string, label: string}>
     */
    public function types(): array
    {
        return $this->pdo->query(
            'SELECT id, code, label FROM marker_types ORDER BY sort_order'
        )->fetchAll();
    }

    public function typeExists(int $typeId): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM marker_types WHERE id = :id');
        $statement->execute(['id' => $typeId]);

        return (bool) $statement->fetchColumn();
    }

    public function create(int $songId, int $typeId, int $timeMs, ?string $note): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO markers (song_id, marker_type_id, time_ms, note)
             VALUES (:song_id, :marker_type_id, :time_ms, :note)'
        );
        $statement->execute([
            'song_id' => $songId,
            'marker_type_id' => $typeId,
            'time_ms' => $timeMs,
            'note' => $note,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
