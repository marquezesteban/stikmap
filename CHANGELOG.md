# Historial de versiones

StikMap usa versionado semántico durante el desarrollo del MVP.

## [0.5.1] - 2026-08-04

- Rediseño de la impresión como cancionero con la letra completa.
- Indicaciones de tiempo, tipo y nota ubicadas encima de la línea o sección asociada.
- Bloque final que identifica marcas todavía sin ubicación dentro de la letra.
- Vista temporal conservada como alternativa para canciones sin letra.

## [0.5.0] - 2026-08-04

- Vista de machete independiente del reproductor y preparada para A4.
- Marcas cronológicas con tiempo, tipo, nota y referencia de letra o sección.
- Diseño de bajo consumo de tinta con identificación visual por tipo.
- Impresión directa y guardado como PDF desde escritorio o teléfono.
- Estado vacío para canciones que todavía no tienen marcas.

## [0.4.4] - 2026-08-04

- Nuevo manual de uso enlazado desde el README.
- Documentación de la sintaxis entre corchetes, ejemplos y asociaciones con marcas.

## [0.4.3] - 2026-08-04

- Corrección del espaciado vertical y horizontal en la lectura de la letra.
- Conservación exclusiva de los espacios y saltos escritos por el usuario.

## [0.4.2] - 2026-08-04

- Selector breve para insertar secciones musicales en el punto del cursor.
- Numeración automática de etiquetas `[VERSO 1]`, `[VERSO 2]` y siguientes.
- Reconocimiento de anotaciones personalizadas escritas entre corchetes.
- Secciones destacadas en la lectura de la letra y diferenciadas en las marcas.

## [0.4.1] - 2026-08-03

- División de la letra en líneas seleccionables, omitiendo espacios vacíos.
- Asociación opcional de una línea al crear o editar una marca.
- Línea asociada visible dentro del mapa temporal.
- Conservación de asociaciones cuando una línea mantiene su texto aunque cambie de posición.
- Desvinculación segura cuando una línea asociada se elimina de la letra.

## [0.4.0] - 2026-08-03

- Carga y edición opcional de la letra desde el formulario de canción.
- Persistencia en SQLite respetando versos, espacios y saltos de línea.
- Panel de lectura de letra adaptable a escritorio, tablets y teléfonos.
- Estado vacío con acceso directo para agregar la letra.

## [0.3.2] - 2026-08-03

- CRUD de canciones con carga opcional de MP3.
- Reproductor con forma de onda, zoom táctil y reloj con milisegundos.
- Creación, edición y eliminación de marcas persistidas en SQLite.
- Puntos de colores sobre la onda y acceso directo a su edición.
- Interfaz adaptable a escritorio, tablets y teléfonos.
- Autoría, contacto y licencia visibles.

## Criterio de versiones

- `0.x.0`: avance principal o nuevo sprint del MVP.
- `0.x.y`: mejora o corrección dentro del sprint actual.
- `1.0.0`: MVP completo, probado y documentado.
