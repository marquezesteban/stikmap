<?php

declare(strict_types=1);

final class SongController
{
    public function __construct(private readonly SongRepository $songs)
    {
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
        ]);
    }

    public function store(): void
    {
        verifyCsrfToken();
        [$title, $artist, $errors] = $this->validatedInput();

        if ($errors !== []) {
            $this->renderForm(['id' => null, 'title' => $title, 'artist' => $artist], $errors);
            return;
        }

        $this->songs->create($title, $artist === '' ? null : $artist);
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
        [$title, $artist, $errors] = $this->validatedInput();

        if ($errors !== []) {
            $song['title'] = $title;
            $song['artist'] = $artist;
            $this->renderForm($song, $errors);
            return;
        }

        $this->songs->update((int) $song['id'], $title, $artist === '' ? null : $artist);
        flash('success', 'Cambios guardados.');
        redirectTo();
    }

    public function destroy(): void
    {
        verifyCsrfToken();
        $song = $this->requireSong();
        $this->songs->delete((int) $song['id']);
        flash('success', 'Canción eliminada.');
        redirectTo();
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
     * @return array{0: string, 1: string, 2: array<string, string>}
     */
    private function validatedInput(): array
    {
        $title = trim((string) ($_POST['title'] ?? ''));
        $artist = trim((string) ($_POST['artist'] ?? ''));
        $errors = [];

        if ($title === '') {
            $errors['title'] = 'Escribí el título de la canción.';
        } elseif (mb_strlen($title) > 120) {
            $errors['title'] = 'El título no puede superar los 120 caracteres.';
        }

        if (mb_strlen($artist) > 120) {
            $errors['artist'] = 'El artista no puede superar los 120 caracteres.';
        }

        return [$title, $artist, $errors];
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
}
