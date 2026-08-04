# Sprints de StikMap

Este documento conserva el objetivo, el alcance y la evidencia de cada sprint. Una función que no aparece en el alcance del sprint activo puede proponerse, pero no se implementa hasta ser priorizada.

## Visión del producto

StikMap convierte una canción en un mapa de ejecución para batería. Debe ayudar a anticipar qué viene, ubicarse rápidamente durante un ensayo y reducir la carga de memorizar estructuras completas.

El flujo central del MVP es:

```text
Canción → MP3 y letra → escucha → marcas temporales → mapa de batería → machete
```

La experiencia prioriza teléfono y tablet: controles táctiles, lectura rápida, pocas decisiones por pantalla y una identidad visual propia.

## Definición de terminado

Un sprint se considera terminado solamente cuando:

- Cumple todos sus criterios de aceptación.
- Fue probado desde Apache con datos reales o controlados.
- Fue revisado en teléfono, tablet y escritorio cuando afecta la interfaz.
- No deja datos ni archivos temporales.
- Tiene commits pequeños y descriptivos.
- Actualiza la documentación correspondiente.
- El usuario valida la experiencia antes de avanzar.

## Sprint 0 — Base del proyecto

**Estado:** terminado.

**Objetivo:** establecer una base segura y versionada para el MVP.

**Incluyó:**

- Estructura de carpetas.
- Repositorio Git sobre `main`.
- README y `.gitignore`.
- Conexión PDO a SQLite.
- Migraciones idempotentes.
- Tablas de canciones, tipos de marca, líneas de letra y marcas.
- Protección Apache de carpetas internas y uploads.

**Pruebas:** sintaxis PHP, creación repetible de SQLite, tablas, catálogo de 10 marcas y claves foráneas.

**Commits:** `2faa98d`, `64f944c`, `6b768d2`.

## Sprint 1 — Biblioteca de canciones

**Estado:** terminado y validado por el usuario.

**Objetivo:** administrar el repertorio desde cualquier tamaño de pantalla.

**Incluyó:**

- Listado y estado vacío.
- Alta con título obligatorio y artista opcional.
- Edición y eliminación con confirmación.
- Validación, CSRF y errores 404.
- Diseño mobile-first con identidad propia.
- Acceso desde dispositivos de la red local.

**Pruebas:** creación, validación, edición, eliminación, persistencia, navegación táctil, ausencia de desbordamiento horizontal y acceso LAN.

**Commits:** `e2b5964`, `82997ed`, `a0a3a26`.

## Sprint 2 — Audio y forma de onda

**Estado:** terminado; pendiente de validación prolongada con canciones del usuario.

**Objetivo:** cargar y recorrer el audio que servirá como referencia temporal.

**Incluyó:**

- Carga y reemplazo de MP3 de hasta 38 MB.
- Validación por extensión, MIME y estado de subida.
- Nombres internos aleatorios y limpieza de archivos reemplazados.
- Pantalla de trabajo por canción.
- Forma de onda con WaveSurfer.js 7.
- Reproducir, pausar y saltar 10 segundos.
- Indicador de audio en la biblioteca.
- Carga opcional del MP3 durante la creación de una canción, con acceso directo al reproductor.

**Pruebas:** MP3 válido, reemplazo, archivo falso rechazado, reproducción completa, limpieza de archivos y revisión responsive/LAN.

**Commits:** `13ed72e`, `85f58a3`.

## Sprint 2.5 — Identidad y publicación

**Estado:** terminado.

**Objetivo:** adoptar una marca distintiva antes de ampliar el producto y evitar renombrados tardíos.

**Incluyó:**

- Adopción integral del nombre StikMap.
- Migración de carpeta, URL local y nombre de la base SQLite.
- Renombrado del repositorio público de GitHub.
- Licencia de código GNU AGPL v3.
- Política separada para proteger el nombre y la identidad visual.

**Criterios de cierre:** no quedan referencias activas al nombre anterior, la canción existente se conserva, las URLs local y LAN funcionan y `main` queda sincronizada con GitHub.

## Sprint 3 — Marcas temporales

**Estado:** en desarrollo.

**Objetivo:** convertir la escucha en un mapa de batería editable y persistente.

**Entrega 3.1 — precisión y persistencia básica:**

**Estado de la entrega:** terminada y probada.

- Zoom táctil de la forma de onda con controles para acercar, alejar y volver a la vista completa.
- Captura del instante actual sin pausar automáticamente la reproducción.
- Alta de marca con tipo inicial y nota opcional.
- Persistencia en milisegundos y listado cronológico.
- Salto del reproductor al tocar una marca guardada.
- Restauración del reproductor en el instante guardado, conservando el estado de reproducción cuando el navegador lo permite.

**Entrega 3.2 — mantenimiento e integración visual:**

**Estado de la entrega:** terminada y probada.

- Edición de tiempo, tipo y nota desde el mismo panel.
- Eliminación con confirmación y validación de pertenencia a la canción.
- Puntos de color sobre la forma de onda mediante Regions de WaveSurfer 7.
- Salto al instante al tocar tanto el punto como la fila del listado.
- Campo de tiempo editable con precisión de milisegundos y validación contra la duración del audio.
- Forma de onda más alta, puntos sin recorte y menú contextual deshabilitado sobre ellos.
- Apertura directa de la edición al tocar un punto de color sobre la onda.
- Reloj del reproductor con milisegundos para posición actual y duración total.

**Alcance propuesto:**

- Crear una marca usando el tiempo actual del reproductor.
- Guardar el tiempo como milisegundos enteros.
- Elegir uno de los 10 tipos iniciales.
- Agregar una nota opcional.
- Mostrar las marcas ordenadas cronológicamente.
- Representar cada marca como un punto visible sobre la forma de onda.
- Tocar una marca para llevar el reproductor a ese instante.
- Editar tiempo, tipo y nota.
- Eliminar una marca con confirmación.
- Mantener todos los controles cómodos para uso táctil.

**Propuesta de interacción:** un botón principal `Marcar ahora` captura el instante actual y abre un panel compacto para elegir el tipo y escribir una nota. La reproducción no se detiene automáticamente; el baterista decide cuándo pausar.

**Criterios de aceptación:**

- El botón permanece deshabilitado hasta que el audio esté listo.
- Una marca conserva el mismo instante después de recargar la página.
- El listado y la forma de onda reflejan altas, ediciones y eliminaciones.
- Las marcas siempre pertenecen a la canción correcta.
- Tocar una marca posiciona el audio con precisión.
- Los datos ingresados se validan y escapan correctamente.
- El flujo funciona en teléfono, tablet y escritorio sin hover obligatorio.
- Las pruebas existentes siguen pasando.

**Fuera de alcance:**

- Letra y asociación con líneas.
- Vista imprimible.
- Tipos de marca personalizados.
- Arrastrar marcas sobre la forma de onda.
- Atajos de teclado.
- Regiones con inicio y fin.
- Sincronización entre dispositivos.

## Sprints siguientes

- **Sprint 4 — Letra:** pegado, división en líneas y asociación opcional con marcas.
  - **Entrega 4.1 — carga y lectura:** terminada. Incluye pegado, edición, persistencia y lectura responsive respetando los saltos de línea.
  - **Entrega 4.2 — asociación con marcas:** terminada. Permite elegir, cambiar o quitar una línea al crear o editar una marca, y verla dentro del mapa temporal.
  - **Entrega 4.3 — secciones musicales:** terminada. Permite insertar etiquetas entre corchetes, numerar versos y usar las secciones como referencia de una marca.
- **Sprint 5 — Impresión:** terminado. Incluye machete cronológico, vista A4 independiente, impresión o guardado como PDF y referencias opcionales de letra o sección.
- **Cierre del MVP:** pruebas integrales, correcciones y documentación de instalación.
