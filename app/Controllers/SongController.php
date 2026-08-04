<?php

declare(strict_types=1);

final class SongController
{
    public function __construct(
        private readonly SongRepository $songs,
        private readonly MarkerRepository $markers,
        private readonly AudioUploadService $audioUploads,
    ) {
    }

    public function index(): void
    {
        render('songs/index', [
            'pageTitle' => 'Canciones',
            'songs' => $this->songs->all(),
            'flashMessage' => pullFlash(),
        ]);
    }

    public function create(): void
    {
        $this->renderForm([
            'id' => null,
            'title' => '',
            'artist' => '',
            'lyrics' => '',
        ]);
    }

    public function show(): void
    {
        $this->renderShow($this->requireSong());
    }

    public function store(): void
    {
        verifyCsrfToken();
        [$title, $artist, $lyrics, $errors] = $this->validatedInput();

        if ($errors !== []) {
            $this->renderForm([
                'id' => null,
                'title' => $title,
                'artist' => $artist,
                'lyrics' => $lyrics,
            ], $errors);
            return;
        }

        $songId = $this->songs->create(
            $title,
            $artist === '' ? null : $artist,
            $lyrics === '' ? null : $lyrics,
        );
        $audioFile = $_FILES['audio'] ?? [];

        if ($this->hasAudioUpload($audioFile)) {
            $newAudio = null;

            try {
                $newAudio = $this->audioUploads->store($audioFile, $songId);

                if (!$this->songs->updateAudio($songId, $newAudio)) {
                    throw new RuntimeException('No se pudo asociar el audio con la canción.');
                }
            } catch (AudioUploadException $exception) {
                $this->songs->delete($songId);
                $errors['audio'] = $exception->getMessage();
                $this->renderForm([
                    'id' => null,
                    'title' => $title,
                    'artist' => $artist,
                    'lyrics' => $lyrics,
                ], $errors);
                return;
            } catch (Throwable $exception) {
                $this->audioUploads->delete($newAudio['filename'] ?? null);
                $this->songs->delete($songId);
                throw $exception;
            }

            flash('success', 'Canción y audio cargados.');
            redirectTo('show', ['id' => $songId]);
        }

        flash('success', 'Canción creada.');
        redirectTo();
    }

    public function edit(): void
    {
        $song = $this->requireSong();
        $this->renderForm($song);
    }

    public function update(): void
    {
        verifyCsrfToken();
        $song = $this->requireSong();
        [$title, $artist, $lyrics, $errors] = $this->validatedInput();

        if ($errors !== []) {
            $song['title'] = $title;
            $song['artist'] = $artist;
            $song['lyrics'] = $lyrics;
            $this->renderForm($song, $errors);
            return;
        }

        $this->songs->update(
            (int) $song['id'],
            $title,
            $artist === '' ? null : $artist,
            $lyrics === '' ? null : $lyrics,
        );
        flash('success', 'Cambios guardados.');
        redirectTo('show', ['id' => (int) $song['id']]);
    }

    public function destroy(): void
    {
        verifyCsrfToken();
        $song = $this->requireSong();
        $this->songs->delete((int) $song['id']);
        $this->audioUploads->delete($song['audio_filename'] ?? null);
        flash('success', 'Canción eliminada.');
        redirectTo();
    }

    public function uploadAudio(): void
    {
        verifyCsrfToken();
        $song = $this->requireSong();

        try {
            $newAudio = $this->audioUploads->store($_FILES['audio'] ?? [], (int) $song['id']);
        } catch (AudioUploadException $exception) {
            $this->renderShow($song, $exception->getMessage());
            return;
        }

        try {
            if (!$this->songs->updateAudio((int) $song['id'], $newAudio)) {
                throw new RuntimeException('La canción dejó de estar disponible durante la carga.');
            }
        } catch (Throwable $exception) {
            $this->audioUploads->delete($newAudio['filename']);
            throw $exception;
        }

        $this->audioUploads->delete($song['audio_filename'] ?? null);
        flash('success', ($song['audio_filename'] ?? null) === null ? 'Audio cargado.' : 'Audio reemplazado.');
        redirectTo('show', ['id' => (int) $song['id']]);
    }

    public function storeMarker(): void
    {
        verifyCsrfToken();
        $song = $this->requireSong();
        [$timeInput, $typeInput, $note, $lyricLineId, $resumePlaying, $errors, $input] =
            $this->validatedMarkerInput($song);

        if ($errors !== []) {
            $this->renderShow($song, null, $errors, $input);
            return;
        }

        $this->markers->create(
            (int) $song['id'],
            $typeInput,
            $timeInput,
            $note === '' ? null : $note,
            $lyricLineId,
        );
        flash('success', 'Marca guardada en ' . formatMarkerTime($timeInput) . '.');
        $this->redirectToMarkerTime((int) $song['id'], $timeInput, $resumePlaying);
    }

    public function updateMarker(): void
    {
        verifyCsrfToken();
        $song = $this->requireSong();
        $marker = $this->requireMarker((int) $song['id']);
        [$timeInput, $typeInput, $note, $lyricLineId, $resumePlaying, $errors, $input] =
            $this->validatedMarkerInput($song);
        $input['id'] = (int) $marker['id'];

        if ($errors !== []) {
            $this->renderShow($song, null, $errors, $input);
            return;
        }

        $this->markers->update(
            (int) $marker['id'],
            (int) $song['id'],
            $typeInput,
            $timeInput,
            $note === '' ? null : $note,
            $lyricLineId,
        );
        flash('success', 'Marca actualizada en ' . formatMarkerTime($timeInput) . '.');
        $this->redirectToMarkerTime((int) $song['id'], $timeInput, $resumePlaying);
    }

    public function destroyMarker(): void
    {
        verifyCsrfToken();
        $song = $this->requireSong();
        $marker = $this->requireMarker((int) $song['id']);
        $resumeMs = filter_input(INPUT_POST, 'resume_ms', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0],
        ]);
        $resumePlaying = ($_POST['resume_playing'] ?? '0') === '1';

        $this->markers->delete((int) $marker['id'], (int) $song['id']);
        flash('success', 'Marca eliminada.');
        $this->redirectToMarkerTime(
            (int) $song['id'],
            is_int($resumeMs) ? $resumeMs : (int) $marker['time_ms'],
            $resumePlaying,
        );
    }

    /**
     * @param array<string, mixed> $song
     * @return array{0: int, 1: int, 2: string, 3: int|null, 4: bool, 5: array<string, string>, 6: array<string, mixed>}
     */
    private function validatedMarkerInput(array $song): array
    {
        $timeInput = filter_input(INPUT_POST, 'time_ms', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0],
        ]);
        $typeInput = filter_input(INPUT_POST, 'marker_type_id', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        $note = trim((string) ($_POST['note'] ?? ''));
        $lyricLineInput = trim((string) ($_POST['lyric_line_id'] ?? ''));
        $lyricLineId = $lyricLineInput === ''
            ? null
            : filter_var($lyricLineInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $resumePlaying = ($_POST['resume_playing'] ?? '0') === '1';
        $errors = [];

        if (($song['audio_filename'] ?? null) === null) {
            $errors['time_ms'] = 'Cargá el audio antes de crear marcas.';
        } elseif (!is_int($timeInput)) {
            $errors['time_ms'] = 'Elegí un instante válido desde el reproductor.';
        }

        if (!is_int($typeInput) || !$this->markers->typeExists($typeInput)) {
            $errors['marker_type_id'] = 'Elegí un tipo de marca válido.';
        }

        if (mb_strlen($note) > 240) {
            $errors['note'] = 'La nota no puede superar los 240 caracteres.';
        }

        if ($lyricLineInput !== '' && (
            !is_int($lyricLineId)
            || !$this->markers->lyricLineExistsForSong($lyricLineId, (int) $song['id'])
        )) {
            $errors['lyric_line_id'] = 'Elegí una línea de la letra de esta canción.';
        }

        $input = [
            'time_ms' => is_int($timeInput) ? $timeInput : 0,
            'marker_type_id' => is_int($typeInput) ? $typeInput : 0,
            'note' => $note,
            'lyric_line_id' => is_int($lyricLineId) ? $lyricLineId : null,
        ];

        return [
            is_int($timeInput) ? $timeInput : 0,
            is_int($typeInput) ? $typeInput : 0,
            $note,
            is_int($lyricLineId) ? $lyricLineId : null,
            $resumePlaying,
            $errors,
            $input,
        ];
    }

    private function redirectToMarkerTime(int $songId, int $timeMs, bool $resumePlaying): never
    {
        redirectTo('show', [
            'id' => $songId,
            'resume_ms' => $timeMs,
            'autoplay' => $resumePlaying ? 1 : 0,
        ]);
    }

    /**
     * @param array<string, mixed> $song
     * @param array<string, string> $errors
     */
    private function renderForm(array $song, array $errors = []): void
    {
        $isEditing = $song['id'] !== null;
        render('songs/form', [
            'pageTitle' => $isEditing ? 'Editar canción' : 'Nueva canción',
            'song' => $song,
            'errors' => $errors,
            'isEditing' => $isEditing,
        ]);
    }

    /**
     * @param array<string, mixed> $song
     */
    private function renderShow(
        array $song,
        ?string $audioError = null,
        array $markerErrors = [],
        array $markerInput = [],
    ): void
    {
        $resumeMs = filter_input(INPUT_GET, 'resume_ms', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0],
        ]);

        render('songs/show', [
            'pageTitle' => (string) $song['title'],
            'song' => $song,
            'audioError' => $audioError,
            'markers' => $this->markers->allForSong((int) $song['id']),
            'markerTypes' => $this->markers->types(),
            'lyricLines' => $this->songs->ensureLyricLinesForSong(
                (int) $song['id'],
                isset($song['lyrics']) ? (string) $song['lyrics'] : null,
            ),
            'markerErrors' => $markerErrors,
            'markerInput' => $markerInput,
            'resumeMs' => is_int($resumeMs) ? $resumeMs : 0,
            'resumePlaying' => ($_GET['autoplay'] ?? '0') === '1',
            'flashMessage' => pullFlash(),
            'pageScripts' => ($song['audio_filename'] ?? null) === null ? [] : [
                'https://unpkg.com/wavesurfer.js@7',
                'https://unpkg.com/wavesurfer.js@7/dist/plugins/regions.min.js',
                assetUrl('assets/js/audio-player.js'),
            ],
        ]);
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: array<string, string>}
     */
    private function validatedInput(): array
    {
        $title = trim((string) ($_POST['title'] ?? ''));
        $artist = trim((string) ($_POST['artist'] ?? ''));
        $lyrics = trim(str_replace(["\r\n", "\r"], "\n", (string) ($_POST['lyrics'] ?? '')));
        $errors = [];

        if ($title === '') {
            $errors['title'] = 'Escribí el título de la canción.';
        } elseif (mb_strlen($title) > 120) {
            $errors['title'] = 'El título no puede superar los 120 caracteres.';
        }

        if (mb_strlen($artist) > 120) {
            $errors['artist'] = 'El artista no puede superar los 120 caracteres.';
        }

        if (mb_strlen($lyrics) > 50000) {
            $errors['lyrics'] = 'La letra no puede superar los 50.000 caracteres.';
        }

        return [$title, $artist, $lyrics, $errors];
    }

    /**
     * @param array<string, mixed> $file
     */
    private function hasAudioUpload(array $file): bool
    {
        return (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * @return array<string, mixed>
     */
    private function requireSong(): array
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        $song = is_int($id) ? $this->songs->find($id) : null;

        if ($song === null) {
            http_response_code(404);
            render('errors/404', ['pageTitle' => 'Canción no encontrada']);
            exit;
        }

        return $song;
    }

    /**
     * @return array<string, mixed>
     */
    private function requireMarker(int $songId): array
    {
        $markerId = filter_input(INPUT_GET, 'marker_id', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        $marker = is_int($markerId) ? $this->markers->findForSong($markerId, $songId) : null;

        if ($marker === null) {
            http_response_code(404);
            render('errors/404', ['pageTitle' => 'Marca no encontrada']);
            exit;
        }

        return $marker;
    }
}
