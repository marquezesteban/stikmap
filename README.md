# DrumMap

DrumMap es una aplicación web local para preparar un mapa temporal de una canción y generar un machete imprimible para batería.

## Alcance del MVP

- Crear, listar, editar y eliminar canciones.
- Subir un archivo MP3 por canción.
- Pegar y conservar la letra respetando sus líneas.
- Reproducir el audio con una forma de onda.
- Crear marcas en la posición actual del reproductor.
- Editar y eliminar marcas.
- Asociar opcionalmente cada marca con una nota y una línea de la letra.
- Imprimir una vista resumida de la canción y sus marcas.

No incluye autenticación, usuarios ni servicios en la nube.

## Stack

- PHP 8
- SQLite mediante PDO
- JavaScript puro
- Bootstrap 5
- WaveSurfer.js
- Apache de XAMPP

Bootstrap 5.3 y WaveSurfer.js 7 se cargan desde CDN. El MVP no requiere Node.js ni un proceso de compilación.

## Estructura propuesta

```text
drummap/
|-- app/
|   |-- Controllers/       # Coordinación de peticiones HTTP
|   |-- Repositories/      # Consultas PDO y persistencia
|   |-- Services/          # Subidas y reglas de aplicación
|   `-- Views/
|       |-- layouts/       # Plantilla general
|       `-- songs/         # Vistas de canciones, editor e impresión
|-- config/                # Configuración y conexión PDO
|-- database/
|   `-- migrations/        # Esquema SQL versionado
|-- public/                # Único punto de entrada web
|   |-- assets/
|   |   |-- css/
|   |   `-- js/
|   `-- uploads/           # MP3 locales; ignorados por Git
|-- scripts/               # Utilidades ejecutadas por consola
|-- storage/
|   |-- database/          # Archivo SQLite; ignorado por Git
|   `-- logs/              # Archivos de ejecución; ignorados por Git
|-- tests/                 # Pruebas automatizadas futuras
|-- .editorconfig
|-- .gitignore
`-- README.md
```

Las carpetas internas tienen reglas de Apache para impedir el acceso web directo. La carpeta de subidas permite servir MP3, pero no ejecutar archivos PHP.

## Modelo de datos

El esquema inicial usa cuatro tablas:

- `songs`: título, artista, letra y metadatos del MP3.
- `marker_types`: catálogo estable de tipos de marca.
- `lyric_lines`: líneas numeradas de la letra de cada canción.
- `markers`: instante en milisegundos, tipo, nota y línea de letra opcional.

El tiempo se guarda como un entero en milisegundos para evitar errores de redondeo de números decimales. Las claves foráneas se habilitan en cada conexión SQLite.

## Instalación local

1. Instalar XAMPP con PHP 8 y habilitar las extensiones `pdo_sqlite` y `sqlite3` en `php.ini`.
2. Mantener el proyecto en `C:\xampp\htdocs\drummap`.
3. Inicializar la base de datos desde PowerShell:

   ```powershell
   C:\xampp\php\php.exe scripts\init-db.php
   ```

4. Iniciar Apache desde el panel de XAMPP.
5. Abrir `http://localhost/drummap/public/`.

El script de inicialización es repetible: aplica solamente las migraciones que todavía no estén registradas.

## Pruebas

La prueba actual comprueba el ciclo completo de persistencia de canciones sobre una base SQLite en memoria:

```powershell
C:\xampp\php\php.exe tests\SongRepositoryTest.php
```

Antes de cerrar cada etapa también se recorre el flujo desde Apache y se revisa la interfaz en tamaños de teléfono, tablet y escritorio.

La carga de audio acepta archivos MP3 de hasta 38 MB. Los archivos reciben nombres internos aleatorios, se validan por extensión y contenido, y se guardan en `public/uploads/`, fuera de Git. Al reemplazar un MP3 o eliminar su canción también se elimina el archivo anterior.

## Etapas y commits

La implementación se divide en cambios pequeños y verificables:

1. `chore: create initial project structure`
2. `docs: describe MVP architecture and roadmap`
3. `feat(db): add initial SQLite schema`
4. `feat(songs): add song list and creation`
5. `feat(songs): add song editing and deletion`
6. `feat(audio): add MP3 upload and waveform player`
7. `feat(markers): add marker creation and timeline`
8. `feat(markers): add marker editing and deletion`
9. `feat(lyrics): add lyric line associations`
10. `feat(print): add drummer cheat sheet view`

Cada etapa debería cerrar con una comprobación manual y, donde aporte valor, una prueba automatizada pequeña.

## GitHub

El repositorio local usa la rama `main`. Cuando exista un repositorio remoto vacío en GitHub, se puede vincular sin reescribir el historial:

```powershell
git remote add origin https://github.com/USUARIO/drummap.git
git push -u origin main
```

Los MP3, la base SQLite y los archivos locales de configuración quedan fuera del repositorio.
