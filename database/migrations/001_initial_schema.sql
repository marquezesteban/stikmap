CREATE TABLE songs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL CHECK (length(trim(title)) > 0),
    artist TEXT,
    lyrics TEXT,
    audio_filename TEXT,
    audio_original_name TEXT,
    audio_mime_type TEXT,
    audio_size_bytes INTEGER CHECK (audio_size_bytes IS NULL OR audio_size_bytes >= 0),
    duration_ms INTEGER CHECK (duration_ms IS NULL OR duration_ms >= 0),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE marker_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT NOT NULL UNIQUE,
    label TEXT NOT NULL UNIQUE,
    sort_order INTEGER NOT NULL UNIQUE
);

INSERT INTO marker_types (code, label, sort_order) VALUES
    ('intro', 'Intro', 10),
    ('verse', 'Verso', 20),
    ('chorus', 'Estribillo', 30),
    ('fill', 'Fill', 40),
    ('break', 'Corte', 50),
    ('ride', 'Ride', 60),
    ('solo', 'Solo', 70),
    ('intensity_up', 'Subir intensidad', 80),
    ('intensity_down', 'Bajar intensidad', 90),
    ('ending', 'Final', 100);

CREATE TABLE lyric_lines (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    song_id INTEGER NOT NULL,
    line_number INTEGER NOT NULL CHECK (line_number >= 1),
    content TEXT NOT NULL,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE,
    UNIQUE (song_id, line_number)
);

CREATE TABLE markers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    song_id INTEGER NOT NULL,
    marker_type_id INTEGER NOT NULL,
    time_ms INTEGER NOT NULL CHECK (time_ms >= 0),
    note TEXT,
    lyric_line_id INTEGER,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE,
    FOREIGN KEY (marker_type_id) REFERENCES marker_types(id) ON DELETE RESTRICT,
    FOREIGN KEY (lyric_line_id) REFERENCES lyric_lines(id) ON DELETE SET NULL
);

CREATE INDEX idx_lyric_lines_song ON lyric_lines (song_id, line_number);
CREATE INDEX idx_markers_song_time ON markers (song_id, time_ms, id);
CREATE INDEX idx_markers_type ON markers (marker_type_id);

CREATE TRIGGER markers_validate_lyric_line_insert
BEFORE INSERT ON markers
WHEN NEW.lyric_line_id IS NOT NULL
    AND NOT EXISTS (
        SELECT 1
        FROM lyric_lines
        WHERE id = NEW.lyric_line_id AND song_id = NEW.song_id
    )
BEGIN
    SELECT RAISE(ABORT, 'La línea de letra no pertenece a la canción');
END;

CREATE TRIGGER markers_validate_lyric_line_update
BEFORE UPDATE OF song_id, lyric_line_id ON markers
WHEN NEW.lyric_line_id IS NOT NULL
    AND NOT EXISTS (
        SELECT 1
        FROM lyric_lines
        WHERE id = NEW.lyric_line_id AND song_id = NEW.song_id
    )
BEGIN
    SELECT RAISE(ABORT, 'La línea de letra no pertenece a la canción');
END;
